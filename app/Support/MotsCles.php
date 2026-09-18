<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Normalisation partagée pour toute correspondance par mots-clés de l'assistant IA
 * (base de connaissances, recherche documentaire, routage d'outils sans function-calling
 * — 10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §2.2/§3). Extrait de
 * `BaseConnaissances::normaliser()` pour éviter trois implémentations dupliquées.
 */
class MotsCles
{
    /**
     * @return array<int, string>
     */
    public static function extraire(string $texte): array
    {
        $ascii = Str::of($texte)->ascii()->lower()->toString();
        $nettoye = preg_replace('/[^a-z0-9 ]/', ' ', $ascii) ?? '';

        return array_values(array_filter(explode(' ', $nettoye), fn (string $mot) => mb_strlen($mot) > 2));
    }
}
