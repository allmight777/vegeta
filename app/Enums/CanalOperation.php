<?php

namespace App\Enums;

enum CanalOperation: string
{
    case Guichet = 'guichet';
    case Import = 'import';

    public function libelle(): string
    {
        return match ($this) {
            self::Guichet => 'Guichet',
            self::Import => 'Import',
        };
    }
}
