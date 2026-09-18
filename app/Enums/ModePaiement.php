<?php

namespace App\Enums;

enum ModePaiement: string
{
    case Especes = 'especes';
    case Virement = 'virement';
    case MobileMoney = 'mobile_money';

    public function libelle(): string
    {
        return match ($this) {
            self::Especes => 'Espèces',
            self::Virement => 'Virement',
            self::MobileMoney => 'Mobile money',
        };
    }
}
