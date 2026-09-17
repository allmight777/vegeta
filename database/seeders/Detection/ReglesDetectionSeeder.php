<?php

namespace Database\Seeders\Detection;

use App\Models\RegleDetection;
use Illuminate\Database\Seeder;

/**
 * Volontairement 4 règles pour ce MVP resserré (05_PROMPT_MVP_RECENTRE.md §11) — pas
 * les 23 règles du prompt initial. Seuils "demo" à calibrer avec les mentors métier.
 */
class ReglesDetectionSeeder extends Seeder
{
    public function run(): void
    {
        $regles = [
            [
                'code' => 'FRACTIONNEMENT_GUICHET',
                'libelle' => 'Fractionnement au guichet',
                'parametres' => ['seuil' => 1000000, 'fenetre_heures' => 48, 'min_operations' => 2],
                'source' => 'demo',
                'reference_texte' => 'Loi art. 17 i) — hypothèse de seuil à calibrer',
            ],
            [
                'code' => 'FRACTIONNEMENT_MULTI_AGENCES',
                'libelle' => 'Fractionnement multi-agences',
                'parametres' => ['seuil' => 1500000, 'fenetre_jours' => 7, 'min_operations' => 2, 'seuil_rapprochement' => 0.85],
                'source' => 'demo',
                'reference_texte' => 'Loi art. 72 al. 3, art. 17 i) — hypothèse de seuil à calibrer',
            ],
            [
                'code' => 'SEUIL_MENSUEL_CENTIF',
                'libelle' => 'Seuil mensuel de déclaration CENTIF',
                'parametres' => ['seuil' => 15000000],
                'source' => 'briefing_cif',
                'reference_texte' => 'Annoncé oralement par la CIF le 17/09/2026 ; à confirmer dans la Décision n°021/2023/CM/UMOA',
            ],
            [
                'code' => 'COMPTE_DORMANT_REACTIVE',
                'libelle' => 'Compte dormant réactivé',
                'parametres' => ['mois_inactivite' => 6, 'montant_min' => 100000],
                'source' => 'demo',
                'reference_texte' => 'Briefing CIF — durée d\'inactivité à confirmer avec les mentors',
            ],
        ];

        foreach ($regles as $regle) {
            RegleDetection::updateOrCreate(['code' => $regle['code']], $regle + ['actif' => true]);
        }
    }
}
