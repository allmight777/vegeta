<?php

namespace App\Policies;

use App\Enums\StatutAlerte;
use App\Enums\StatutFiltrage;
use App\Enums\TypeAlerte;
use App\Models\Agent;
use App\Models\Client;
use App\Services\Kyc\CalculateurCompletude;

class ClientPolicy
{
    /**
     * Refuse toute opération tant qu'un champ KYC bloquant manque, qu'un signataire de
     * la personne morale reste "à vérifier" côté filtrage (§5.3, §5.4), ou qu'un NPI
     * s'est révélé invalide après une vérification différée non encore traitée
     * (07_PROMPT_MODE_DEGRADE_NPI_OCR §2.4 — jamais de blocage pour une simple attente
     * de connexion, seulement pour une invalidité confirmée).
     */
    public function peutValiderOperation(Agent $agent, Client $client): bool
    {
        if (app(CalculateurCompletude::class)->champsBloquantsManquants($client) !== []) {
            return false;
        }

        $signataireAVerifier = $client->personneMorale?->signataires()
            ->where('statut_filtrage', StatutFiltrage::AVerifier->value)
            ->exists();

        if ($signataireAVerifier) {
            return false;
        }

        $npiInvalideNonTraite = $client->alertes()
            ->where('type', TypeAlerte::NpiInvalideApresVerification->value)
            ->where('statut', '!=', StatutAlerte::Traitee->value)
            ->exists();

        return ! $npiInvalideNonTraite;
    }
}
