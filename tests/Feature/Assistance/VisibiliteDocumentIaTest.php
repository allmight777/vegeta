<?php

namespace Tests\Feature\Assistance;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\DocumentIa;
use App\Models\Reseau;
use App\Services\Assistance\Outils\OutilRechercheDocumentaire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §8 : un document marqué
 * `visible_caissier = false` n'apparaît jamais dans les résultats de
 * OutilRechercheDocumentaire pour un compte caissier.
 */
class VisibiliteDocumentIaTest extends TestCase
{
    use RefreshDatabase;

    private function caissier(Agence $agence): Agent
    {
        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Caissier',
            'matricule' => 'CAI-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Caissier,
        ]);
    }

    private function responsable(Agence $agence): Agent
    {
        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Responsable',
            'matricule' => 'RES-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::ResponsableAgence,
        ]);
    }

    public function test_un_document_non_visible_au_caissier_najamais_apparait_dans_ses_resultats(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
        $caissier = $this->caissier($agence);

        DocumentIa::create([
            'titre' => 'Procédure interne confidentielle',
            'nom_fichier_original' => 'procedure.pdf',
            'chemin_fichier' => 'fake/procedure.pdf',
            'type_mime' => 'application/pdf',
            'taille_octets' => 100,
            'contenu_extrait' => 'Cette procédure décrit le traitement interne des dossiers sensibles.',
            'statut_extraction' => 'reussie',
            'visible_caissier' => false,
            'visible_responsable_agence' => true,
            'visible_administrateur' => true,
        ]);

        $resultat = app(OutilRechercheDocumentaire::class)->executer(['requete' => 'procédure interne dossiers'], $caissier);

        $this->assertSame([], $resultat['resultats']);
    }

    public function test_un_document_visible_au_caissier_apparait_dans_ses_resultats(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
        $caissier = $this->caissier($agence);

        DocumentIa::create([
            'titre' => 'Guide caisse',
            'nom_fichier_original' => 'guide.pdf',
            'chemin_fichier' => 'fake/guide.pdf',
            'type_mime' => 'application/pdf',
            'taille_octets' => 100,
            'contenu_extrait' => 'Ce guide explique la procédure de caisse au quotidien.',
            'statut_extraction' => 'reussie',
            'visible_caissier' => true,
            'visible_responsable_agence' => true,
            'visible_administrateur' => true,
        ]);

        $resultat = app(OutilRechercheDocumentaire::class)->executer(['requete' => 'procedure caisse'], $caissier);

        $this->assertCount(1, $resultat['resultats']);
        $this->assertSame('Guide caisse', $resultat['resultats'][0]['titre']);
    }

    public function test_le_responsable_dagence_voit_un_document_reserve_a_son_role(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
        $responsable = $this->responsable($agence);

        DocumentIa::create([
            'titre' => 'Procédure interne confidentielle',
            'nom_fichier_original' => 'procedure.pdf',
            'chemin_fichier' => 'fake/procedure.pdf',
            'type_mime' => 'application/pdf',
            'taille_octets' => 100,
            'contenu_extrait' => 'Cette procédure décrit le traitement interne des dossiers sensibles.',
            'statut_extraction' => 'reussie',
            'visible_caissier' => false,
            'visible_responsable_agence' => true,
            'visible_administrateur' => true,
        ]);

        $resultat = app(OutilRechercheDocumentaire::class)->executer(['requete' => 'procédure interne dossiers'], $responsable);

        $this->assertCount(1, $resultat['resultats']);
    }

    public function test_un_document_dune_autre_agence_najamais_apparait(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agenceA = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence A', 'code' => 'AA']);
        $agenceB = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence B', 'code' => 'AB']);
        $caissierA = $this->caissier($agenceA);

        DocumentIa::create([
            'titre' => 'Note agence B',
            'nom_fichier_original' => 'note.pdf',
            'chemin_fichier' => 'fake/note.pdf',
            'type_mime' => 'application/pdf',
            'taille_octets' => 100,
            'contenu_extrait' => 'Note interne réservée à cette agence uniquement.',
            'statut_extraction' => 'reussie',
            'visible_caissier' => true,
            'visible_responsable_agence' => true,
            'visible_administrateur' => true,
            'agence_id' => $agenceB->id,
        ]);

        $resultat = app(OutilRechercheDocumentaire::class)->executer(['requete' => 'note interne reservee agence'], $caissierA);

        $this->assertSame([], $resultat['resultats']);
    }
}
