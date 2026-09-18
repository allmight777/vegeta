<?php

namespace App\Contracts;

use App\Models\Admin;
use App\Models\Agent;

/**
 * Un outil = une fonction PHP écrite à la main, jamais une requête générée par l'IA
 * (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §0). Le fournisseur d'IA ne reçoit que
 * `nom()`/`description()`/`schemaParametres()` — jamais un accès direct à `executer()`,
 * qui reste appelé côté PHP seul.
 */
interface OutilAssistantIa
{
    /** Identifiant stable, utilisé par le function-calling et le routage par mots-clés. */
    public function nom(): string;

    /** Description envoyée au fournisseur d'IA — jamais de détail d'implémentation sensible. */
    public function description(): string;

    /**
     * Schéma JSON des paramètres, format function-calling (OpenAI-compatible) :
     * `['type' => 'object', 'properties' => [...], 'required' => [...]]`.
     *
     * @return array<string, mixed>
     */
    public function schemaParametres(): array;

    /**
     * Mots-clés déclenchant cet outil pour le routage sans function-calling
     * (fournisseur simulateur — §3 du prompt).
     *
     * @return array<int, string>
     */
    public function motsCles(): array;

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function executer(array $arguments, Agent|Admin $utilisateur): array;
}
