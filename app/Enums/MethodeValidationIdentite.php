<?php

namespace App\Enums;

enum MethodeValidationIdentite: string
{
    case Numero = 'numero';
    case CodeQr = 'code_qr';

    public function libelle(): string
    {
        return match ($this) {
            self::Numero => 'Numéro d\'identification',
            self::CodeQr => 'Code QR',
        };
    }
}
