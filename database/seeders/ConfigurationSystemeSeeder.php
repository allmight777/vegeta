<?php

namespace Database\Seeders;

use App\Models\ConfigurationSysteme;
use App\Services\Configuration\IdentiteSysteme;
use Illuminate\Database\Seeder;

class ConfigurationSystemeSeeder extends Seeder
{
    /**
     * Ligne unique de configuration, aux valeurs d'origine (n'écrase jamais une
     * configuration existante).
     */
    public function run(): void
    {
        if (ConfigurationSysteme::query()->exists()) {
            return;
        }

        ConfigurationSysteme::create(IdentiteSysteme::defauts());
        IdentiteSysteme::oublier();
    }
}
