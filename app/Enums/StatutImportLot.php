<?php

namespace App\Enums;

enum StatutImportLot: string
{
    case EnCours = 'en_cours';
    case Termine = 'termine';

    public function libelle(): string
    {
        return match ($this) {
            self::EnCours => 'En cours',
            self::Termine => 'Terminé',
        };
    }
}
