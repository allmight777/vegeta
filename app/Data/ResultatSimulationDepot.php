<?php

namespace App\Data;

use App\Enums\OperateurMobileMonnaie;

readonly class ResultatSimulationDepot
{
    public function __construct(
        public bool $trouve,
        public ?OperateurMobileMonnaie $operateur = null,
        public ?string $nomTitulaire = null,
    ) {}
}
