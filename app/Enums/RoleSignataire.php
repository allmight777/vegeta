<?php

namespace App\Enums;

enum RoleSignataire: string
{
    case Signataire = 'signataire';
    case BeneficiaireEffectif = 'beneficiaire_effectif';
    case Mandataire = 'mandataire';

    public function libelle(): string
    {
        return match ($this) {
            self::Signataire => 'Signataire',
            self::BeneficiaireEffectif => 'Bénéficiaire effectif',
            self::Mandataire => 'Mandataire',
        };
    }
}
