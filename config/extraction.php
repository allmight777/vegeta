<?php

/*
|--------------------------------------------------------------------------
| Extraction de documents (fiches d'adhésion PDF/Word)
|--------------------------------------------------------------------------
|
| Sélection de Contracts\ExtracteurDocument (06_PROMPT_FORMULAIRE_CLIENT_ENRICHI §5).
| Seul 'demo' est implémenté cette itération : extraction .docx réelle via ZipArchive,
| échec transparent pour .pdf/.doc (aucune dépendance PDF/OCR installée — voir
| docs/COMPOSANTS_TIERS.md et docs/DECISIONS.md). 'ocr_local' et 'ia_en_ligne' restent
| des valeurs valides pour de futures implémentations derrière la même interface.
|
*/

return [

    'driver' => env('EXTRACTION_DRIVER', 'demo'),

    'taille_max_ko' => 10240,
    'nombre_max_fichiers' => 10,

];
