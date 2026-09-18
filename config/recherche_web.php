<?php

/*
|--------------------------------------------------------------------------
| Recherche web de l'assistant IA
|--------------------------------------------------------------------------
|
| 10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §4 : recherche d'information publique
| uniquement (jamais d'import de fichier — la bibliothèque documentaire ne s'alimente
| que par upload direct, cf. config/filesystems.php disque `documents_ia`). Google
| Custom Search JSON API, clés non fournies à ce dépôt : voir docs/DECISIONS.md.
|
*/

return [

    'google_api_key' => env('GOOGLE_SEARCH_API_KEY'),
    'google_engine_id' => env('GOOGLE_SEARCH_ENGINE_ID'),
    'forcer_simulateur' => env('RECHERCHE_WEB_FORCER_SIMULATEUR', false),

];
