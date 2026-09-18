<?php

/**
 * Paramètres du rapprochement d'identité et du plafond quotidien.
 * Aucun de ces nombres n'est réglementaire : ce sont des hypothèses de démonstration
 * (CLAUDE.md §3, colonne `source`). L'interface d'administration doit les afficher avec
 * leur source et permettre de les modifier sans toucher au code.
 */
return [
    'rapprochement' => [
        // Au-dessus : rattachement automatique par empreinte.
        'seuil_auto' => 0.92,
        // Entre les deux : deux identités distinctes + proposition de fusion au responsable.
        'seuil_revue' => 0.80,
        'source' => 'demo',
        'reference_texte' => 'Seuils mesurés sur un échantillon de paires de noms béninois (tools/calibration) — à confirmer',
    ],

    'plafond_quotidien' => [
        // Plafond = revenus mensuels déclarés x coefficient, borné par plancher/plafond.
        'coefficient_revenus' => 1.5,
        'plancher' => 500000,
        'plafond_max' => 20000000,
        // Appliqué quand le KYC ne porte aucun revenu déclaré : volontairement bas,
        // pour que l'absence d'information ne devienne pas une absence de contrôle.
        'defaut_sans_revenus' => 1000000,
        'source' => 'demo',
        'reference_texte' => 'Loi art. 17 i) (cumul journalier espèces) ; coefficient interne à valider avec les mentors',
    ],
];
