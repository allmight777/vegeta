<?php

namespace App\Contracts;

use App\Data\ResultatExtraction;

interface ExtracteurDocument
{
    public function extraire(string $cheminAbsolu, string $typeMime): ResultatExtraction;
}
