<?php

namespace App\Console\Commands\Filtrage;

use App\Models\Reseau;
use App\Services\Audit\Consignateur;
use App\Services\Filtrage\RefiltrageParc;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

/**
 * Rejoue le filtrage sanctions/PPE sur le parc existant après publication d'une
 * liste. Appelée automatiquement en fin d'import (ImporterListe), et disponible
 * manuellement pour une campagne de rattrapage.
 */
class RefiltrerParc extends Command
{
    protected $signature = 'listes:refiltrer
        {--reseau= : code du réseau à refiltrer (tous les réseaux par défaut)}
        {--publiee-le= : date/heure de publication de la liste (AAAA-MM-JJ ou AAAA-MM-JJ HH:MM) pour mesurer le délai réglementaire}
        {--recalculer-empreintes : recalcule d\'abord les empreintes manquantes des entrées de liste}';

    protected $description = 'Recontrôle tout le parc clients contre les listes sanctions/PPE (obligation « sans délai », 24 h).';

    public function handle(RefiltrageParc $refiltrage): int
    {
        if ($this->option('recalculer-empreintes')) {
            $corrigees = $refiltrage->recalculerEmpreintesListes();
            $this->line("  Empreintes recalculées : {$corrigees} entrée(s) de liste.");
        }

        $publieeLe = null;

        if ($valeur = $this->option('publiee-le')) {
            try {
                $publieeLe = Carbon::parse($valeur);
            } catch (Throwable) {
                $this->error('Date de publication illisible. Format attendu : AAAA-MM-JJ ou "AAAA-MM-JJ HH:MM".');

                return self::FAILURE;
            }
        }

        $reseau = null;

        if ($code = $this->option('reseau')) {
            $reseau = Reseau::where('code', $code)->first();

            if ($reseau === null) {
                $this->error("Réseau « {$code} » introuvable.");

                return self::FAILURE;
            }
        }

        $barre = null;
        $progression = function (int $traites, int $total) use (&$barre) {
            if ($barre === null) {
                $barre = $this->output->createProgressBar(max($total, 1));
                $barre->start();
            }
            $barre->setProgress(min($traites, $barre->getMaxSteps()));
        };

        $rapport = $reseau === null
            ? $refiltrage->refiltrerTout($publieeLe, $progression)
            : $refiltrage->refiltrerReseau($reseau->id, $publieeLe, $progression);

        $barre?->finish();
        $this->newLine(2);

        $this->info($rapport->resume());

        if ($rapport->echeanceRespectee() === false) {
            $this->error('  Échéance réglementaire de 24 h dépassée — à justifier au contrôle.');
        }

        // Preuve de diligence : la campagne elle-même entre dans le journal d'audit
        // chaîné, pas seulement ses résultats. Aucune donnée d'identité n'y figure.
        Consignateur::enregistrer(
            acteurType: 'systeme',
            acteurId: null,
            action: 'refiltrage_parc_execute',
            cibleType: 'campagne_refiltrage',
            cibleId: sprintf(
                '%d dossiers / %d correspondances / %ss%s',
                $rapport->dossiersTraites(),
                $rapport->correspondancesTrouvees(),
                number_format($rapport->dureeSecondes(), 2, '.', ''),
                $rapport->echeanceRespectee() === null
                    ? ''
                    : ($rapport->echeanceRespectee() ? ' / echeance_ok' : ' / echeance_depassee'),
            ),
        );

        return self::SUCCESS;
    }
}
