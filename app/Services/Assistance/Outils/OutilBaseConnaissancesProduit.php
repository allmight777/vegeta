<?php

namespace App\Services\Assistance\Outils;

use App\Contracts\OutilAssistantIa;
use App\Models\Admin;
use App\Models\Agent;
use App\Services\Assistance\BaseConnaissances;
use App\Services\Configuration\IdentiteSysteme;

/**
 * Enrobe la base de connaissances produit existante (lexique, guides d'écran —
 * 08_PROMPT_ASSISTANT_IA_CONFORMITE §6), inchangée, sous forme d'outil pour le
 * function-calling et le routage par mots-clés.
 */
class OutilBaseConnaissancesProduit implements OutilAssistantIa
{
    public function __construct(private readonly BaseConnaissances $baseConnaissances) {}

    public function nom(): string
    {
        return 'base_connaissances_produit';
    }

    public function description(): string
    {
        return "Cherche dans le lexique produit et les guides d'écran de ".IdentiteSysteme::nom().'.';
    }

    public function schemaParametres(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'question' => ['type' => 'string', 'description' => 'Question posée par l\'utilisateur.'],
            ],
            'required' => ['question'],
        ];
    }

    public function motsCles(): array
    {
        return ['comment', 'pourquoi', 'quest ce que', 'definition', 'signifie'];
    }

    public function executer(array $arguments, Agent|Admin $utilisateur): array
    {
        $question = (string) ($arguments['question'] ?? '');
        $trouve = $this->baseConnaissances->rechercher($question);

        return $trouve === null
            ? ['trouve' => false]
            : ['trouve' => true, 'reponse' => $trouve['reponse']];
    }
}
