<?php

namespace App\Enums;

enum StatutPpe: string
{
    case NonPpe = 'non_ppe';
    case PpeAVerifier = 'ppe_a_verifier';
    case PpeConfirme = 'ppe_confirme';

    public function libelle(): string
    {
        return match ($this) {
            self::NonPpe => 'Non PPE',
            self::PpeAVerifier => 'PPE à vérifier',
            self::PpeConfirme => 'PPE confirmée',
        };
    }
}
