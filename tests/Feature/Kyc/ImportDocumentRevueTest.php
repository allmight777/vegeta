<?php

namespace Tests\Feature\Kyc;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Reseau;
use App\Services\Kyc\ExtracteurDocumentClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class ImportDocumentRevueTest extends TestCase
{
    use RefreshDatabase;

    private function agent(): Agent
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);

        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => 'GUI-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Guichet,
        ]);
    }

    private function docx(): UploadedFile
    {
        $chemin = tempnam(sys_get_temp_dir(), 'docx').'.docx';
        $zip = new ZipArchive;
        $zip->open($chemin, ZipArchive::CREATE);
        $zip->addFromString('word/document.xml', '<?xml version="1.0"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body><w:p><w:r><w:t>Nom : KPADONOU</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Prénoms : Fidèle</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();

        return new UploadedFile($chemin, 'fiche.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
    }

    public function test_le_document_extrait_ne_cree_aucun_client_tant_qu_il_n_est_pas_valide_explicitement(): void
    {
        Storage::fake('documents_clients');
        $agent = $this->agent();

        $document = app(ExtracteurDocumentClient::class)->traiter($this->docx(), (string) Str::uuid(), $agent->id);

        $this->assertSame(0, Client::count());

        $reponseRevue = $this->actingAs($agent, 'agent')->get(route('agent.clients.import.revue', $document->import_lot_id));
        $reponseRevue->assertOk();
        $this->assertSame(0, Client::count(), 'La simple consultation de la revue ne doit créer aucun client.');

        $reponseValider = $this->actingAs($agent, 'agent')->post(route('agent.clients.import.valider', $document), [
            'type' => 'personne_physique',
            'nature_relation' => 'titulaire_compte',
            'nom' => 'KPADONOU',
            'prenoms' => 'Fidèle',
            'date_naissance' => '1988-03-14',
            'piece_identite_numero' => 'CIP-1',
            'piece_identite_type' => 'cni',
        ]);

        $reponseValider->assertRedirect();
        $this->assertSame(1, Client::count());
        $this->assertNotNull($document->fresh()->client_id);
    }
}
