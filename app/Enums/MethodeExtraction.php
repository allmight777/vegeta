<?php

namespace App\Enums;

/**
 * Seul TexteNatif est réellement branché dans cette itération (extraction .docx via
 * ZipArchive). OcrLocal et IaEnLigne restent définis pour Contracts\ExtracteurDocument,
 * à implémenter plus tard (voir docs/DECISIONS.md).
 */
enum MethodeExtraction: string
{
    case TexteNatif = 'texte_natif';
    case OcrLocal = 'ocr_local';
    case IaEnLigne = 'ia_en_ligne';

    public function libelle(): string
    {
        return match ($this) {
            self::TexteNatif => 'Extraction automatique (texte natif)',
            self::OcrLocal => 'OCR local',
            self::IaEnLigne => 'IA en ligne',
        };
    }
}
