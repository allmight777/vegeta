<?php

namespace App\Enums;

enum StatutSuggestionSoupcon: string
{
    case Nouvelle = 'nouvelle';
    case EnAnalyse = 'en_analyse';
    case Transmise = 'transmise';
    case Traitee = 'traitee';
    case Ecartee = 'ecartee';

    public function libelle(): string
    {
        return match ($this) {
            self::Nouvelle => 'Nouvelle',
            self::EnAnalyse => 'En analyse',
            self::Transmise => 'Transmise au responsable',
            self::Traitee => 'Dossier traité',
            self::Ecartee => 'Écartée',
        };
    }

    /** Une suggestion ouverte empêche d'en générer une seconde pour le même client. */
    public function estOuverte(): bool
    {
        return in_array($this, [self::Nouvelle, self::EnAnalyse, self::Transmise], true);
    }
}
