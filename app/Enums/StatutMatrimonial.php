<?php

namespace App\Enums;

enum StatutMatrimonial: string
{
    case Celibataire = 'celibataire';
    case Marie = 'marie';
    case Divorce = 'divorce';
    case Veuf = 'veuf';

    public function libelle(): string
    {
        return match ($this) {
            self::Celibataire => 'Célibataire',
            self::Marie => 'Marié(e)',
            self::Divorce => 'Divorcé(e)',
            self::Veuf => 'Veuf/Veuve',
        };
    }
}
