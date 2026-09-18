<?php

namespace App\Services\Assistance\Outils;

use App\Contracts\OutilAssistantIa;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Client;
use App\Services\Assistance\Outils\Concerns\ResoutAgenceOutil;

/**
 * Comptages non-identifiants pour une agence — jamais une liste nominative
 * (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §2.3). Réservé au responsable d'agence et à
 * l'administrateur.
 */
class OutilStatistiquesAgregeesAgence implements OutilAssistantIa
{
    use ResoutAgenceOutil;

    public function nom(): string
    {
        return 'statistiques_agence';
    }

    public function description(): string
    {
        return "Statistiques agrégées d'une agence (nombre de dossiers, taux de complétude moyen, comptes dormants réactivés ce mois) — jamais de liste nominative.";
    }

    public function schemaParametres(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'agence_id' => ['type' => 'integer', 'description' => "Identifiant de l'agence (administrateur uniquement)."],
            ],
            'required' => [],
        ];
    }

    public function motsCles(): array
    {
        return ['statistiques', 'combien de dossiers', 'taux de completude', 'comptes dormants'];
    }

    public function executer(array $arguments, Agent|Admin $utilisateur): array
    {
        $agence = $this->agenceCiblee($arguments, $utilisateur);

        if ($agence === null) {
            return ['erreur' => 'Précisez une agence valide.'];
        }

        $clients = Client::deLAgence($agence->id);

        return [
            'agence' => $agence->nom,
            'nombre_dossiers' => (clone $clients)->count(),
            'taux_completude_moyen' => round((float) (clone $clients)->avg('score_completude_kyc'), 1),
            'comptes_dormants_reactives_ce_mois' => Alerte::whereIn('client_id', (clone $clients)->pluck('id'))
                ->where('type', 'compte_dormant_reactive')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }
}
