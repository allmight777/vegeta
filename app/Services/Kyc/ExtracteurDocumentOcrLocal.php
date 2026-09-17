<?php

namespace App\Services\Kyc;

use App\Contracts\ExtracteurDocument;
use App\Data\ResultatExtraction;
use App\Enums\MethodeExtraction;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Imagick;
use Spatie\PdfToImage\Enums\OutputFormat;
use Spatie\PdfToImage\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Throwable;

/**
 * OCR local (fonctionne sans connexion) — désormais le chemin normal pour les documents
 * scannés/photographiés, plus un simple repli d'urgence (07_PROMPT_MODE_DEGRADE_NPI_OCR
 * §4). Ne tente jamais silencieusement : si tesseract ou Imagick sont indisponibles sur
 * ce poste, échoue avec un message explicite plutôt que de lever une exception non
 * catchée. `.doc` (binaire Word ancien) n'est couvert ni ici ni par l'extraction texte
 * native — limite honnête documentée dans docs/DECISIONS.md.
 */
class ExtracteurDocumentOcrLocal implements ExtracteurDocument
{
    private const EXTENSIONS_IMAGE = ['jpg', 'jpeg', 'png'];

    public function extraire(string $cheminAbsolu, string $typeMime): ResultatExtraction
    {
        $extension = strtolower(pathinfo($cheminAbsolu, PATHINFO_EXTENSION));

        try {
            if ($typeMime === 'application/pdf' || $extension === 'pdf') {
                return $this->extrairePdf($cheminAbsolu);
            }

            if (in_array($extension, self::EXTENSIONS_IMAGE, true)) {
                return $this->extraireImage($cheminAbsolu);
            }

            return new ResultatExtraction(false, null, null, "OCR non disponible pour ce type de fichier ({$extension}).");
        } catch (Throwable $e) {
            return new ResultatExtraction(false, null, null, 'OCR non disponible sur ce poste : '.$e->getMessage());
        }
    }

    private function extraireImage(string $chemin): ResultatExtraction
    {
        $texte = trim((new TesseractOCR($chemin))->lang('fra')->run());

        return $texte === ''
            ? new ResultatExtraction(false, null, null, 'Aucun texte détecté par l\'OCR sur cette image.')
            : new ResultatExtraction(true, $texte, MethodeExtraction::OcrLocal);
    }

    private function extrairePdf(string $chemin): ResultatExtraction
    {
        if (! class_exists(Imagick::class)) {
            return new ResultatExtraction(false, null, null, 'Rasterisation PDF indisponible : l\'extension PHP Imagick n\'est pas installée sur ce poste.');
        }

        $dossierTemp = sys_get_temp_dir().'/ocr_'.Str::uuid();
        File::ensureDirectoryExists($dossierTemp);

        try {
            $pages = (new Pdf($chemin))
                ->resolution(300)
                ->format(OutputFormat::Png)
                ->saveAllPages($dossierTemp);

            $texte = collect($pages)
                ->map(fn ($page) => trim((new TesseractOCR($page->filename()))->lang('fra')->run()))
                ->filter(fn (string $t) => $t !== '')
                ->implode("\n");

            return $texte === ''
                ? new ResultatExtraction(false, null, null, 'Aucun texte détecté par l\'OCR sur ce PDF.')
                : new ResultatExtraction(true, $texte, MethodeExtraction::OcrLocal);
        } finally {
            File::deleteDirectory($dossierTemp);
        }
    }
}
