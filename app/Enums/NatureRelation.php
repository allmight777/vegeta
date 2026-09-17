<?php

namespace App\Enums;

enum NatureRelation: string
{
    case TitulaireCompte = 'titulaire_compte';
    case Occasionnel = 'occasionnel';

    public function libelle(): string
    {
        return match ($this) {
            self::TitulaireCompte => 'Titulaire de compte',
            self::Occasionnel => 'Client occasionnel',
        };
    }
}
