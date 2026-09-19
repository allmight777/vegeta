<?php

namespace Database\Seeders;

use Database\Seeders\Demo\AnnuaireMobileMonnaieSimuleSeeder;
use Database\Seeders\Demo\BibliothequeDocumentaireDemoSeeder;
use Database\Seeders\Demo\CopiloteSaisieDemoSeeder;
use Database\Seeders\Demo\DemoCoreBankingSeeder;
use Database\Seeders\Demo\IdentifiantsObligatoiresDemoSeeder;
use Database\Seeders\Demo\MemoireDecisionsDemoSeeder;
use Database\Seeders\Demo\SoupconDemoSeeder;
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
            ConfigurationSystemeSeeder::class,
            ReferentielDemoSeeder::class,
            ListesDemoSeeder::class,
            ReglesDetectionSeeder::class,
            DemoComptesSeeder::class,
            DemoCoreBankingSeeder::class,
            AnnuaireMobileMonnaieSimuleSeeder::class,
            // 13_PROMPT_IA_VISIBLE_DANS_INTERFACE §2.4 : données rendant les
            // fonctionnalités IA immédiatement visibles après ce seed, sans action
            // manuelle préalable.
            CopiloteSaisieDemoSeeder::class,
            SoupconDemoSeeder::class,
            MemoireDecisionsDemoSeeder::class,
            BibliothequeDocumentaireDemoSeeder::class,
            // Doit rester en dernier : complète NPI, téléphone, email et pièce de tous les clients démo.
            IdentifiantsObligatoiresDemoSeeder::class,
            // Scénarios de démonstration ajoutés au fil des phases suivantes.
        ]);
    }
}
