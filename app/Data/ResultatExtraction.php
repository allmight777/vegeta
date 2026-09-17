<?php

namespace App\Data;

use App\Enums\MethodeExtraction;

readonly class ResultatExtraction
{
    public function __construct(
        public bool $reussie,
        public ?string $texte,
        public ?MethodeExtraction $methode,
        public ?string $erreurMessage = null,
    ) {}
}
