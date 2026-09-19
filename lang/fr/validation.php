<?php

return [
    'accepted' => 'Le champ :attribute doit être accepté.',
    'array' => 'Le champ :attribute doit être un tableau.',
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'date' => "Le champ :attribute n'est pas une date valide.",
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'file' => 'Le champ :attribute doit être un fichier.',
    'in' => "La valeur choisie pour :attribute n'est pas valide.",
    'integer' => 'Le champ :attribute doit être un entier.',
    'max' => [
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
        'file' => 'Le fichier :attribute ne doit pas dépasser :max Ko.',
        'numeric' => 'Le champ :attribute ne doit pas dépasser :max.',
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
    'min' => [
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file' => 'Le fichier :attribute doit faire au moins :min Ko.',
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'required' => 'Le champ :attribute est obligatoire.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'unique' => 'La valeur de :attribute est déjà utilisée.',

    'attributes' => [
        'nom' => 'Nom',
        'prenoms' => 'Prénoms',
        'date_naissance' => 'Date de naissance',
        'piece_identite_type' => "Type de pièce d'identité",
        'piece_identite_numero' => "Numéro de pièce d'identité",
        'npi' => "Numéro personnel d'identification (NPI)",
        'telephone' => 'Téléphone',
        'email' => 'Email',
        'adresse' => 'Adresse',
        'type' => 'Type de client',
        'nature_relation' => 'Nature de la relation',
    ],
];
