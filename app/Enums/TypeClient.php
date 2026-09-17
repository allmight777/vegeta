<?php

namespace App\Enums;

enum TypeClient: string
{
    case PersonnePhysique = 'personne_physique';
    case PersonneMorale = 'personne_morale';

    public function libelle(): string
    {
        return match ($this) {
            self::PersonnePhysique => 'Personne physique',
            self::PersonneMorale => 'Personne morale',
        };
    }
}
