<?php

namespace App\Services\Kyc;

use App\Contracts\ExtracteurDocument;
use App\Services\Assistance\ExtracteurDocumentTexteBrut;
use App\Services\Assistance\ExtracteurTableurExcel;

/**
 * Aiguille vers la bonne implémentation d'extraction selon le fichier
 * (07_PROMPT_MODE_DEGRADE_NPI_OCR §4.3, étendu par 10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA
 * §1.3 pour les tableurs et le texte brut de la bibliothèque documentaire). L'OCR local
 * reste le défaut pour tout ce qui n'est ni `.docx`, ni `.xlsx`/`.xls`, ni `.txt`/`.md` —
 * il fonctionne à l'identique en ligne et hors ligne (zéro dépendance externe
 * supplémentaire). Une IA en ligne resterait une amélioration optionnelle
 * (`config('extraction.preference_en_ligne')`), mais aucune implémentation n'existe
 * cette itération.
 */
class SelecteurExtracteurDocument
{
    private const MIME_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    private const MIME_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    private const MIME_XLS = 'application/vnd.ms-excel';

    public function __construct(
        private readonly ExtracteurDocumentTexteNatif $texteNatif,
        private readonly ExtracteurDocumentOcrLocal $ocrLocal,
        private readonly ExtracteurTableurExcel $tableurExcel,
        private readonly ExtracteurDocumentTexteBrut $texteBrut,
    ) {}

    public function choisir(string $typeMime, string $extension): ExtracteurDocument
    {
        $extension = strtolower($extension);

        if ($extension === 'docx' || $typeMime === self::MIME_DOCX) {
            return $this->texteNatif;
        }

        if (in_array($extension, ['xlsx', 'xls'], true) || in_array($typeMime, [self::MIME_XLSX, self::MIME_XLS], true)) {
            return $this->tableurExcel;
        }

        if (in_array($extension, ['txt', 'md'], true)) {
            return $this->texteBrut;
        }

        return $this->ocrLocal;
    }
}
