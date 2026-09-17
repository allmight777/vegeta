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

];
