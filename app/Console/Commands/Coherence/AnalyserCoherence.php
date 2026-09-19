<?php

namespace App\Console\Commands\Coherence;

use App\Models\Client;
use App\Models\Reseau;
use App\Services\Audit\Consignateur;
use App\Services\Coherence\AnalyseurCoherenceProfil;
use Illuminate\Console\Command;

/**
 * Campagne de vigilance constante, prévue pour tourner la nuit
 * (`schedule:work`), comme `ppe:envoyer-recaps`.
 *
 * Les signalements produits partent dans la file du responsable d'agence, pas
 * du caissier : celui-ci ne doit jamais savoir qu'un membre est sous analyse
 * (Loi uniforme art. 63).
 */
class AnalyserCoherence extends Command
{
    protected $signature = 'coherence:analyser
        {--reseau= : code du réseau à analyser (tous par défaut)}
        {--simulation : analyse sans créer aucune alerte}';

    protected $description = 'Confronte le profil KYC déclaré au comportement transactionnel observé.';

    public function handle(AnalyseurCoherenceProfil $analyseur): int
    {
        $requete = Client::query()->with(['personnePhysique', 'comptes']);

        if ($code = $this->option('reseau')) {
            $reseau = Reseau::where('code', $code)->first();

            if ($reseau === null) {
                $this->error("Réseau « {$code} » introuvable.");

                return self::FAILURE;
            }

            $requete->where('reseau_id', $reseau->id);
        }

        $simulation = (bool) $this->option('simulation');
        $analyses = 0;
        $faisceaux = 0;
        $signales = 0;
        $debut = microtime(true);

        $requete->chunkById(100, function ($clients) use ($analyseur, $simulation, &$analyses, &$faisceaux, &$signales) {
            foreach ($clients as $client) {
                $analyses++;
                $faisceau = $analyseur->analyser($client);

                if (! $faisceau->estConstitue()) {
                    continue;
                }

                $faisceaux++;

                $this->line(sprintf(
                    '  <fg=%s>%s</> %s — %d constats, poids %d',
                    $faisceau->gravite()->value === 'critique' ? 'red' : 'yellow',
                    $faisceau->gravite()->value === 'critique' ? '!!' : '! ',
                    $client->nomAffichage(),
                    $faisceau->nombreConstats(),
                    $faisceau->poidsTotal(),
                ));

                if (! $simulation && $analyseur->signaler($client) !== null) {
                    $signales++;
                }
            }
        });

        $duree = round(microtime(true) - $debut, 2);

        $this->newLine();
        $this->info(sprintf(
            '%d dossier(s) analysé(s) en %s s — %d faisceau(x) constitué(s), %d alerte(s) ouverte(s)%s.',
            $analyses,
            number_format($duree, 2, ',', ' '),
            $faisceaux,
            $signales,
            $simulation ? ' (simulation : aucune écriture)' : '',
        ));

        if (! $simulation) {
            Consignateur::enregistrer(
                acteurType: 'systeme',
                acteurId: null,
                action: 'campagne_coherence_executee',
                cibleType: 'campagne_coherence',
                cibleId: sprintf('%d analyses / %d faisceaux / %d alertes', $analyses, $faisceaux, $signales),
            );
        }

        return self::SUCCESS;
    }
}
