<?php

namespace App\Contracts;

interface ProviderIa
{
    /**
     * @param  array<string, mixed>  $contexte  liste fermée, cf. Services\Assistance\ConstructeurContexteIa
     * @param  array<int, array{role: string, content: string}>  $historique
     */
    public function repondre(string $question, array $contexte, array $historique = []): string;
}
