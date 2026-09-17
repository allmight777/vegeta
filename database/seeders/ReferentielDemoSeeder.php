<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\Reseau;
use Illuminate\Database\Seeder;

/**
 * Réseaux et agences synthétiques : sert de socle à tous les autres seeders de démo.
 * Deux réseaux dans la même base suffisent à démontrer l'isolation multi-réseaux et,
 * plus tard, la simulation du canal inter-réseaux (voir 05_PROMPT_MVP_RECENTRE.md §8).
 */
class ReferentielDemoSeeder extends Seeder
{
    public function run(): void
    {
        $alpha = Reseau::firstOrCreate(['code' => 'ALPHA'], ['nom' => 'Réseau Alpha']);
        $beta = Reseau::firstOrCreate(['code' => 'BETA'], ['nom' => 'Réseau Beta']);

        foreach ([
            ['reseau' => $alpha, 'code' => 'DASSA', 'nom' => 'Agence de Dassa'],
            ['reseau' => $alpha, 'code' => 'SAVALOU', 'nom' => 'Agence de Savalou'],
            ['reseau' => $alpha, 'code' => 'BOHICON', 'nom' => 'Agence de Bohicon'],
            ['reseau' => $beta, 'code' => 'PARAKOU', 'nom' => 'Agence de Parakou'],
        ] as $donnees) {
            Agence::firstOrCreate(
                ['reseau_id' => $donnees['reseau']->id, 'code' => $donnees['code']],
                ['nom' => $donnees['nom']],
            );
        }
    }
}
