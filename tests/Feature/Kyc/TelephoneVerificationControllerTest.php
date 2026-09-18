<?php

namespace Tests\Feature\Kyc;

use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\JournalAudit;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TelephoneVerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function agentDuReseau(Reseau $reseau, string $matricule = 'GUI-1'): Agent
    {
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT-'.$matricule]);

        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => $matricule,
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Guichet,
        ]);
    }

    private function clientExistant(Reseau $reseau, string $telephone, string $nom, string $prenoms): PersonnePhysique
    {
        $client = Client::create([
            'reseau_id' => $reseau->id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);

        return PersonnePhysique::create([
            'client_id' => $client->id,
            'nom' => $nom,
            'prenoms' => $prenoms,
            'telephone' => $telephone,
            'champs_manquants' => [],
        ]);
    }

    public function test_signale_un_doublon_probable_quand_le_nom_saisi_differe(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT1']);
        $this->clientExistant($reseau, '90000001', 'KPADONOU', 'Fidèle');
        $agent = $this->agentDuReseau($reseau);

        $reponse = $this->actingAs($agent, 'agent')->postJson(route('agent.clients.telephone.verifier'), [
            'telephone' => '90000001',
            'nom_saisi' => 'DOSSOU Paul',
        ]);

        $reponse->assertOk()
            ->assertJson(['deja_enregistre' => true])
            ->assertJsonPath('avertissement_nom', fn ($valeur) => is_string($valeur) && str_contains($valeur, 'usurpation'));
    }

    public function test_ne_signale_rien_pour_un_numero_inedit(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT2']);
        $agent = $this->agentDuReseau($reseau);

        $reponse = $this->actingAs($agent, 'agent')->postJson(route('agent.clients.telephone.verifier'), [
            'telephone' => '90000099',
            'nom_saisi' => 'DOSSOU Paul',
        ]);

        $reponse->assertOk()->assertJson(['deja_enregistre' => false, 'avertissement_nom' => null]);
    }

    public function test_isolation_entre_reseaux_un_doublon_d_un_autre_reseau_n_est_jamais_signale(): void
    {
        $reseauA = Reseau::create(['nom' => 'Réseau A', 'code' => 'RA']);
        $reseauB = Reseau::create(['nom' => 'Réseau B', 'code' => 'RB']);
        $this->clientExistant($reseauA, '90000002', 'KPADONOU', 'Fidèle');
        $agentReseauB = $this->agentDuReseau($reseauB, 'GUI-2');

        $reponse = $this->actingAs($agentReseauB, 'agent')->postJson(route('agent.clients.telephone.verifier'), [
            'telephone' => '90000002',
            'nom_saisi' => 'KPADONOU Fidèle',
        ]);

        $reponse->assertOk()->assertJson(['deja_enregistre' => false, 'avertissement_nom' => null]);
    }

    public function test_la_verification_est_journalisee_sans_jamais_stocker_le_numero_en_clair(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT3']);
        $this->clientExistant($reseau, '90000003', 'KPADONOU', 'Fidèle');
        $agent = $this->agentDuReseau($reseau, 'GUI-3');

        $this->actingAs($agent, 'agent')->postJson(route('agent.clients.telephone.verifier'), [
            'telephone' => '90000003',
            'nom_saisi' => 'KPADONOU Fidèle',
        ])->assertOk();

        $this->assertTrue(JournalAudit::where('action', 'verification_telephone')->where('acteur_id', $agent->id)->exists());

        $brut = DB::table('personnes_physiques')->first();
        $this->assertStringStartsWith('v1:', $brut->telephone);
        $this->assertNotEmpty($brut->telephone_idx);
    }
}
