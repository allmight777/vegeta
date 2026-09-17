<?php

namespace App\Services\Kyc;

use App\Contracts\ExtracteurDocument;
use App\Data\ResultatExtraction;
use App\Enums\MethodeExtraction;
use ZipArchive;

/**
 * Extraction réelle du texte natif d'un `.docx` (ZipArchive, aucune dépendance Composer —
 * extension core `ext-zip` déjà requise par Laravel). Sélectionnée par
 * SelecteurExtracteurDocument uniquement pour les `.docx` ; tout autre type de fichier
 * passe par ExtracteurDocumentOcrLocal (07_PROMPT_MODE_DEGRADE_NPI_OCR §3.3). Le `.doc`
 * (binaire Word ancien) et le calque texte natif d'un PDF ne sont pas couverts ici —
 * limite documentée dans docs/DECISIONS.md.
 */
class ExtracteurDocumentTexteNatif implements ExtracteurDocument
{
    private const MIME_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    public function extraire(string $cheminAbsolu, string $typeMime): ResultatExtraction
    {
        $extension = strtolower(pathinfo($cheminAbsolu, PATHINFO_EXTENSION));

        if ($typeMime === self::MIME_DOCX || $extension === 'docx') {
            return $this->extraireDocx($cheminAbsolu);
        }

        return new ResultatExtraction(
            reussie: false,
            texte: null,
            methode: null,
            erreurMessage: "Extraction {$extension} non disponible en texte natif — corriger manuellement ou déposer un fichier .docx.",
        );
    }

    private function extraireDocx(string $cheminAbsolu): ResultatExtraction
    {
        $zip = new ZipArchive;

        if ($zip->open($cheminAbsolu) !== true) {
            return new ResultatExtraction(false, null, null, 'Fichier .docx illisible ou corrompu.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return new ResultatExtraction(false, null, null, 'Fichier .docx sans contenu texte exploitable (word/document.xml absent).');
        }

        // Les sauts de paragraphe Word (<w:p>) doivent devenir des retours à la ligne
        // avant de retirer les balises, sinon deux champs collés perdent leur séparateur.
        $texte = preg_replace('/<\/w:p>/i', "\n", $xml);
        $texte = strip_tags($texte);
        $texte = html_entity_decode($texte, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $texte = preg_replace('/[ \t]+/', ' ', $texte);
        $texte = trim((string) $texte);

        if ($texte === '') {
            return new ResultatExtraction(false, null, null, 'Fichier .docx vide.');
        }

        return new ResultatExtraction(true, $texte, MethodeExtraction::TexteNatif);
    }
}
