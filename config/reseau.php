<?php

/*
|--------------------------------------------------------------------------
| Connectivité réseau (internet)
|--------------------------------------------------------------------------
|
| À ne pas confondre avec App\Models\Reseau / ContexteReseau, qui désignent le réseau
| SFD (le tenant). Ici « réseau » = connexion internet, utilisé par
| Contracts\DetecteurConnectivite (07_PROMPT_MODE_DEGRADE_NPI_OCR §2).
|
| Aucune URL de santé officielle CIF n'a été fournie à ce dépôt : la valeur par défaut
| est une hypothèse de démonstration (source = demo), à remplacer par un endpoint réel
| (ex. l'API NPI elle-même) dès qu'il existe.
|
*/

return [

    'url_test_connectivite' => env('RESEAU_URL_TEST_CONNECTIVITE', 'https://www.google.com/generate_204'),

    'duree_cache_secondes' => 10,

];
