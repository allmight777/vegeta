<?php

namespace App\Enums;

enum GraviteAlerte: string
{
    case Info = 'info';
    case Attention = 'attention';
    case Critique = 'critique';

    public function libelle(): string
    {
        return match ($this) {
            self::Info => 'Information',
            self::Attention => 'Attention',
            self::Critique => 'Critique',
        };
    }
}
