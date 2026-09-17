<?php

namespace App\Enums;

enum StatutFiltrage: string
{
    case AVerifier = 'a_verifier';
    case Ecarte = 'ecarte';
    case Confirme = 'confirme';

    public function libelle(): string
    {
        return match ($this) {
            self::AVerifier => 'À vérifier',
            self::Ecarte => 'Écarté (faux positif)',
            self::Confirme => 'Confirmé',
        };
    }
}
