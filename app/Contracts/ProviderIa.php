<?php

namespace App\Contracts;

use App\Models\Admin;
use App\Models\Agent;

interface ProviderIa
{
    /**
     * @param  array<string, mixed>  $contexte  liste fermée, cf. Services\Assistance\ConstructeurContexteIa
     * @param  array<int, OutilAssistantIa>  $outils  jeu d'outils autorisés pour ce rôle (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §3) — jamais plus que leur description envoyée au fournisseur, l'exécution reste toujours côté PHP
     * @param  array<int, array{role: string, content: string}>  $historique
     */
    public function repondre(string $question, array $contexte, array $outils, Agent|Admin $utilisateur, array $historique = []): string;
}
