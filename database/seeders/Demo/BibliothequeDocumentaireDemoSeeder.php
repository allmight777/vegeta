<?php

namespace Database\Seeders\Demo;

use App\Models\Admin;
use App\Models\DocumentIa;
use App\Models\Reseau;
use App\Services\Assistance\TraiteurDocumentIa;
use App\Services\Securite\IndexAveugle;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * 13_PROMPT_IA_VISIBLE_DANS_INTERFACE §2.4 : sans document déjà chargé, l'outil
 * "recherche documentaire" de l'assistant (`OutilRechercheDocumentaire`) n'a rien à
 * trouver sur un `migrate:fresh --seed` frais, et la bibliothèque documentaire admin
 * (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA) paraît vide/cassée le jour J.
 *
 * Contenu explicitement marqué comme fictif dans le texte lui-même (pas un vrai
 * rapport GIABA) — CLAUDE.md §3 « ne jamais inventer une valeur ni un format
 * officiel ». Passe par le vrai pipeline `TraiteurDocumentIa` (comme
 * `DemoCoreBankingSeeder` le fait pour l'import CSV), pour que le document soit
 * strictement identique à un vrai upload admin.
 */
class BibliothequeDocumentaireDemoSeeder extends Seeder
{
    public function run(IndexAveugle $indexAveugle, TraiteurDocumentIa $traiteur): void
    {
        if (! config('cif_demo.actif') || DocumentIa::count() > 0) {
            return;
        }

        $admin = Admin::where(
            'email_idx',
            $indexAveugle->calculer('admin.alpha@cif-empreinte.demo', 'email')
        )->first();

        $reseau = Reseau::where('code', 'ALPHA')->first();

        $contenu = <<<'TEXTE'
        Guide interne de démonstration — typologies régionales LBC/FT
        (Extrait fictif, non officiel — usage de démonstration uniquement, ne remplace
        aucune publication réelle du GIABA)

        Typologie 1 — Transfert de valeurs non déclaré
        Signaux d'alerte : activité déclarée « transfert d'argent informel » ou
        équivalent, client à relation occasionnelle, dépôts espèces répétés sur une
        courte période sans lien avec l'activité déclarée. Ce schéma correspond à un
        contournement des circuits de transfert agréés.

        Typologie 2 — Intégration par biens de valeur
        Signaux d'alerte : activité déclarée « vente de véhicules d'occasion » ou
        commerce de biens de valeur similaire, dépôts fractionnés sous les seuils de
        déclaration, absence de justificatif d'origine des fonds.

        Typologie 3 — Activité générique et montants élevés
        Signaux d'alerte : activité déclarée très générique (« commerce général »),
        montants déposés significativement supérieurs à ce que l'activité déclarée
        laisserait attendre. Vigilance renforcée recommandée sur l'origine des fonds.
        TEXTE;

        $fichier = UploadedFile::fake()->createWithContent('typologies-demo.txt', $contenu);

        $traiteur->traiter($fichier, [
            'reseau_id' => $reseau?->id,
            'agence_id' => null,
            'visible_caissier' => false,
            'visible_responsable_agence' => true,
        ], $admin?->id);

        $this->command?->info('BibliothequeDocumentaireDemoSeeder : document de démonstration chargé.');
    }
}
