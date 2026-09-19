<?php

/*
|--------------------------------------------------------------------------
| KYC — vérifications externes
|--------------------------------------------------------------------------
|
| Sélection des implémentations Contracts\VerificateurNpi et
| Contracts\ConnecteurSystemeExistant (06_PROMPT_FORMULAIRE_CLIENT_ENRICHI §6, §7).
| Les variantes "réelles" sont écrites mais non branchables faute d'accès à une API
| publique pour ce hackathon — voir docs/DECISIONS.md.
|
*/

return [

    // 'simulateur' (défaut, démonstration) | 'api_reelle' (non branchable sans accès réel)
    'verificateur_npi' => env('KYC_VERIFICATEUR_NPI', 'simulateur'),

    'npi_api_url' => env('KYC_NPI_API_URL'),
    'npi_api_cle' => env('KYC_NPI_API_CLE'),

    // 'local' (défaut, cherche dans import_lignes.donnees_brutes) | 'api_core_banking'
    'connecteur_systeme_existant' => env('KYC_CONNECTEUR_SYSTEME_EXISTANT', 'local'),

    'core_banking_api_url' => env('KYC_CORE_BANKING_API_URL'),
    'core_banking_api_cle' => env('KYC_CORE_BANKING_API_CLE'),

    // Nombre de passages de `npi:verifier-en-attente` sans connectivité avant d'arrêter de
    // réessayer (07_PROMPT_MODE_DEGRADE_NPI_OCR §2.4) — source = demo, à calibrer avec
    // l'équipe selon la fréquence réelle de coupure sur le terrain.
    'npi_tentatives_max' => env('KYC_NPI_TENTATIVES_MAX', 20),

    // Copilote de saisie (12_PROMPT_IA_INTEGREE_PROFONDE §4) — seuils heuristiques de
    // démonstration, jamais une valeur réglementaire (CLAUDE.md §3 : aucun seuil codé en
    // dur sans `source`). À calibrer avec les mentors métier avant toute mise en
    // production réelle.
    'copilote' => [
        // Âge en dessous duquel une profession "retraité(e)" déclarée est jugée
        // incohérente (retraite précoce plausible à partir de cet âge, pas avant).
        'age_min_retraite' => env('KYC_COPILOTE_AGE_MIN_RETRAITE', 50),

        // Ratio dépôt initial / revenu mensuel déclaré à partir duquel le copilote
        // avertit (origine des fonds, Loi art. 17 i) — 20x un revenu mensuel correspond
        // à un peu moins de deux ans de revenus déposés en une fois.
        'ratio_depot_revenu_alerte' => env('KYC_COPILOTE_RATIO_DEPOT_REVENU_ALERTE', 20),

        // Similarité de nom (Dice sur empreinte) à partir de laquelle un dossier existant du
        // réseau est signalé comme doublon probable pendant la saisie. Hypothèse de
        // démonstration : plus permissif que le rapprochement d'identité (identite.php) car
        // ce n'est qu'un avertissement non bloquant.
        'doublon_seuil_nom' => env('KYC_COPILOTE_DOUBLON_SEUIL_NOM', 0.75),

        // Nombre minimal de dossiers utilisant une orthographe pour la proposer comme norme.
        'normalisation_min_dossiers' => env('KYC_COPILOTE_NORMALISATION_MIN_DOSSIERS', 2),

        'source' => 'demo',
    ],

];
