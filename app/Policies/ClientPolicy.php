<?php

namespace App\Policies;

use App\Enums\StatutFiltrage;
use App\Models\Agent;
use App\Models\Client;
use App\Services\Kyc\CalculateurCompletude;

class ClientPolicy
{
    /**
     * Refuse toute opération tant qu'un champ KYC bloquant manque, ou qu'un signataire
     * de la personne morale reste "à vérifier" côté filtrage (§5.3, §5.4).
     */
    public function peutValiderOperation(Agent $agent, Client $client): bool
    {
        if (app(CalculateurCompletude::class)->champsBloquantsManquants($client) !== []) {
            return false;
        }

        $signataireAVerifier = $client->personneMorale?->signataires()
            ->where('statut_filtrage', StatutFiltrage::AVerifier->value)
            ->exists();

        return ! $signataireAVerifier;
    }
}
