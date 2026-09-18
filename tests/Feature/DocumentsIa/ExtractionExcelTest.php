<?php

namespace Tests\Feature\DocumentsIa;

use App\Enums\MethodeExtraction;
use App\Services\Assistance\ExtracteurTableurExcel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * 10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §8 : un fichier `.xlsx` de test synthétique
 * (jamais une vraie donnée client) est correctement transformé en texte exploitable par
 * la recherche documentaire.
 */
class ExtractionExcelTest extends TestCase
{
    use RefreshDatabase;

    private function fichierXlsxDeTest(): string
    {
        $classeur = new Spreadsheet;
        $feuille = $classeur->getActiveSheet();
        $feuille->setTitle('Procédures');
        $feuille->setCellValue('A1', 'Étape');
        $feuille->setCellValue('B1', 'Description');
        $feuille->setCellValue('A2', '1');
        $feuille->setCellValue('B2', 'Vérifier la pièce d\'identité');
        $feuille->setCellValue('A3', '2');
        $feuille->setCellValue('B3', 'Saisir le dossier synthétique de démonstration');

        $chemin = tempnam(sys_get_temp_dir(), 'test_xlsx_').'.xlsx';
        (new Xlsx($classeur))->save($chemin);

        return $chemin;
    }

    public function test_un_xlsx_synthetique_est_extrait_en_texte_exploitable(): void
    {
        $chemin = $this->fichierXlsxDeTest();

        try {
            $resultat = app(ExtracteurTableurExcel::class)->extraire($chemin, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            $this->assertTrue($resultat->reussie);
            $this->assertSame(MethodeExtraction::Tableur, $resultat->methode);
            $this->assertStringContainsString('Procédures', $resultat->texte);
            $this->assertStringContainsString('Vérifier la pièce d\'identité', $resultat->texte);
            $this->assertStringContainsString('Saisir le dossier synthétique de démonstration', $resultat->texte);
        } finally {
            @unlink($chemin);
        }
    }

    public function test_un_classeur_sans_contenu_echoue_proprement(): void
    {
        $classeur = new Spreadsheet;
        $chemin = tempnam(sys_get_temp_dir(), 'test_xlsx_vide_').'.xlsx';
        (new Xlsx($classeur))->save($chemin);

        try {
            $resultat = app(ExtracteurTableurExcel::class)->extraire($chemin, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            $this->assertFalse($resultat->reussie);
            $this->assertNotNull($resultat->erreurMessage);
        } finally {
            @unlink($chemin);
        }
    }

    public function test_un_fichier_inexistant_echoue_proprement(): void
    {
        // PhpSpreadsheet retombe sur une tentative de lecture CSV pour tout contenu
        // ambigu portant l'extension .xlsx (un texte brut devient une feuille d'une
        // cellule) — un fichier réellement introuvable est le cas qui échoue de façon
        // fiable, quel que soit le lecteur choisi.
        $chemin = sys_get_temp_dir().'/n-existe-pas-'.uniqid().'.xlsx';

        $resultat = app(ExtracteurTableurExcel::class)->extraire($chemin, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertFalse($resultat->reussie);
        $this->assertNotNull($resultat->erreurMessage);
    }
}
