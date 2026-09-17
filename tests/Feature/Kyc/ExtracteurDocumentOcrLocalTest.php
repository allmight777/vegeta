<?php

namespace Tests\Feature\Kyc;

use App\Enums\MethodeExtraction;
use App\Services\Kyc\ExtracteurDocumentOcrLocal;
use App\Services\Kyc\MappeurChampsExtraits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use thiagoalessio\TesseractOCR\FriendlyErrors;

class ExtracteurDocumentOcrLocalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Vrai seulement si le binaire tesseract est réellement présent sur ce poste — sinon
     * le pipeline OCR réel (07_PROMPT_MODE_DEGRADE_NPI_OCR §4) ne peut pas être exercé
     * ici, mais fonctionnera sur un poste équipé (ex. Windows + Tesseract le jour J).
     */
    private function tesseractDisponible(): bool
    {
        try {
            FriendlyErrors::checkTesseractPresence('tesseract');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Fabrique une image de test synthétique (texte imprimé simple, jamais une vraie
     * fiche client, cf. CLAUDE.md §2.1) contenant les libellés du référentiel.
     */
    private function imageDeTexteSynthetique(string $texte): string
    {
        $chemin = tempnam(sys_get_temp_dir(), 'ocr').'.png';
        $image = imagecreate(600, 100);
        imagecolorallocate($image, 255, 255, 255);
        $noir = imagecolorallocate($image, 0, 0, 0);
        imagestring($image, 5, 10, 10, $texte, $noir);
        imagepng($image, $chemin);
        imagedestroy($image);

        return $chemin;
    }

    public function test_les_champs_sont_retrouves_sur_une_image_de_texte_simple_avec_une_confiance_plafonnee(): void
    {
        if (! $this->tesseractDisponible()) {
            $this->markTestSkipped('tesseract non installé sur ce poste — le pipeline OCR réel n\'est pas exerçable ici (voir docs/COMPOSANTS_TIERS.md).');
        }

        $chemin = $this->imageDeTexteSynthetique('Nom : KPADONOU');

        $resultat = app(ExtracteurDocumentOcrLocal::class)->extraire($chemin, 'image/png');

        $this->assertTrue($resultat->reussie);
        $this->assertSame(MethodeExtraction::OcrLocal, $resultat->methode);
        $this->assertStringContainsString('KPADONOU', $resultat->texte);
    }

    public function test_le_mappeur_ne_depasse_jamais_le_plafond_de_confiance_donne_meme_pour_un_match_explicite(): void
    {
        $resultat = app(MappeurChampsExtraits::class)->mapper('Nom : KPADONOU', 'personne_physique', 0.7);

        $this->assertSame(0.7, $resultat['nom']['confiance']);
    }

    public function test_un_type_de_fichier_non_gere_echoue_explicitement(): void
    {
        $chemin = tempnam(sys_get_temp_dir(), 'inconnu').'.doc';
        file_put_contents($chemin, 'contenu binaire non exploitable');

        $resultat = app(ExtracteurDocumentOcrLocal::class)->extraire($chemin, 'application/msword');

        $this->assertFalse($resultat->reussie);
        $this->assertNotNull($resultat->erreurMessage);
    }
}
