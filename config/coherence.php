<?php

/*
|--------------------------------------------------------------------------
| Cohérence profil déclaré / comportement transactionnel
|--------------------------------------------------------------------------
|
| Vigilance constante (Loi uniforme art. 18 ; Instruction BCEAO 001-03-2025
| art. 6) : les opérations doivent rester cohérentes avec la connaissance
| qu'on a du client. Un seuil absolu ne sait pas qu'un dépôt de 400 000 XOF
| est banal pour un grossiste et aberrant pour un apprenti tailleur.
|
| AUCUNE de ces valeurs n'est réglementaire : ce sont des hypothèses de
| démonstration, affichées comme telles (source = demo) et destinées à être
| calibrées par chaque réseau sur son propre historique. Ne jamais les
| présenter comme des seuils officiels.
|
*/

return [

    /*
    | Seuil de faisceau : nombre minimum de constats concordants avant de
    | signaler. Un indicateur isolé est du bruit — un agriculteur qui dépose
    | après la récolte, un commerçant qui reçoit du mobile money. Trois
    | indices concordants, c'est autre chose. Alerter sur un seul indice
    | noierait la file du responsable en trois jours.
    */
    'faisceau' => [
        'constats_minimum' => 2,
        'poids_minimum' => 3,
        'poids_critique' => 6,
    ],

    /*
    | Indicateur 1 — écart entre les flux observés et les revenus déclarés.
    | Rapport entre le cumul des dépôts sur la fenêtre et le revenu mensuel
    | déclaré au KYC, ramené au mois.
    */
    'ecart_flux_revenus' => [
        'fenetre_jours' => 30,
        'ratio_attention' => 3.0,
        'ratio_fort' => 10.0,
        'montant_plancher' => 100000.0, // en deçà, on n'ennuie personne
        'poids_attention' => 2,
        'poids_fort' => 4,
    ],

    /*
    | Indicateur 2 — compte de passage (typologie GIABA classique).
    | Dépôt suivi d'un retrait de l'essentiel du montant en quelques heures,
    | répété. Un compte d'épargne ne se comporte pas ainsi : c'est la
    | signature d'un compte-relais.
    */
    'compte_de_passage' => [
        'fenetre_jours' => 90,
        'delai_max_heures' => 48,
        'part_retiree_minimum' => 0.80,
        'occurrences_minimum' => 3,
        'poids' => 3,
    ],

    /*
    | Indicateur 3 — incohérence entre l'activité déclarée et les canaux
    | réellement utilisés. La correspondance ci-dessous est une hypothèse de
    | démonstration : chaque réseau connaît ses métiers mieux que nous.
    |
    | Clé = fragment d'activité recherché dans profession/activite_1/activite_2
    | (normalisé, sans accent, en minuscules).
    */
    'activite_canal' => [
        'operations_minimum' => 5,
        'part_dominante' => 0.80,
        'poids' => 2,

        'profils' => [
            'cultivateur' => ['attendus' => ['especes'], 'libelle' => 'Cultivateur / agriculture'],
            'agriculteur' => ['attendus' => ['especes'], 'libelle' => 'Cultivateur / agriculture'],
            'eleveur' => ['attendus' => ['especes'], 'libelle' => 'Élevage'],
            'pecheur' => ['attendus' => ['especes'], 'libelle' => 'Pêche'],
            'artisan' => ['attendus' => ['especes', 'mobile_money'], 'libelle' => 'Artisanat'],
            'tailleur' => ['attendus' => ['especes', 'mobile_money'], 'libelle' => 'Artisanat'],
            'couturier' => ['attendus' => ['especes', 'mobile_money'], 'libelle' => 'Artisanat'],
            'commercant' => ['attendus' => ['especes', 'mobile_money'], 'libelle' => 'Commerce de détail'],
            'revendeur' => ['attendus' => ['especes', 'mobile_money'], 'libelle' => 'Commerce de détail'],
            'salarie' => ['attendus' => ['virement'], 'libelle' => 'Salariat'],
            'fonctionnaire' => ['attendus' => ['virement'], 'libelle' => 'Fonction publique'],
            'enseignant' => ['attendus' => ['virement'], 'libelle' => 'Enseignement'],
            'retraite' => ['attendus' => ['virement'], 'libelle' => 'Retraite'],
            'etudiant' => ['attendus' => ['especes', 'mobile_money'], 'libelle' => 'Étudiant'],
        ],
    ],

];
