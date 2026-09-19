<?php

namespace App\Enums;

/** « Canal utilisé » de la fiche : Guichet / Mobile / Virement / Autre. */
enum CanalSoupcon: string
{
    case Guichet = 'guichet';
    case Mobile = 'mobile';
    case Virement = 'virement';
    case Autre = 'autre';

    public function libelle(): string
    {
        return match ($this) {
            self::Guichet => 'Guichet',
            self::Mobile => 'Mobile',
            self::Virement => 'Virement',
            self::Autre => 'Autre',
        };
    }
}
