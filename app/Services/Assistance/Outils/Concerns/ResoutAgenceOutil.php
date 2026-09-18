<?php

namespace App\Services\Assistance\Outils\Concerns;

use App\Models\Admin;
use App\Models\Agence;
use App\Models\Agent;

/**
 * Résolution sûre de l'agence ciblée par un outil (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA
 * §0 décision 2) : pour un Agent, toujours son agence, jamais un argument fourni par l'IA
 * — empêche une injection de prompt de faire consulter l'agence d'un autre. Pour un Admin,
 * un argument `agence_id` est accepté mais toujours revalidé contre son `reseau_id` (même
 * garde que Admin\Agents\AgentController::agencesVisibles()).
 */
trait ResoutAgenceOutil
{
    private function agenceCiblee(array $arguments, Agent|Admin $utilisateur): ?Agence
    {
        if ($utilisateur instanceof Agent) {
            return $utilisateur->agence;
        }

        $agenceId = $arguments['agence_id'] ?? null;
        if ($agenceId === null) {
            return null;
        }

        $agence = Agence::find($agenceId);
        if ($agence === null) {
            return null;
        }

        if (! $utilisateur->estAdminPlateforme() && $agence->reseau_id !== $utilisateur->reseau_id) {
            return null;
        }

        return $agence;
    }
}
