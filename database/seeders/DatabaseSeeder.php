<?php

namespace Database\Seeders;

use Database\Seeders\Demo\DemoCoreBankingSeeder;
use Database\Seeders\Detection\ReglesDetectionSeeder;
use Database\Seeders\Listes\ListesDemoSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ReferentielDemoSeeder::class,
            ListesDemoSeeder::class,
            ReglesDetectionSeeder::class,
            DemoComptesSeeder::class,
            DemoCoreBankingSeeder::class,
            // Scénarios de démonstration ajoutés au fil des phases suivantes.
        ]);
    }
}
