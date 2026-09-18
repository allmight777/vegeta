<?php

namespace App\Services\Assistance\Outils;

use App\Contracts\OutilAssistantIa;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Client;
use App\Services\Assistance\Outils\Concerns\ResoutAgenceOutil;

/**
 * Nombre d'alertes ouvertes par gravité — jamais les noms des clients concernés
 * (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §2.3). Réservé au responsable d'agence et à
 * l'administrateur (jamais injecté dans le jeu d'outils du caissier, voir OutilsParRole).
 */
class OutilCompterAlertesDuJour implements OutilAssistantIa
{
    use ResoutAgenceOutil;

    public function nom(): string
    {
        return 'compter_alertes_du_jour';
    }

    public function description(): string
    {
        return 'Compte les alertes ouvertes par gravité pour une agence, sans jamais nommer les dossiers concernés.';
    }

    public function schemaParametres(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'agence_id' => ['type' => 'integer', 'description' => "Identifiant de l'agence (administrateur uniquement — ignoré pour un responsable d'agence, toujours sa propre agence)."],
            ],
            'required' => [],
        ];
    }

    public function motsCles(): array
    {
        return ['alerte', 'alertes', 'combien dalertes'];
    }

    public function executer(array $arguments, Agent|Admin $utilisateur): array
    {
        $agence = $this->agenceCiblee($arguments, $utilisateur);

        if ($agence === null) {
            return ['erreur' => 'Précisez une agence valide pour compter ses alertes.'];
        }

        $clientIds = Client::deLAgence($agence->id)->pluck('id');

        $comptes = Alerte::whereIn('client_id', $clientIds)
            ->where('statut', '!=', 'traitee')
            ->selectRaw('gravite, count(*) as total')
            ->groupBy('gravite')
            ->pluck('total', 'gravite');

        return [
            'agence' => $agence->nom,
            'critique' => (int) ($comptes['critique'] ?? 0),
            'attention' => (int) ($comptes['attention'] ?? 0),
            'info' => (int) ($comptes['info'] ?? 0),
        ];
    }
}
