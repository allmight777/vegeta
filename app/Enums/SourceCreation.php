<?php

namespace App\Enums;

enum SourceCreation: string
{
    case SaisieAgent = 'saisie_agent';
    case ImportCsv = 'import_csv';

    public function libelle(): string
    {
        return match ($this) {
            self::SaisieAgent => 'Saisie agent',
            self::ImportCsv => 'Import CSV',
        };
    }
}
