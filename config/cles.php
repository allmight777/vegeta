<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clés de chiffrement des données d'identité
    |--------------------------------------------------------------------------
    |
    | Une clé par réseau (32 octets aléatoires, base64), jamais APP_KEY, jamais
    | commitée. Convention de nom d'environnement : CLE_RESEAU_<CODE_RESEAU_MAJUSCULE>,
    | lue directement via env() par App\Services\Securite\GestionnaireCles car le
    | code réseau n'est connu qu'à l'exécution (table `reseaux`).
    |
    | 'demo' est la clé de repli utilisée quand aucune clé dédiée n'est définie
    | pour un réseau — acceptable uniquement en démonstration (CIF_DEMO=true).
    |
    */

    'demo' => env('CLE_CIF_DEMO'),

];
