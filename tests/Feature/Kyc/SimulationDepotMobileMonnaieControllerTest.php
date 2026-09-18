<?php

namespace Tests\Feature\Kyc;

use App\Enums\OperateurMobileMonnaie;
use App\Enums\RoleAgent;
use App\Enums\SourceValeur;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\CompteMobileMonnaieSimule;
use App\Models\JournalAudit;
use App\Models\Reseau;
use App\Services\Securite\IndexAveugle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SimulationDepotMobileMonnaieControllerTest extends TestCase
{
    use RefreshDatabase;

    private function agent(string $matricule = 'GUI-1'): Agent
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT-'.$matricule]);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT-'.$matricule]);

        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => $matricule,
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Caissier,
        ]);
    }

    private function compteSimule(string $telephone, string $nomTitulaire, OperateurMobileMonnaie $operateur = OperateurMobileMonnaie::Mtn): CompteMobileMonnaieSimule
    {
        return CompteMobileMonnaieSimule::create([
            'telephone' => $telephone,
            'telephone_idx' => app(IndexAveugle::class)->calculer($telephone, 'telephone'),
            'operateur' => $operateur,
            'nom_titulaire' => $nomTitulaire,
            'source' => SourceValeur::Demo,
        ]);
    }

    public function test_retourne_le_titulaire_simule_quand_le_numero_est_connu(): void
    {
        $this->compteSimule('90000001', 'KPADONOU Fidèle');
        $agent = $this->agent();

        $reponse = $this->actingAs($agent, 'agent')->postJson(route('agent.clients.simulation-depot.verifier'), [
            'telephone' => '90000001',
        ]);

        $reponse->assertOk()->assertJson([
            'trouve' => true,
            'operateur' => 'mtn',
            'nom_titulaire' => 'KPADONOU Fidèle',
        ]);
    }

    public function test_ne_trouve_rien_pour_un_numero_absent_de_l_annuaire_simule(): void
    {
        $agent = $this->agent('GUI-2');

        $reponse = $this->actingAs($agent, 'agent')->postJson(route('agent.clients.simulation-depot.verifier'), [
            'telephone' => '90009999',
        ]);

        $reponse->assertOk()->assertJson(['trouve' => false, 'nom_titulaire' => null]);
    }

    public function test_la_verification_est_journalisee_sans_jamais_stocker_le_numero_en_clair(): void
    {
        $this->compteSimule('90000003', 'KPADONOU Fidèle');
        $agent = $this->agent('GUI-3');

        $this->actingAs($agent, 'agent')->postJson(route('agent.clients.simulation-depot.verifier'), [
            'telephone' => '90000003',
        ])->assertOk();

        $this->assertTrue(JournalAudit::where('action', 'simulation_depot_mobile_monnaie')->where('acteur_id', $agent->id)->exists());

        $brut = DB::table('comptes_mobile_monnaie_simules')->first();
        $this->assertStringStartsWith('v1:', $brut->telephone);
        $this->assertNotEmpty($brut->telephone_idx);
    }
}
