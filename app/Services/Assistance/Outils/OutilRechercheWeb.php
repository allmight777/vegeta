<?php

namespace App\Services\Assistance\Outils;

use App\Contracts\OutilAssistantIa;
use App\Models\Admin;
use App\Models\Agent;
use App\Services\Assistance\SelecteurMoteurRechercheWeb;

/**
 * Recherche d'information publique sur internet, jamais d'import de fichier
 * (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §4.1) : retourne des liens à proposer, avec
 * un court extrait à paraphraser — jamais une citation intégrale.
 */
class OutilRechercheWeb implements OutilAssistantIa
{
    public function __construct(private readonly SelecteurMoteurRechercheWeb $selecteur) {}

    public function nom(): string
    {
        return 'recherche_web';
    }

    public function description(): string
    {
        return "Cherche de l'information publique sur internet (ex. texte d'un article de loi) et propose des liens. Ne télécharge ni n'importe jamais de fichier.";
    }

    public function schemaParametres(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'requete' => ['type' => 'string', 'description' => 'Termes de recherche.'],
            ],
            'required' => ['requete'],
        ];
    }

    public function motsCles(): array
    {
        return ['internet', 'web', 'recherche en ligne', 'site', 'source externe'];
    }

    public function executer(array $arguments, Agent|Admin $utilisateur): array
    {
        $requete = (string) ($arguments['requete'] ?? $arguments['question'] ?? '');

        if (trim($requete) === '') {
            return ['resultats' => []];
        }

        return ['resultats' => $this->selecteur->choisir()->rechercher($requete)];
    }
}
