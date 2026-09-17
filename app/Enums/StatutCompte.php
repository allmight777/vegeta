<?php

namespace App\Enums;

enum StatutCompte: string
{
    case Actif = 'actif';
    case Dormant = 'dormant';

    public function libelle(): string
    {
        return match ($this) {
            self::Actif => 'Actif',
            self::Dormant => 'Dormant',
        };
    }
}
