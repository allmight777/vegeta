<?php

/*
|--------------------------------------------------------------------------
| Assistant IA conformité
|--------------------------------------------------------------------------
|
| 08_PROMPT_ASSISTANT_IA_CONFORMITE. Aucun binaire local, aucune dépendance à installer
| (contrairement à l'OCR de 07_PROMPT) : soit un appel HTTP vers une API de complétion de
| chat externe, soit un simulateur purement PHP (Services\Assistance\ProviderIaSimulateur)
| utilisé automatiquement en l'absence de clé, de connexion, ou en cas d'échec — jamais
| une erreur visible à l'écran (voir docs/DECISIONS.md pour le choix générique de forme
| d'API, aucun fournisseur précis n'étant mandaté ni testé dans ce dépôt).
|
*/

return [

    'api_url' => env('ASSISTANCE_IA_API_URL'),
    'api_cle' => env('ASSISTANCE_IA_API_CLE'),

    // Liste de repli séparée par virgules — absorbe un quota dépassé sans échec visible.
    'modeles' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ASSISTANCE_IA_MODELES', ''))
    ))),

    // Bascule dédiée à la démonstration devant le jury (source = demo) : force le
    // simulateur même si une clé API est configurée, pour ne jamais dépendre du wifi de
    // la salle (07_PROMPT §4.3 documente la même prudence pour l'OCR).
    'forcer_simulateur' => env('ASSISTANCE_IA_FORCER_SIMULATEUR', false),

];
