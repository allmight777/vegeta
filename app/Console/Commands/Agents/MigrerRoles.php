<?php

namespace App\Console\Commands\Agents;

use App\Enums\RoleAgent;
use App\Models\Agent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Migration de données : fusionne les anciens rôles agent (`guichet`, `responsable_lbcft`,
 * `direction`) vers les deux nouveaux (`caissier`, `responsable_agence`). Idempotente — relançable
 * sans effet une fois tous les comptes migrés. Ne supprime jamais de compte : un doublon
 * `responsable_lbcft` + `direction` sur la même agence devient deux comptes `responsable_agence`.
 *
 *   php artisan agents:migrer-roles
 */
class MigrerRoles extends Command
{
    protected $signature = 'agents:migrer-roles';

    protected $description = 'Migre agents.role des anciennes valeurs (guichet, responsable_lbcft, direction) vers les nouvelles (caissier, responsable_agence)';

    private const CORRESPONDANCE = [
        'guichet' => RoleAgent::Caissier,
        'responsable_lbcft' => RoleAgent::ResponsableAgence,
        'direction' => RoleAgent::ResponsableAgence,
    ];

    public function handle(): int
    {
        DB::transaction(function () {
            foreach (self::CORRESPONDANCE as $ancienRole => $nouveauRole) {
                $nombre = Agent::where('role', $ancienRole)->count();

                if ($nombre === 0) {
                    continue;
                }

                Agent::where('role', $ancienRole)->update(['role' => $nouveauRole->value]);
                $this->info("{$nombre} compte(s) migré(s) de « {$ancienRole} » vers « {$nouveauRole->value} ».");
            }
        });

        $this->newLine();
        $this->info('Terminé. Aucun compte supprimé.');

        return self::SUCCESS;
    }
}
