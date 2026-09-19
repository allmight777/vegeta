<?php

namespace Database\Seeders\Demo;

use App\Enums\SourceCreation;
use App\Models\Agence;
use App\Models\Reseau;
use App\Services\Kyc\CreateurClient;
use Illuminate\Database\Seeder;

/**
 * 16_PROMPT_IA_PARTOUT §2.4 : sans ces dossiers, les trois comportements du copilote de
 * saisie (alerte doublon, incohérence de profil, normalisation d'activité) ne montrent
 * rien et paraissent cassés le jour J. Données 100 % synthétiques.
 *
 * Scénario de démonstration (caissier CAI-0001, agence de Dassa) :
 *  - Doublon : nouveau client, nom « ADJAO » / prénoms « Kofi » → un dossier proche existe à
 *    l'agence de Savalou (« ADJAHO Koffi »), même réseau.
 *  - Incohérence : ouvrir « Compléter » sur le dossier TCHOKPON Sylvain (déclaré retraité à
 *    25 ans, dépôt initial ≈ 83 mois de revenus, pièce expirée).
 *  - Normalisation : saisir « commercante » dans Profession → « 5 dossiers utilisent
 *    « Commerçante » ».
 */
class CopiloteSaisieDemoSeeder extends Seeder
{
    public function run(CreateurClient $createur): void
    {
        if (! config('cif_demo.actif')) {
            return;
        }

        $reseau = Reseau::where('code', 'ALPHA')->firstOrFail();
        $dassa = Agence::where('reseau_id', $reseau->id)->where('code', 'DASSA')->firstOrFail();
        $savalou = Agence::where('reseau_id', $reseau->id)->where('nom', 'like', '%Savalou%')->first() ?? $dassa;

        $dossiers = [
            // [agence, champs]
            [$savalou, ['nom' => 'ADJAHO', 'prenoms' => 'Koffi', 'date_naissance' => '1985-06-12', 'lieu_naissance' => 'Savalou', 'piece_identite_type' => 'cni', 'piece_identite_numero' => 'CIP-810001', 'profession' => 'Enseignant', 'revenus_mensuels_estimes' => 90000]],
            [$dassa, ['nom' => 'TCHOKPON', 'prenoms' => 'Sylvain', 'date_naissance' => '2001-04-10', 'lieu_naissance' => 'Dassa-Zoumè', 'piece_identite_type' => 'cni', 'piece_identite_numero' => 'CIP-810002', 'piece_identite_expiration' => '2024-01-15', 'profession' => 'Retraité', 'revenus_mensuels_estimes' => 60000, 'depot_especes' => 5000000]],
            [$dassa, ['nom' => 'AGBO', 'prenoms' => 'Nathalie', 'date_naissance' => '1990-02-03', 'profession' => 'Commerçante', 'activite_1' => 'Commerce de détail', 'revenus_mensuels_estimes' => 55000]],
            [$dassa, ['nom' => 'SOGLO', 'prenoms' => 'Pélagie', 'date_naissance' => '1987-09-21', 'profession' => 'Commerçante', 'activite_1' => 'Commerce de détail', 'revenus_mensuels_estimes' => 48000]],
            [$savalou, ['nom' => 'HOUESSOU', 'prenoms' => 'Odette', 'date_naissance' => '1979-12-01', 'profession' => 'Commerçante', 'activite_1' => 'Commerce de détail', 'revenus_mensuels_estimes' => 62000]],
            [$savalou, ['nom' => 'ZINSOU', 'prenoms' => 'Clarisse', 'date_naissance' => '1995-05-17', 'profession' => 'Commerçante', 'revenus_mensuels_estimes' => 40000]],
        ];

        foreach ($dossiers as [$agence, $champs]) {
            $client = $createur->creer(
                ['type' => 'personne_physique', 'nature_relation' => 'titulaire_compte'] + $champs,
                $reseau->id,
                SourceCreation::SaisieAgent,
                null,
            );

            // Le créateur rattache l'agence de l'agent connecté : ici aucun, on la fixe.
            $client->update(['agence_creation_id' => $agence->id]);
        }

        $this->command?->info('CopiloteSaisieDemoSeeder : '.count($dossiers).' dossiers de démonstration créés.');
    }
}
