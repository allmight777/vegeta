<?php

namespace App\Enums;

enum StatutDeclarationCentif: string
{
    case APreparer = 'a_preparer';
    case Generee = 'generee';

    public function libelle(): string
    {
        return match ($this) {
            self::APreparer => 'À préparer',
            self::Generee => 'Générée',
        };
    }
}
