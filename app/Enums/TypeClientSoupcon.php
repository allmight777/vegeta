<?php

namespace App\Enums;

/** « Type de client » de la fiche : Particulier / Entreprise / ONG / PPE. */
enum TypeClientSoupcon: string
{
    case Particulier = 'particulier';
    case Entreprise = 'entreprise';
    case Ong = 'ong';
    case Ppe = 'ppe';

    public function libelle(): string
    {
        return match ($this) {
            self::Particulier => 'Particulier',
            self::Entreprise => 'Entreprise',
            self::Ong => 'ONG',
            self::Ppe => 'PPE',
        };
    }
}
