<?php

namespace App\Services\Assistance;

use App\Contracts\ProviderIa;
use App\Models\Admin;
use App\Models\Agent;

/**
 * Recherche par mots-clés simples — pas d'IA générative (08_PROMPT §4.3). Reconnaît d'abord les échanges conversationnels
 * (Services\Assistance\ReconnaisseurConversation), puis essaie le routage d'outils (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §3, pour les questions du
 * type "quels sont les profils incomplets"), puis retombe sur la base de connaissances
 * locale. C'est le mode recommandé pour la démonstration devant le jury
 * (docs/DECISIONS.md).
 */
class ProviderIaSimulateur implements ProviderIa
{
    public function __construct(
        private readonly BaseConnaissances $baseConnaissances,
        private readonly RouteurOutilsMotsCles $routeurOutils,
        private readonly ReconnaisseurConversation $conversation,
    ) {}

    public function repondre(string $question, array $contexte, array $outils, Agent|Admin $utilisateur, array $historique = []): string
    {
        // Salutations, politesse, « qui es-tu » : avant tout routage, jamais le message d'échec.
        $reponseConversation = $this->conversation->repondre($question, $utilisateur);

        if ($reponseConversation !== null) {
            return $reponseConversation;
        }

        if ($outils !== []) {
            $reponseOutil = $this->routeurOutils->router($question, $outils, $utilisateur);

            if ($reponseOutil !== null) {
                return $reponseOutil;
            }
        }

        $trouve = $this->baseConnaissances->rechercher($question);

        return $trouve['reponse'] ?? GestionnaireAssistant::AUCUNE_REPONSE;
    }
}
