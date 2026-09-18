<?php
// app/Console/Commands/Filtrage/EnvoyerRecapsPpeQuotidiens.php

namespace App\Console\Commands\Filtrage;

use App\Services\Filtrage\GenerateurRecapPpeJour;
use Illuminate\Console\Command;

class EnvoyerRecapsPpeQuotidiens extends Command
{
    protected $signature = 'ppe:envoyer-recaps';

    protected $description = 'Envoie le récapitulatif quotidien PPE à chaque responsable d\'agence';

    public function handle(GenerateurRecapPpeJour $generateur): int
    {
        $envoyes = $generateur->envoyerTousLesRecaps();

        $this->info("Récapitulatifs envoyés : {$envoyes} agence(s).");

        return self::SUCCESS;
    }
}
