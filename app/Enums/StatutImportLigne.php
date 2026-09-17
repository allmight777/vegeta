<?php

namespace App\Enums;

enum StatutImportLigne: string
{
    case Complet = 'complet';
    case ACompleter = 'a_completer';
    case Nouveau = 'nouveau';

    public function libelle(): string
    {
        return match ($this) {
            self::Complet => 'Complet',
            self::ACompleter => 'À compléter',
            self::Nouveau => 'Nouveau',
        };
    }
}
