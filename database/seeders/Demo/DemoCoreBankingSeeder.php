<?php

namespace Database\Seeders\Demo;

use App\Models\Agence;
use App\Models\Reseau;
use App\Services\Empreinte\ServiceEmpreinte;
use App\Services\Filtrage\MoteurFiltrage;
use App\Services\Import\ImportateurCsv;
use App\Services\Kyc\CalculateurCompletude;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Génère un CSV fictif « export core banking », volontairement incomplet, et le fait
 * passer par le vrai pipeline d'import (problème 1). Données entièrement synthétiques.
 */
class DemoCoreBankingSeeder extends Seeder
{
    public function run(ImportateurCsv $importateur): void
    {
        if (! config('cif_demo.actif')) {
            return;
        }

        $agence = Agence::where('reseau_id', Reseau::where('code', 'ALPHA')->value('id'))
            ->where('code', 'DASSA')
            ->firstOrFail();

        $entetes = array_keys(ImportateurCsv::CHAMPS_CIBLES);
        $lignes = [
            // La plus complète (il manque encore l'expiration de la pièce, jamais fournie par un export core banking).
            ['nom' => 'KPADONOU', 'prenoms' => 'Fidèle', 'date_naissance' => '1988-03-14', 'lieu_naissance' => 'Dassa-Zoumè', 'piece_identite_numero' => 'CIP-000001', 'adresse' => 'Quartier Zongo, Dassa', 'profession' => 'Commerçante', 'revenus_mensuels_estimes' => '85000'],
            // Bloquante : pas de pièce d'identité ni de date de naissance.
            ['nom' => 'AHOUANDJINOU', 'prenoms' => 'Rachidatou', 'date_naissance' => '', 'lieu_naissance' => 'Savalou', 'piece_identite_numero' => '', 'adresse' => 'Savalou centre', 'profession' => 'Artisane', 'revenus_mensuels_estimes' => '60000'],
            // Bloquante : pas de pièce d'identité.
            ['nom' => 'DOSSOU', 'prenoms' => 'Marcelin', 'date_naissance' => '1975-11-02', 'lieu_naissance' => 'Bohicon', 'piece_identite_numero' => '', 'adresse' => 'Bohicon gare', 'profession' => 'Agriculteur', 'revenus_mensuels_estimes' => '45000'],
            // Non bloquante : lieu de naissance et profession manquants.
            ['nom' => 'HOUNKPATIN', 'prenoms' => 'Bernadette', 'date_naissance' => '1992-07-22', 'lieu_naissance' => '', 'piece_identite_numero' => 'CIP-000004', 'adresse' => 'Dassa, quartier gare', 'profession' => '', 'revenus_mensuels_estimes' => '70000'],
            // Non bloquante : adresse et revenus manquants.
            ['nom' => 'ADJOVI', 'prenoms' => 'Éric', 'date_naissance' => '1983-01-30', 'lieu_naissance' => 'Dassa-Zoumè', 'piece_identite_numero' => 'CIP-000005', 'adresse' => '', 'profession' => 'Enseignant', 'revenus_mensuels_estimes' => ''],
            // Presque tout manque (hors nom/prénoms).
            ['nom' => 'TOSSOU', 'prenoms' => 'Alphonsine', 'date_naissance' => '', 'lieu_naissance' => '', 'piece_identite_numero' => '', 'adresse' => '', 'profession' => '', 'revenus_mensuels_estimes' => ''],
        ];

        $chemin = 'imports/demo-core-banking.csv';
        $flux = fopen('php://temp', 'r+');
        fputcsv($flux, $entetes);
        foreach ($lignes as $ligne) {
            fputcsv($flux, array_map(fn ($cle) => $ligne[$cle] ?? '', $entetes));
        }
        rewind($flux);
        Storage::disk('local')->put($chemin, stream_get_contents($flux));
        fclose($flux);

        $mapping = array_combine($entetes, $entetes);

        $lot = $importateur->importer($chemin, $mapping, $agence, app(ServiceEmpreinte::class), app(CalculateurCompletude::class), app(MoteurFiltrage::class));

        $this->command?->info("DemoCoreBankingSeeder : {$lot->nombre_lignes} ligne(s) importée(s), {$lot->nombre_a_completer} à compléter.");
    }
}
