<?php

namespace App\Enums;

enum StatutEscalade: string
{
    case EnAttente = 'en_attente';
    case Traitee = 'traitee';

    public function libelle(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Traitee => 'Traitée',
        };
    }
}
