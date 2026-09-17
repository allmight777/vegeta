<?php

namespace Tests\Feature\Kyc;

use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\RoleSignataire;
use App\Enums\SourceCreation;
use App\Enums\TypeClient;
use App\Models\Admin;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\PersonneMorale;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Models\Signataire;
use App\Services\Kyc\CalculateurCompletude;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompletudeEtImportTest extends TestCase
{
    use RefreshDatabase;

    private function agence(): Agence
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);

        return Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
    }

    public function test_le_score_de_completude_refuse_de_compter_un_champ_manquant_comme_present(): void
    {
        $agence = $this->agence();
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create([
            'client_id' => $client->id,
            'nom' => 'KPADONOU',
            'prenoms' => 'Fidèle',
            'champs_manquants' => [],
        ]);

        $resultat = app(CalculateurCompletude::class)->evaluer($client->fresh(['personnePhysique']));

        $this->assertLessThan(100, $resultat['score']);
        $this->assertContains('date_naissance', $resultat['champs_manquants']);
        $this->assertContains('piece_identite_numero', $resultat['champs_manquants']);
    }

    public function test_un_champ_bloquant_manquant_refuse_la_validation_d_une_operation(): void
    {
        $agence = $this->agence();
        $agent = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => 'GUI-0002',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Guichet,
        ]);
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'KPADONOU', 'prenoms' => 'Fidèle', 'champs_manquants' => []]);
        app(CalculateurCompletude::class)->evaluer($client->fresh(['personnePhysique']));

        $this->assertFalse($agent->can('peutValiderOperation', $client->fresh(['personnePhysique'])));
    }

    public function test_une_personne_morale_sans_beneficiaire_effectif_conforme_est_bloquante(): void
    {
        $agence = $this->agence();
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonneMorale,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        $morale = PersonneMorale::create([
            'client_id' => $client->id,
            'raison_sociale' => 'SARL DEMO',
            'forme_juridique' => 'SARL',
            'rccm' => 'RCCM-1',
            'ifu' => 'IFU-1',
            'champs_manquants' => [],
        ]);

        $resultat = app(CalculateurCompletude::class)->evaluer($client->fresh(['personneMorale']));
        $this->assertContains('beneficiaire_effectif', $resultat['champs_manquants']);

        Signataire::create([
            'personne_morale_id' => $morale->id,
            'nom' => 'Un Dirigeant',
            'role' => RoleSignataire::BeneficiaireEffectif,
            'pourcentage_detention' => 30,
            'statut_filtrage' => 'confirme',
        ]);

        $resultat = app(CalculateurCompletude::class)->evaluer($client->fresh(['personneMorale.signataires']));
        $this->assertNotContains('beneficiaire_effectif', $resultat['champs_manquants']);
    }

    public function test_import_csv_cree_des_clients_incomplets_via_l_ecran_admin(): void
    {
        Storage::fake('local');

        $agence = $this->agence();
        $admin = Admin::create([
            'reseau_id' => null,
            'nom' => 'Admin',
            'email' => 'admin@test.demo',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
        ]);

        $csv = "nom,prenoms,date_naissance,piece_identite_numero\n".
               "KPADONOU,Fidèle,1988-03-14,CIP-1\n".
               "AHOUANDJINOU,Rachidatou,,\n";
        $fichier = UploadedFile::fake()->createWithContent('export.csv', $csv);

        $reponseApercu = $this->actingAs($admin, 'admin')->post('/admin/import/apercu', [
            'agence_id' => $agence->id,
            'fichier' => $fichier,
        ]);
        $reponseApercu->assertOk();

        $stockes = Storage::disk('local')->files('imports');
        $this->assertNotEmpty($stockes);

        $reponseConfirmer = $this->actingAs($admin, 'admin')->post('/admin/import/confirmer', [
            'fichier' => $stockes[0],
            'agence_id' => $agence->id,
            'mapping' => ['nom' => 'nom', 'prenoms' => 'prenoms', 'date_naissance' => 'date_naissance', 'piece_identite_numero' => 'piece_identite_numero'],
        ]);
        $reponseConfirmer->assertRedirect(route('admin.import.index'));

        $this->assertSame(2, Client::count());
        $this->assertTrue(Client::where('score_completude_kyc', '<', 100)->exists());
    }
}
