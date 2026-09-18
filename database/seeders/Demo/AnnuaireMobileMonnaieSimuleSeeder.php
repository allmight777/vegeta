<?php

namespace Database\Seeders\Demo;

use App\Enums\OperateurMobileMonnaie;
use App\Enums\SourceValeur;
use App\Models\CompteMobileMonnaieSimule;
use App\Services\Securite\IndexAveugle;
use Illuminate\Database\Seeder;

/**
 * Annuaire numéro → titulaire entièrement synthétique, jeu fixe et déterministe (aucun
 * Faker : mêmes noms béninois que les autres jeux de démo du dépôt). Remplace un vrai
 * appel USSD/API opérateur — voir la migration create_comptes_mobile_monnaie_simules_table
 * et docs/DECISIONS.md pour le contexte de cette décision.
 */
class AnnuaireMobileMonnaieSimuleSeeder extends Seeder
{
    public function run(IndexAveugle $indexAveugle): void
    {
        if (! config('cif_demo.actif')) {
            return;
        }

        $comptes = [
            ['telephone' => '90000001', 'nom_titulaire' => 'KPADONOU Fidèle'],
            ['telephone' => '90000010', 'nom_titulaire' => 'AHOUANDJINOU Rachidatou'],
            ['telephone' => '61000020', 'nom_titulaire' => 'GBAGUIDI Sèmiyou'],
            ['telephone' => '96000030', 'nom_titulaire' => 'HOUNKPATIN Bernadette'],
            ['telephone' => '45000040', 'nom_titulaire' => 'ADJOVI Éric'],
            ['telephone' => '55000050', 'nom_titulaire' => 'TOSSOU Alphonsine'],
            ['telephone' => '94000060', 'nom_titulaire' => 'DOSSOU Marcelin'],
            ['telephone' => '63000070', 'nom_titulaire' => 'ALIDOU Roukiatou'],
            ['telephone' => '21000080', 'nom_titulaire' => 'GNONLONFOUN Cyriaque'],
            ['telephone' => '40000090', 'nom_titulaire' => 'SOSSOU Marceline'],
            ['telephone' => '93000015', 'nom_titulaire' => 'AKPOVI Boniface'],
            ['telephone' => '47000025', 'nom_titulaire' => 'DAGNON Colette'],
        ];

        foreach ($comptes as $compte) {
            $operateur = OperateurMobileMonnaie::depuisPrefixe($compte['telephone']);

            if ($operateur === null) {
                continue;
            }

            CompteMobileMonnaieSimule::firstOrCreate(
                ['telephone_idx' => $indexAveugle->calculer($compte['telephone'], 'telephone')],
                [
                    'telephone' => $compte['telephone'],
                    'operateur' => $operateur,
                    'nom_titulaire' => $compte['nom_titulaire'],
                    'source' => SourceValeur::Demo,
                ],
            );
        }
    }
}
