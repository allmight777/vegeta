<?php

namespace App\Enums;

enum StatutVerificationNpi: string
{
    case VerifieValide = 'verifie_valide';
    case VerifieInvalide = 'verifie_invalide';
    case EnAttenteConnexion = 'en_attente_connexion';
    case FormatInvalide = 'format_invalide';

    public function libelle(): string
    {
        return match ($this) {
            self::VerifieValide => 'NPI vérifié',
            self::VerifieInvalide => 'NPI invalide — action requise',
            self::EnAttenteConnexion => 'NPI en attente de connexion',
            self::FormatInvalide => 'Format de NPI invalide',
        };
    }
}
