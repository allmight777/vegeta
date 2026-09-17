<?php

namespace App\Enums;

enum TypePieceIdentite: string
{
    case Cni = 'cni';
    case Cip = 'cip';
    case CarteBiometrique = 'carte_biometrique';
    case Passeport = 'passeport';

    public function libelle(): string
    {
        return match ($this) {
            self::Cni => 'Carte nationale d\'identité',
            self::Cip => 'Carte d\'identité professionnelle',
            self::CarteBiometrique => 'Carte biométrique',
            self::Passeport => 'Passeport',
        };
    }
}
