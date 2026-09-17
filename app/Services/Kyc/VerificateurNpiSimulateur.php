<?php

namespace App\Services\Kyc;

use App\Contracts\VerificateurNpi;
use App\Data\ResultatVerificationNpi;
use DateTimeImmutable;

/**
 * Simulateur de démonstration (source = demo) : valide tout NPI de format plausible
 * (délégué à ValidateurFormatNpi), sauf les deux NPI de test explicitement invalides
 * ci-dessous, prévus pour démontrer le refus de création à l'oral
 * (06_PROMPT_FORMULAIRE_CLIENT_ENRICHI §6.1).
 */
class VerificateurNpiSimulateur implements VerificateurNpi
{
    public const NPI_TEST_INVALIDES = ['0000000000', '1111111111'];

    public function __construct(private readonly ValidateurFormatNpi $validateurFormat) {}

    public function verifier(string $npi): ResultatVerificationNpi
    {
        $npi = trim($npi);
        $estValide = $this->validateurFormat->estPlausible($npi) && ! in_array($npi, self::NPI_TEST_INVALIDES, true);

        return new ResultatVerificationNpi(
            estValide: $estValide,
            nomOfficiel: null,
            dateNaissanceOfficielle: null,
            source: 'demo',
            verifieLe: new DateTimeImmutable,
        );
    }
}
