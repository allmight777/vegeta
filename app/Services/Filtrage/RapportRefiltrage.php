<?php

namespace App\Services\Filtrage;

use Carbon\CarbonInterface;

/**
 * Résultat mesurable d'une campagne de refiltrage — c'est la preuve de diligence
 * qu'un contrôleur BCEAO demandera : quand la liste a été publiée, quand le parc a
 * fini d'être recontrôlé, et si le délai réglementaire de 24 heures a été tenu.
 */
class RapportRefiltrage
{
    private int $dossiersTraites = 0;

    private int $correspondancesTrouvees = 0;

    private int $dossiersAvecCorrespondance = 0;

    private ?CarbonInterface $termineLe = null;

    public function __construct(
        public readonly CarbonInterface $demarreLe,
        public readonly ?CarbonInterface $listePublieeLe = null,
    ) {}

    public function enregistrer(int $correspondances): void
    {
        $this->correspondancesTrouvees += $correspondances;

        if ($correspondances > 0) {
            $this->dossiersAvecCorrespondance++;
        }
    }

    public function cloturer(CarbonInterface $termineLe, int $dossiersTraites): void
    {
        $this->termineLe = $termineLe;
        $this->dossiersTraites = $dossiersTraites;
    }

    public function dossiersTraites(): int
    {
        return $this->dossiersTraites;
    }

    public function correspondancesTrouvees(): int
    {
        return $this->correspondancesTrouvees;
    }

    public function dossiersAvecCorrespondance(): int
    {
        return $this->dossiersAvecCorrespondance;
    }

    public function dureeSecondes(): float
    {
        if ($this->termineLe === null) {
            return 0.0;
        }

        return round(abs($this->termineLe->floatDiffInSeconds($this->demarreLe)), 2);
    }

    /**
     * Débit mesuré, en dossiers par seconde. Sert à extrapoler honnêtement le temps
     * de refiltrage sur un parc réel plutôt qu'à l'affirmer.
     */
    public function dossiersParSeconde(): float
    {
        $duree = $this->dureeSecondes();

        return $duree > 0.0 ? round($this->dossiersTraites / $duree, 1) : 0.0;
    }

    /**
     * Délai écoulé entre la publication de la liste et la fin du refiltrage
     * (CLAUDE.md §41). Null si la date de publication n'est pas connue.
     */
    public function delaiDepuisPublicationHeures(): ?float
    {
        if ($this->listePublieeLe === null || $this->termineLe === null) {
            return null;
        }

        return round(abs($this->termineLe->floatDiffInHours($this->listePublieeLe)), 2);
    }

    public function echeanceRespectee(): ?bool
    {
        $delai = $this->delaiDepuisPublicationHeures();

        return $delai === null ? null : $delai <= RefiltrageParc::ECHEANCE_REGLEMENTAIRE_HEURES;
    }

    /**
     * Phrase prête à afficher (console, écran conformité, PDF de rapport).
     */
    public function resume(): string
    {
        $base = sprintf(
            '%d dossier(s) recontrôlé(s) en %s s (%s dossiers/s) — %d correspondance(s) sur %d dossier(s).',
            $this->dossiersTraites,
            number_format($this->dureeSecondes(), 2, ',', ' '),
            number_format($this->dossiersParSeconde(), 1, ',', ' '),
            $this->correspondancesTrouvees,
            $this->dossiersAvecCorrespondance,
        );

        $delai = $this->delaiDepuisPublicationHeures();

        if ($delai === null) {
            return $base;
        }

        return $base.sprintf(
            ' Délai depuis publication de la liste : %s h sur %d h réglementaires — %s.',
            number_format($delai, 2, ',', ' '),
            RefiltrageParc::ECHEANCE_REGLEMENTAIRE_HEURES,
            $this->echeanceRespectee() ? 'échéance respectée' : 'ÉCHÉANCE DÉPASSÉE',
        );
    }
}
