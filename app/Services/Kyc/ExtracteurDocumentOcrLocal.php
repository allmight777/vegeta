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
 *
 * Deux passes Tesseract (segmentation par défaut, puis `--psm 4` — colonnes de texte
 * uniforme, meilleur sur les fiches à champs alignés) plutôt qu'un seul mode fixe : sur
 * un document réel, l'un des deux peut échouer à regrouper libellé et valeur alors que
 * l'autre y arrive, et l'inverse est vrai sur un autre document. On garde le texte dont
 * le mappage vers le référentiel KYC retrouve le plus de champs — la mesure de qualité
 * qui compte réellement pour ce pipeline, pas une heuristique générique sur le texte
 * brut.
 */
class ExtracteurDocumentOcrLocal implements ExtracteurDocument
{
    private const EXTENSIONS_IMAGE = ['jpg', 'jpeg', 'png'];

    public function __construct(private readonly MappeurChampsExtraits $mappeur) {}

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
        $texteDefaut = trim((new TesseractOCR($chemin))->lang('fra')->run());
        $textePsm4 = trim((new TesseractOCR($chemin))->lang('fra')->psm(4)->run());

        $texte = $this->meilleurTexte($texteDefaut, $textePsm4);

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

            $texteDefaut = $this->ocrPages($pages, fn (string $page) => (new TesseractOCR($page))->lang('fra')->run());
            $textePsm4 = $this->ocrPages($pages, fn (string $page) => (new TesseractOCR($page))->lang('fra')->psm(4)->run());

            $texte = $this->meilleurTexte($texteDefaut, $textePsm4);

            return $texte === ''
                ? new ResultatExtraction(false, null, null, 'Aucun texte détecté par l\'OCR sur ce PDF.')
                : new ResultatExtraction(true, $texte, MethodeExtraction::OcrLocal);
        } finally {
            File::deleteDirectory($dossierTemp);
        }
    }

    /**
     * @param  array<int, string>  $pages
     * @param  callable(string): string  $ocr
     */
    private function ocrPages(array $pages, callable $ocr): string
    {
        return collect($pages)
            ->map(fn (string $page) => trim($ocr($page)))
            ->filter(fn (string $t) => $t !== '')
            ->implode("\n\f\n");
    }

    /**
     * Garde le texte dont le mappage vers le référentiel KYC retrouve le plus de
     * champs (le type de client — physique/morale — est deviné séparément pour chaque
     * variante, la segmentation pouvant changer la façon dont "PERSONNE MORALE" est
     * lu). Si les deux textes mappent au même nombre de champs — notamment 0/0 pour un
     * document qui n'est pas une fiche KYC, ex. bibliothèque documentaire de
     * l'assistant IA — le texte le plus long l'emporte, repli générique raisonnable
     * quand la mesure principale ne discrimine pas.
     */
    private function meilleurTexte(string $texteDefaut, string $textePsm4): string
    {
        if ($texteDefaut === $textePsm4) {
            return $texteDefaut;
        }

        $nombreChampsDefaut = $this->nombreChampsMappes($texteDefaut);
        $nombreChampsPsm4 = $this->nombreChampsMappes($textePsm4);

        if ($nombreChampsDefaut !== $nombreChampsPsm4) {
            return $nombreChampsPsm4 > $nombreChampsDefaut ? $textePsm4 : $texteDefaut;
        }

        return mb_strlen($textePsm4) > mb_strlen($texteDefaut) ? $textePsm4 : $texteDefaut;
    }

    private function nombreChampsMappes(string $texte): int
    {
        $type = $this->mappeur->typeClientDevine($texte);

        return count($this->mappeur->mapper($texte, $type));
    }
}
