<?php

namespace App\Services\Kyc;

use App\Contracts\ConnecteurSystemeExistant;
use Illuminate\Support\Facades\Http;

/**
 * Appelle l'API REST du core banking du SFD, si elle existe. Écrite mais non branchable
 * pour ce hackathon : aucun SFD partenaire n'a fourni d'accès à un core banking réel —
 * voir docs/DECISIONS.md. Jamais sélectionnée par défaut
 * (config('kyc.connecteur_systeme_existant') = 'local').
 */
class ConnecteurApiCoreBanking implements ConnecteurSystemeExistant
{
    public function rechercherParCritere(string $type, array $criteres): ?array
    {
        $reponse = Http::withToken((string) config('kyc.core_banking_api_cle'))
            ->timeout(5)
            ->get(rtrim((string) config('kyc.core_banking_api_url'), '/').'/clients/recherche', [
                'type' => $type,
                ...$criteres,
            ]);

        return $reponse->successful() ? ($reponse->json('champs') ?? null) : null;
    }
}
