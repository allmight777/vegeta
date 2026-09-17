<?php

namespace App\Enums;

enum SourceValeur: string
{
    case Reglementaire = 'reglementaire';
    case BriefingCif = 'briefing_cif';
    case PolitiqueInterne = 'politique_interne';
    case Demo = 'demo';

    public function libelle(): string
    {
        return match ($this) {
            self::Reglementaire => 'Réglementaire',
            self::BriefingCif => 'Briefing CIF — à confirmer',
            self::PolitiqueInterne => 'Politique interne',
            self::Demo => 'Démonstration — hypothèse',
        };
    }
}
