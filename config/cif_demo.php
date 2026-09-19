<?php

return [

    // Active les seeders et écrans de calibration/démonstration. À mettre à false en
    // dehors d'un contexte de démonstration (aucune donnée personnelle réelle n'est
    // de toute façon jamais utilisée — voir CLAUDE.md §2).
    'actif' => (bool) env('CIF_DEMO', true),

    // Adresse e-mail du responsable d'agence de démonstration (alertes de conformité).
    // Renseigner DEMO_EMAIL_RESPONSABLE dans .env pour recevoir réellement les e-mails.
    'email_responsable' => env('DEMO_EMAIL_RESPONSABLE', 'responsable.dassa@cif-empreinte.demo'),

];
