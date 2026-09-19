<?php

namespace Database\Seeders\Demo;

use App\Enums\MotifDecisionFiltrage;
use App\Enums\RoleAgent;
use App\Enums\StatutFiltrage;
use App\Models\Agent;
use App\Models\DecisionFiltrage;
use App\Models\EntreeListe;
use App\Models\Identite;
use App\Models\Reseau;
use App\Services\Securite\IndexAveugle;
use Illuminate\Database\Seeder;

/**
 * 13_PROMPT_IA_VISIBLE_DANS_INTERFACE §2.4 : sans ces décisions déjà enregistrées,
 * l'encart "cas similaires" de la mémoire de décisions (12_PROMPT_IA_INTEGREE_PROFONDE
 * §6) reste vide sur un `migrate:fresh --seed` frais, et la fonctionnalité la plus
 * forte de la démo paraît cassée le jour J.
 *
 * `ListesDemoSeeder` + `DemoCoreBankingSeeder` créent déjà, sans intervention
 * supplémentaire, UNE correspondance "à vérifier" réelle (client AHOUANDJINOU
 * Rachidatou, homonyme exact d'une entrée de liste fictive) — c'est la "nouvelle
 * correspondance en attente" du scénario recommandé. Ce seeder ajoute les décisions
 * ANTÉRIEURES sur cette même entrée de liste, pour trois AUTRES personnes
 * (homonymes déjà tranchés par le passé), afin que l'encart s'affiche dès la
 * première ouverture de l'écran de filtrage par RES-0001 — sans jamais toucher à la
 * correspondance AHOUANDJINOU elle-même, qui doit rester "à vérifier" pour la
 * démonstration live de la décision.
 */
class MemoireDecisionsDemoSeeder extends Seeder
{
    public function run(IndexAveugle $indexAveugle): void
    {
        if (! config('cif_demo.actif')) {
            return;
        }

        $entree = EntreeListe::where(
            'nom_idx',
            $indexAveugle->calculer('AHOUANDJINOU Rachidatou', 'nom')
        )->first();

        if ($entree === null || DecisionFiltrage::where('entree_liste_id', $entree->id)->exists()) {
            return;
        }

        $reseau = Reseau::where('code', 'ALPHA')->firstOrFail();
        $responsable = Agent::where(
            'matricule_idx',
            $indexAveugle->calculer('RES-0001', 'matricule')
        )->where('role', RoleAgent::ResponsableAgence)->first();

        $decisionsPassees = [
            MotifDecisionFiltrage::HomonymeSimple,
            MotifDecisionFiltrage::HomonymeSimple,
            MotifDecisionFiltrage::PieceIdentiteVerifiee,
        ];

        foreach ($decisionsPassees as $motif) {
            $identite = Identite::create(['reseau_id' => $reseau->id]);

            DecisionFiltrage::create([
                'cle_decision' => hash('sha256', $identite->id.'|'.$entree->id.'|demo-memoire'),
                'identite_id' => $identite->id,
                'entree_liste_id' => $entree->id,
                'portee' => 'identite',
                'source_liste' => $entree->source->value,
                'version_liste' => $entree->version_liste,
                'statut' => StatutFiltrage::Ecarte,
                'motif_code' => $motif,
                'decide_par_agent_id' => $responsable?->id,
                'decide_le' => now()->subDays(random_int(5, 60)),
                'expire_le' => now()->addDays((int) config('filtrage.decisions.duree_jours')),
                'applications' => random_int(1, 4),
                'derniere_application_le' => now()->subDays(random_int(1, 4)),
            ]);
        }

        $this->command?->info('MemoireDecisionsDemoSeeder : 3 décisions passées créées sur l\'entrée AHOUANDJINOU.');
    }
}
