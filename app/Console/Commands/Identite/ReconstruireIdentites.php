<?php

namespace App\Console\Commands\Identite;

use App\Enums\TypeClient;
use App\Models\Client;
use App\Services\Identite\ResolveurIdentite;
use Illuminate\Console\Command;

/**
 * Rattrapage : crée les identités des fiches déjà en base (avant cette fonctionnalité,
 * ou importées en masse). Idempotent — relançable sans créer de doublon.
 *
 *   php artisan identites:reconstruire
 *   php artisan identites:reconstruire --force   (recalcule aussi les fiches déjà rattachées)
 */
class ReconstruireIdentites extends Command
{
    protected $signature = 'identites:reconstruire {--force : recalcule aussi les fiches déjà rattachées}';

    protected $description = 'Rattache les fiches clients existantes à leur identité (NPI puis empreinte)';

    public function handle(ResolveurIdentite $resolveur): int
    {
        $requete = Client::where('type', TypeClient::PersonnePhysique)->with('personnePhysique');

        if (! $this->option('force')) {
            $requete->whereNull('identite_id');
        }

        $clients = $requete->get();
        $this->info($clients->count().' fiche(s) à traiter.');

        $barre = $this->output->createProgressBar($clients->count());
        foreach ($clients as $client) {
            $resolveur->rattacher($client);
            $barre->advance();
        }
        $barre->finish();

        $this->newLine(2);
        $this->info('Terminé. Vérifiez les rapprochements proposés dans la file de fusion.');

        return self::SUCCESS;
    }
}
