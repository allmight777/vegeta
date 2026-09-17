<?php

namespace App\Enums;

enum SourceListeType: string
{
    case Onu = 'onu';
    case PpeBenin = 'ppe_benin';
    case PpeCedeao = 'ppe_cedeao';
    case Demo = 'demo';

    public function libelle(): string
    {
        return match ($this) {
            self::Onu => 'Liste consolidée ONU',
            self::PpeBenin => 'PPE Bénin (fictive)',
            self::PpeCedeao => 'PPE CEDEAO (fictive)',
            self::Demo => 'Liste de démonstration',
        };
    }
}
