<?php

namespace App\Services\Assistance;

use App\Contracts\ExtracteurDocument;
use App\Data\ResultatExtraction;
use App\Enums\MethodeExtraction;

/**
 * `.txt`/`.md` : lecture directe, aucune extraction nécessaire
 * (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §1.3).
 */
class ExtracteurDocumentTexteBrut implements ExtracteurDocument
{
    public function extraire(string $cheminAbsolu, string $typeMime): ResultatExtraction
    {
        $texte = @file_get_contents($cheminAbsolu);

        if ($texte === false) {
            return new ResultatExtraction(false, null, null, 'Fichier texte illisible.');
        }

        $texte = trim($texte);

        if ($texte === '') {
            return new ResultatExtraction(false, null, null, 'Fichier texte vide.');
        }

        return new ResultatExtraction(true, $texte, MethodeExtraction::TexteNatif);
    }
}
