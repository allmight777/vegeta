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

    /*
    | Forçage de l'état de connectivité — pour la démonstration et les tests.
    |
    | Sans ce réglage, l'état dépend du wifi de la salle : la démonstration du refus
    | d'un NPI invalide ne bloque pas si la machine est hors ligne (branche dégradée),
    | et chaque création de dossier coûte 2 s de timeout HTTP.
    |
    | 'en_ligne'   : se comporte comme si internet était disponible (vérification NPI réelle)
    | 'hors_ligne' : se comporte comme si internet était coupé (démonstration du mode dégradé)
    | 'auto'       : comportement de production, test HTTP réel (défaut)
    */
    'mode_connectivite' => env('RESEAU_MODE_CONNECTIVITE', 'auto'),

];
