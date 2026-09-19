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
 *    « Commerçante » » ; ou taper « etu », « agr », « com » → liste de propositions.
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

            // Variété de professions/activités pour les propositions pendant la frappe
            // (17_PROMPT §1) : « etu » → Étudiant, « agr » → Agriculteur, « cou » → Couturière,
            // « men » → Menuisier, « mec » → Mécanicien, « cha » → Chauffeur, « coi » → Coiffeuse,
            // « ele » → Éleveur.
            [$dassa, ['nom' => 'KINTOKPON', 'prenoms' => 'Arnaud', 'date_naissance' => '2003-03-08', 'profession' => 'Étudiant', 'revenus_mensuels_estimes' => 20000]],
            [$savalou, ['nom' => 'LOKOSSOU', 'prenoms' => 'Grâce', 'date_naissance' => '2002-11-19', 'profession' => 'Étudiant', 'revenus_mensuels_estimes' => 15000]],
            [$dassa, ['nom' => 'GOUNOU', 'prenoms' => 'Basile', 'date_naissance' => '1971-08-27', 'profession' => 'Agriculteur', 'activite_1' => 'Culture de maïs et de manioc', 'revenus_mensuels_estimes' => 50000]],
            [$savalou, ['nom' => 'TCHOBO', 'prenoms' => 'Judith', 'date_naissance' => '1984-01-14', 'profession' => 'Couturière', 'activite_1' => 'Couture', 'revenus_mensuels_estimes' => 45000]],
            [$dassa, ['nom' => 'ALLADAYE', 'prenoms' => 'Prisca', 'date_naissance' => '1993-06-30', 'profession' => 'Couturière', 'activite_1' => 'Couture', 'revenus_mensuels_estimes' => 42000]],
            [$savalou, ['nom' => 'FAGBEMI', 'prenoms' => 'Théophile', 'date_naissance' => '1980-10-05', 'profession' => 'Menuisier', 'revenus_mensuels_estimes' => 65000]],
            [$dassa, ['nom' => 'AKPO', 'prenoms' => 'Romaric', 'date_naissance' => '1989-04-22', 'profession' => 'Mécanicien', 'revenus_mensuels_estimes' => 70000]],
            [$savalou, ['nom' => 'DJIDJOHO', 'prenoms' => 'Anicet', 'date_naissance' => '1976-12-09', 'profession' => 'Chauffeur', 'revenus_mensuels_estimes' => 58000]],
            [$dassa, ['nom' => 'BIAOU', 'prenoms' => 'Sènami', 'date_naissance' => '1997-07-11', 'profession' => 'Coiffeuse', 'revenus_mensuels_estimes' => 38000]],
            [$savalou, ['nom' => 'SANNI', 'prenoms' => 'Moussa', 'date_naissance' => '1969-02-25', 'profession' => 'Éleveur', 'activite_1' => 'Élevage de volaille', 'revenus_mensuels_estimes' => 52000]],
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
