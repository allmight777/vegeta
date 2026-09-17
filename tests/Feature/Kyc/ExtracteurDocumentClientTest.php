<?php

namespace Tests\Feature\Kyc;

use App\Enums\StatutExtractionDocument;
use App\Models\DocumentClient;
use App\Services\Kyc\ExtracteurDocumentClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class ExtracteurDocumentClientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Fabrique un .docx synthétique minimal (jamais une vraie fiche client) contenant un
     * texte de type "LIBELLE : valeur" par ligne, comme le ferait Word.
     */
    private function fichierDocxSynthetique(array $lignes): UploadedFile
    {
        $chemin = tempnam(sys_get_temp_dir(), 'docx').'.docx';
        $zip = new ZipArchive;
        $zip->open($chemin, ZipArchive::CREATE);

        $paragraphes = collect($lignes)
            ->map(fn ($ligne) => '<w:p><w:r><w:t>'.htmlspecialchars($ligne, ENT_XML1).'</w:t></w:r></w:p>')
            ->implode('');

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'.$paragraphes.'</w:body></w:document>';

        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        return new UploadedFile($chemin, 'fiche-test.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
    }

    public function test_un_docx_synthetique_est_extrait_avec_les_champs_retrouves(): void
    {
        Storage::fake('documents_clients');

        $fichier = $this->fichierDocxSynthetique([
            'FICHE D\'ADHESION PERSONNE PHYSIQUE',
            'Nom : KPADONOU',
            'Prénoms : Fidèle',
        ]);

        $document = app(ExtracteurDocumentClient::class)->traiter($fichier, (string) Str::uuid(), null);

        $this->assertSame(StatutExtractionDocument::Reussie, $document->statut_extraction);
        $this->assertSame('personne_physique', $document->type_client_devine);
        $this->assertSame('KPADONOU', $document->donnees_extraites['nom']['valeur']);
        $this->assertGreaterThan(0, $document->donnees_extraites['nom']['confiance']);
    }

    public function test_un_pdf_echoue_proprement_sans_extraction_disponible(): void
    {
        Storage::fake('documents_clients');

        $fichier = UploadedFile::fake()->create('fiche-test.pdf', 10, 'application/pdf');

        $document = app(ExtracteurDocumentClient::class)->traiter($fichier, (string) Str::uuid(), null);

        $this->assertSame(StatutExtractionDocument::Echouee, $document->statut_extraction);
        $this->assertNotNull($document->erreur_message);
    }

    public function test_l_echec_d_un_fichier_du_lot_ne_bloque_pas_le_traitement_des_autres(): void
    {
        Storage::fake('documents_clients');

        $lot = (string) Str::uuid();
        $extracteur = app(ExtracteurDocumentClient::class);

        $documentEchoue = $extracteur->traiter(UploadedFile::fake()->create('a.pdf', 5, 'application/pdf'), $lot, null);
        $documentReussi = $extracteur->traiter($this->fichierDocxSynthetique(['Nom : AHOUANDJINOU']), $lot, null);

        $this->assertSame(StatutExtractionDocument::Echouee, $documentEchoue->statut_extraction);
        $this->assertSame(StatutExtractionDocument::Reussie, $documentReussi->statut_extraction);
        $this->assertSame(2, DocumentClient::where('import_lot_id', $lot)->count());
    }
}
