<?php

namespace App\Services\Coherence\Indicateurs;

use App\Models\Client;
use App\Services\Coherence\Constat;
use App\Services\Coherence\ContexteTransactionnel;

/**
 * Indicateur 2 — compte de passage (typologie GIABA classique).
 *
 * Un dépôt suivi du retrait de l'essentiel du montant en quelques heures, et
 * cela de façon répétée. Un compte d'épargne ne se comporte jamais ainsi :
 * c'est la signature d'un compte-relais, où le membre n'est qu'un porteur.
 *
 * Aucun montant unitaire n'a besoin d'être élevé — c'est justement ce qui rend
 * la typologie invisible à une détection par seuil.
 */
class CompteDePassage implements Indicateur
{
    public function code(): string
    {
        return 'compte_de_passage';
    }

    public function evaluer(Client $client, ContexteTransactionnel $contexte): ?Constat
    {
        $config = config('coherence.compte_de_passage');

        $paires = $contexte->allersRetoursRapides(
            (int) $config['fenetre_jours'],
            (int) $config['delai_max_heures'],
            (float) $config['part_retiree_minimum'],
        );

        if (count($paires) < (int) $config['occurrences_minimum']) {
            return null;
        }

        $delaiMoyen = collect($paires)->avg('delai_heures');
        $volume = collect($paires)->sum('montant_depose');

        return new Constat(
            code: $this->code(),
            libelle: sprintf(
                '%d dépôts ont été retirés à plus de %d %% en moyenne %s h plus tard — comportement de compte de passage.',
                count($paires),
                (int) ((float) $config['part_retiree_minimum'] * 100),
                number_format((float) $delaiMoyen, 1, ',', ' '),
            ),
            poids: (int) $config['poids'],
            fait: [
                'occurrences' => count($paires),
                'delai_moyen_heures' => round((float) $delaiMoyen, 1),
                'volume_transite' => round((float) $volume, 2),
                'fenetre_jours' => (int) $config['fenetre_jours'],
                'part_retiree_minimum' => (float) $config['part_retiree_minimum'],
                'source_seuil' => 'demo',
            ],
        );
    }
}
