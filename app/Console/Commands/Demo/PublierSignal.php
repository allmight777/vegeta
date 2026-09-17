<?php

namespace App\Console\Commands\Demo;

use App\Models\Alerte;
use Illuminate\Console\Command;

/**
 * Simulation du niveau 2 (canal inter-réseaux) — voir 05_PROMPT_MVP_RECENTRE.md §8.
 *
 * Ce MVP ne construit PAS de vrai canal réseau : cette commande démontre uniquement
 * le principe (une alerte PPE créée dans un réseau peut faire apparaître, dans un
 * autre réseau, un signal sans aucune identité — juste un type de risque et une
 * empreinte) en l'affichant en console, sans rien persister ni exposer de nom.
 * En production, ce canal serait un service séparé ; le contrat qu'il implémenterait
 * (interroger/publier un signal) est déjà pressenti par MoteurFiltrage côté filtrage.
 */
class PublierSignal extends Command
{
    protected $signature = 'demo:publier-signal {--alerte= : id d\'une alerte filtrage_ppe ou filtrage_sanction}';

    protected $description = 'Simule la publication d\'un signal de risque sans identité vers un autre réseau (niveau 2, simulé).';

    public function handle(): int
    {
        $alerte = $this->trouverAlerte();

        if ($alerte === null) {
            $this->error('Aucune alerte filtrage_ppe/filtrage_sanction trouvée. Précisez --alerte=<id> ou lancez `demo:scenario 2` ou `4` d\'abord.');

            return self::FAILURE;
        }

        $client = $alerte->loadMissing('client.personnePhysique', 'client.personneMorale', 'client.reseau')->client;
        $empreinteHex = substr(bin2hex((string) ($client->personnePhysique?->empreinte_nom ?? $client->personneMorale?->empreinte_nom)), 0, 16);

        $this->info('Signal publié (simulation — aucun canal réel, aucune identité transmise) :');
        $this->line('  type de risque : '.$alerte->type->value);
        $this->line('  réseau émetteur : '.$client->reseau->nom);
        $this->line('  empreinte (extrait) : '.$empreinteHex.'…');
        $this->line('  consultable par : tout autre réseau participant au canal (simulé)');
        $this->newLine();
        $this->comment('À l\'oral : « Nous simulons ici deux réseaux dans la même base pour la démo ; en '.
            'production, ce canal serait un service séparé — le contrat d\'interface est déjà écrit pour ça. »');

        return self::SUCCESS;
    }

    private function trouverAlerte(): ?Alerte
    {
        $id = $this->option('alerte');

        if ($id !== null) {
            return Alerte::find($id);
        }

        return Alerte::whereIn('type', ['filtrage_ppe', 'filtrage_sanction'])->latest()->first();
    }
}
