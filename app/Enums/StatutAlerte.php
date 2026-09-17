<?php

namespace App\Enums;

enum StatutAlerte: string
{
    case Nouvelle = 'nouvelle';
    case EnCours = 'en_cours';
    case Traitee = 'traitee';

    public function libelle(): string
    {
        return match ($this) {
            self::Nouvelle => 'Nouvelle',
            self::EnCours => 'En cours',
            self::Traitee => 'Traitée',
        };
    }
}
