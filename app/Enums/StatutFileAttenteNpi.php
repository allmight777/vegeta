<?php

namespace App\Enums;

enum StatutFileAttenteNpi: string
{
    case EnAttente = 'en_attente';
    case Traitee = 'traitee';
    case EchoueeDefinitivement = 'echouee_definitivement';

    public function libelle(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Traitee => 'Traitée',
            self::EchoueeDefinitivement => 'Échouée définitivement (aucune connexion)',
        };
    }
}
