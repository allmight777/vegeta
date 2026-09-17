<?php

namespace App\Contracts;

use App\Data\ResultatVerificationNpi;

interface VerificateurNpi
{
    public function verifier(string $npi): ResultatVerificationNpi;
}
