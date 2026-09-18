<?php

namespace App\Enums;

enum RoleAgent: string
{
    case Caissier = 'caissier';
    case ResponsableAgence = 'responsable_agence';

    /** @deprecated Migré vers Caissier par `agents:migrer-roles`. Conservé pour compatibilité descendante le temps de vérifier la migration de données. */
    case Guichet = 'guichet';

    /** @deprecated Migré vers ResponsableAgence par `agents:migrer-roles`. */
    case ResponsableLbcft = 'responsable_lbcft';

    /** @deprecated Migré vers ResponsableAgence par `agents:migrer-roles`. */
    case Direction = 'direction';

    public function libelle(?string $civilite = null): string
    {
        return match ($this) {
            self::Caissier => match ($civilite) {
                'm' => 'Caissier',
                'f' => 'Caissière',
                default => 'Caissier / Caissière',
            },
            self::ResponsableAgence => "Responsable d'agence",
            self::Guichet => 'Agent guichet',
            self::ResponsableLbcft => 'Responsable LBC/FT',
            self::Direction => 'Direction',
        };
    }
}
