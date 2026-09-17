<?php

namespace App\Services\Assistance;

use App\Contracts\ProviderIa;

/**
 * Recherche par mots-clés simples dans la base de connaissances locale — pas d'IA
 * générative, une simple correspondance texte robuste (08_PROMPT §4.3). C'est le mode
 * recommandé pour la démonstration devant le jury (docs/DECISIONS.md).
 */
class ProviderIaSimulateur implements ProviderIa
{
    public function __construct(private readonly BaseConnaissances $baseConnaissances) {}

    public function repondre(string $question, array $contexte, array $historique = []): string
    {
        $trouve = $this->baseConnaissances->rechercher($question);

        return $trouve['reponse'] ?? GestionnaireAssistant::AUCUNE_REPONSE;
    }
}
