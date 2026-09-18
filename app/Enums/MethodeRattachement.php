<?php

namespace App\Enums;

enum MethodeRattachement: string
{
    case Npi = 'npi';
    case Empreinte = 'empreinte';
    case Manuel = 'manuel';
    case Creation = 'creation';

    public function libelle(): string
    {
        return match ($this) {
            self::Npi => 'NPI vérifié (ANIP)',
            self::Empreinte => 'Empreinte nom + date de naissance',
            self::Manuel => 'Décision du responsable LBC/FT',
            self::Creation => 'Première fiche de cette identité',
        };
    }

    /** Un rattachement par NPI est certain ; par empreinte, il reste révisable. */
    public function estCertain(): bool
    {
        return $this === self::Npi || $this === self::Manuel;
    }
}
