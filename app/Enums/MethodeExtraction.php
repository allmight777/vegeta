<?php

namespace App\Enums;

/**
 * TexteNatif (.docx, .txt/.md), OcrLocal (PDF/images) et Tableur (.xlsx/.xls) sont
 * branchés. IaEnLigne reste défini pour Contracts\ExtracteurDocument, à implémenter plus
 * tard (voir docs/DECISIONS.md).
 */
enum MethodeExtraction: string
{
    case TexteNatif = 'texte_natif';
    case OcrLocal = 'ocr_local';
    case IaEnLigne = 'ia_en_ligne';
    case Tableur = 'tableur';

    public function libelle(): string
    {
        return match ($this) {
            self::TexteNatif => 'Extraction automatique (texte natif)',
            self::OcrLocal => 'OCR local',
            self::IaEnLigne => 'IA en ligne',
            self::Tableur => 'Tableur (Excel)',
        };
    }
}
