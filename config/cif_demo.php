<?php

return [

    // Active les seeders et écrans de calibration/démonstration. À mettre à false en
    // dehors d'un contexte de démonstration (aucune donnée personnelle réelle n'est
    // de toute façon jamais utilisée — voir CLAUDE.md §2).
    'actif' => (bool) env('CIF_DEMO', true),

];
