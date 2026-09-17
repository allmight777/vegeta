<?php

namespace App\Enums;

enum RoleAgent: string
{
    case Guichet = 'guichet';
    case ResponsableLbcft = 'responsable_lbcft';
    case Direction = 'direction';

    public function libelle(): string
    {
        return match ($this) {
            self::Guichet => 'Agent guichet',
            self::ResponsableLbcft => 'Responsable LBC/FT',
            self::Direction => 'Direction',
        };
    }
}
