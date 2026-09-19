<?php

namespace App\Services\Coherence\Indicateurs;

use App\Models\Client;
use App\Services\Coherence\Constat;
use App\Services\Coherence\ContexteTransactionnel;

/**
 * Indicateur 1 — les sommes déposées sont sans rapport avec le revenu déclaré
 * au KYC.
 *
 * C'est l'écart le plus parlant et le plus difficile à justifier : un membre
 * qui déclare 40 000 XOF par mois et dépose 2,8 M en six semaines a, soit
 * sous-déclaré son activité, soit une source de fonds à expliquer. Dans les
 * deux cas, la fiche KYC est fausse et doit être reprise.
 */
class EcartFluxRevenus implements Indicateur
{
    public function code(): string
    {
        return 'ecart_flux_revenus';
    }

    public function evaluer(Client $client, ContexteTransactionnel $contexte): ?Constat
    {
        $config = config('coherence.ecart_flux_revenus');
        $revenus = $contexte->revenusMensuelsDeclares;

        // Sans revenu déclaré, l'écart n'a pas de sens : c'est un problème de
        // complétude KYC, déjà traité par CalculateurCompletude.
        if ($revenus <= 0.0) {
            return null;
        }

        $fenetre = (int) $config['fenetre_jours'];
        $cumul = $contexte->cumulDepots($fenetre);

        if ($cumul < (float) $config['montant_plancher']) {
            return null;
        }

        // Revenu ramené à la fenêtre d'observation.
        $revenuSurFenetre = $revenus * ($fenetre / 30);
        $ratio = $cumul / $revenuSurFenetre;

        if ($ratio < (float) $config['ratio_attention']) {
            return null;
        }

        $fort = $ratio >= (float) $config['ratio_fort'];

        return new Constat(
            code: $this->code(),
            libelle: sprintf(
                'Les dépôts des %d derniers jours représentent %s fois le revenu déclaré au KYC.',
                $fenetre,
                number_format($ratio, 1, ',', ' '),
            ),
            poids: (int) ($fort ? $config['poids_fort'] : $config['poids_attention']),
            fait: [
                'cumul_depots' => round($cumul, 2),
                'revenus_mensuels_declares' => round($revenus, 2),
                'fenetre_jours' => $fenetre,
                'ratio' => round($ratio, 2),
                'seuil_applique' => $fort ? (float) $config['ratio_fort'] : (float) $config['ratio_attention'],
                'source_seuil' => 'demo',
            ],
        );
    }
}
