<?php

namespace App\Enums;

enum TypeOperation: string
{
    case Depot = 'depot';
    case Retrait = 'retrait';

    public function libelle(): string
    {
        return match ($this) {
            self::Depot => 'Dépôt',
            self::Retrait => 'Retrait',
        };
    }
}
