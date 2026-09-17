<?php

namespace Tests\Feature\Kyc;

use App\Services\Kyc\ExtracteurDocumentOcrLocal;
use App\Services\Kyc\ExtracteurDocumentTexteNatif;
use App\Services\Kyc\SelecteurExtracteurDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SelecteurExtracteurDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_docx_est_aiguille_vers_l_extraction_texte_natif(): void
    {
        $selecteur = app(SelecteurExtracteurDocument::class);

        $extracteur = $selecteur->choisir(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docx',
        );

        $this->assertInstanceOf(ExtracteurDocumentTexteNatif::class, $extracteur);
    }

    #[DataProvider('fichiersNonDocx')]
    public function test_tout_ce_qui_n_est_pas_docx_est_aiguille_vers_l_ocr_local(string $mime, string $extension): void
    {
        $selecteur = app(SelecteurExtracteurDocument::class);

        $this->assertInstanceOf(ExtracteurDocumentOcrLocal::class, $selecteur->choisir($mime, $extension));
    }

    public static function fichiersNonDocx(): array
    {
        return [
            'pdf scanné' => ['application/pdf', 'pdf'],
            'photo jpg' => ['image/jpeg', 'jpg'],
            'photo png' => ['image/png', 'png'],
            'doc binaire ancien' => ['application/msword', 'doc'],
        ];
    }
}
