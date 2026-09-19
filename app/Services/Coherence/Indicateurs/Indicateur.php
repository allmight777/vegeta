<?php

namespace App\Services\Coherence\Indicateurs;

use App\Models\Client;
use App\Services\Coherence\Constat;
use App\Services\Coherence\ContexteTransactionnel;

/**
 * Un indicateur observe UN écart possible entre le profil déclaré et le
 * comportement observé, et retourne un Constat chiffré — ou null si rien à
 * signaler.
 *
 * Volontairement déterministe : aucun appel réseau, aucune IA, aucun aléa.
 * C'est ce qui rend un signalement opposable en contrôle. La couche IA
 * n'intervient qu'ensuite, pour raconter le faisceau — jamais pour le noter.
 */
interface Indicateur
{
    public function code(): string;

    public function evaluer(Client $client, ContexteTransactionnel $contexte): ?Constat;
}
