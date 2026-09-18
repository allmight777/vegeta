<?php

namespace App\Services\Assistance;

use App\Contracts\MoteurRechercheWeb;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Google Custom Search JSON API (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §4.2) —
 * `GOOGLE_SEARCH_API_KEY`/`GOOGLE_SEARCH_ENGINE_ID` non fournies à ce dépôt, voir
 * docs/DECISIONS.md. Ne sert jamais à importer un fichier dans la bibliothèque
 * documentaire — uniquement des liens et un extrait, jamais un chemin automatique vers
 * `documents_ia`.
 */
class MoteurRechercheWebApiExterne implements MoteurRechercheWeb
{
    private const URL = 'https://www.googleapis.com/customsearch/v1';

    public function rechercher(string $requete): array
    {
        try {
            $reponse = Http::timeout(8)->get(self::URL, [
                'key' => config('recherche_web.google_api_key'),
                'cx' => config('recherche_web.google_engine_id'),
                'q' => $requete,
                'num' => 5,
            ]);
        } catch (Throwable) {
            return [];
        }

        if (! $reponse->successful()) {
            return [];
        }

        return collect($reponse->json('items', []))
            ->map(fn (array $item) => [
                'titre' => (string) ($item['title'] ?? ''),
                'url' => (string) ($item['link'] ?? ''),
                'extrait' => (string) ($item['snippet'] ?? ''),
            ])
            ->values()
            ->all();
    }
}
