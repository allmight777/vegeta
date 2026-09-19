<?php

namespace App\Enums;

enum StatutDossierSoupcon: string
{
    case EnCours = 'en_cours';
    case Transmise = 'transmise';
    case Traitee = 'traitee';

    public function libelle(): string
    {
        return match ($this) {
            self::EnCours => 'En cours de rédaction',
            self::Transmise => 'Transmis au responsable',
            self::Traitee => 'Traité',
        };
    }
}
