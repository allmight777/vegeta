<?php

namespace App\Enums;

enum StatutFusionIdentite: string
{
    case EnAttente = 'en_attente';
    case Confirmee = 'confirmee';
    case Ecartee = 'ecartee';

    public function libelle(): string
    {
        return match ($this) {
            self::EnAttente => 'Rapprochement à confirmer',
            self::Confirmee => 'Fusion confirmée',
            self::Ecartee => 'Personnes distinctes',
        };
    }
}
