<?php

namespace App\Services\Empreinte;

use App\Support\Bitset;

class ComparateurEmpreinte
{
    public function dice(Bitset $a, Bitset $b): float
    {
        $intersection = $a->et($b)->nbBitsActifs();
        $total = $a->nbBitsActifs() + $b->nbBitsActifs();

        return $total === 0 ? 0.0 : (2 * $intersection) / $total;
    }

    /**
     * Score composite §6.1 : 0.7 nom + 0.3 date quand la date est disponible,
     * nom seul sinon (avec un seuil d'alerte plus haut appliqué côté appelant).
     */
    public function scoreComposite(float $diceNom, ?float $diceDate): float
    {
        return $diceDate === null ? $diceNom : (0.7 * $diceNom + 0.3 * $diceDate);
    }
}
