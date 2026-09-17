<?php

namespace App\Data;

use DateTimeImmutable;

readonly class ResultatVerificationNpi
{
    public function __construct(
        public bool $estValide,
        public ?string $nomOfficiel,
        public ?string $dateNaissanceOfficielle,
        public string $source,
        public DateTimeImmutable $verifieLe,
    ) {}
}
