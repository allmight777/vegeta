<?php

namespace App\Enums;

enum SourceValeur: string
{
    case Reglementaire = 'reglementaire';
    case BriefingCif = 'briefing_cif';
    case PolitiqueInterne = 'politique_interne';
    case Demo = 'demo';

    /**
     * Libellé destiné à l'écran de saisie : sans la mention « à confirmer », note de travail
     * interne qui n'a pas sa place devant un caissier. L'écran admin garde libelle() en entier.
     */
    public function libelleCourt(): string
    {
        return match ($this) {
            self::BriefingCif => 'Briefing CIF',
            default => $this->libelle(),
        };
    }

    public function libelle(): string
    {
        return match ($this) {
            self::Reglementaire => 'Réglementaire',
            self::BriefingCif => 'Briefing CIF — à confirmer',
            self::PolitiqueInterne => 'Politique interne',
            self::Demo => 'Démonstration — hypothèse',
        };
    }
}
