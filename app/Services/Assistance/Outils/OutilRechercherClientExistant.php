<?php

namespace App\Services\Assistance\Outils;

use App\Contracts\ConnecteurSystemeExistant;
use App\Contracts\OutilAssistantIa;
use App\Models\Admin;
use App\Models\Agent;

/**
 * Confirme seulement si un dossier existe déjà dans le système externe du SFD — jamais
 * son contenu en clair dans la conversation (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA
 * §2.3). "trouvé / non trouvé", jamais un dump de champs.
 */
class OutilRechercherClientExistant implements OutilAssistantIa
{
    public function __construct(private readonly ConnecteurSystemeExistant $connecteur) {}

    public function nom(): string
    {
        return 'rechercher_client_existant';
    }

    public function description(): string
    {
        return 'Vérifie si une personne existe déjà dans le système existant du SFD (répond trouvé/non trouvé uniquement, jamais le détail).';
    }

    public function schemaParametres(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'npi' => ['type' => 'string', 'description' => "Numéro personnel d'identification, si connu."],
                'nom' => ['type' => 'string', 'description' => 'Nom déclaré, si le NPI est inconnu.'],
                'date_naissance' => ['type' => 'string', 'description' => 'Date de naissance AAAA-MM-JJ, si le NPI est inconnu.'],
            ],
            'required' => [],
        ];
    }

    public function motsCles(): array
    {
        return ['deja client', 'existe deja', 'dossier existant', 'systeme existant'];
    }

    public function executer(array $arguments, Agent|Admin $utilisateur): array
    {
        $criteres = array_filter([
            'npi' => $arguments['npi'] ?? null,
            'nom' => $arguments['nom'] ?? null,
            'date_naissance' => $arguments['date_naissance'] ?? null,
        ]);

        if ($criteres === []) {
            return ['trouve' => false];
        }

        $resultat = $this->connecteur->rechercherParCritere('personne_physique', $criteres);

        // Volontairement : on ne retourne jamais $resultat lui-même (champs en clair),
        // seulement le fait qu'une correspondance existe.
        return ['trouve' => $resultat !== null];
    }
}
