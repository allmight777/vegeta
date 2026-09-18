<?php

namespace App\Data;

readonly class ResultatVerificationTelephone
{
    public function __construct(
        public bool $dejaEnregistre,
        public ?string $avertissementNom,
    ) {}
}
