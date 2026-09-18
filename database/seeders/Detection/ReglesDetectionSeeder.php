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
                // seuil_unitaire : au-dessous duquel chaque dépôt passe inaperçu ;
                // seuil_cumul : au-dessus duquel la somme devient déclarable.
                'parametres' => ['seuil_unitaire' => 5000000, 'seuil_cumul' => 15000000, 'fenetre_heures' => 48, 'min_operations' => 2],
                'source' => 'demo',
                'reference_texte' => 'Loi art. 17 i) — hypothèse de seuil à calibrer',
            ],
            [
                'code' => 'FRACTIONNEMENT_MULTI_AGENCES',
                'libelle' => 'Fractionnement multi-agences',
                'parametres' => ['seuil_unitaire' => 5000000, 'seuil_cumul' => 15000000, 'fenetre_jours' => 7, 'min_operations' => 2],
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
            [
                'code' => 'PLAFOND_QUOTIDIEN_IDENTITE',
                'libelle' => 'Plafond quotidien espèces, tous comptes de la personne',
                'parametres' => ['ratio_alerte_approche' => 0.8],
                'source' => 'demo',
                'reference_texte' => "Loi art. 17 i) — opérations en espèces multiples d'une même personne dans la journée "
                    .'considérées comme une opération unique ; plafond calculé sur le profil (config/identite.php)',
            ],
        ];

        foreach ($regles as $regle) {
            RegleDetection::updateOrCreate(['code' => $regle['code']], $regle + ['actif' => true]);
        }
    }
}
