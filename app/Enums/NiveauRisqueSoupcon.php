<?php

namespace App\Enums;

enum NiveauRisqueSoupcon: string
{
    case Faible = 'faible';
    case Moyen = 'moyen';
    case Eleve = 'eleve';

    public function libelle(): string
    {
        return match ($this) {
            self::Faible => 'Faible',
            self::Moyen => 'Moyen',
            self::Eleve => 'Élevé',
        };
    }
}
