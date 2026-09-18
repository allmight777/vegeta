<?php

namespace Tests\Feature\Kyc;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Reseau;
use App\Services\Kyc\VerificateurNpiSimulateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NpiValideRegleTest extends TestCase
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
            'role' => RoleAgent::Caissier,
        ]);
    }

    public function test_un_npi_de_format_valide_ne_bloque_pas_la_creation(): void
    {
        $agent = $this->agent();

        $reponse = $this->actingAs($agent, 'agent')->post(route('agent.clients.stocker'), [
            'type' => 'personne_physique',
            'nature_relation' => 'titulaire_compte',
            'nom' => 'KPADONOU',
            'prenoms' => 'Fidèle',
            'date_naissance' => '1988-03-14',
            'piece_identite_numero' => 'CIP-1',
            'piece_identite_type' => 'cni',
            'npi' => '1234567890',
        ]);

        $reponse->assertRedirect();
        $this->assertSame(1, Client::count());
    }

    public function test_un_npi_de_test_explicitement_invalide_bloque_la_creation(): void
    {
        $agent = $this->agent();
        [$npiInvalide] = VerificateurNpiSimulateur::NPI_TEST_INVALIDES;

        $reponse = $this->actingAs($agent, 'agent')->post(route('agent.clients.stocker'), [
            'type' => 'personne_physique',
            'nature_relation' => 'titulaire_compte',
            'nom' => 'KPADONOU',
            'prenoms' => 'Fidèle',
            'date_naissance' => '1988-03-14',
            'piece_identite_numero' => 'CIP-1',
            'piece_identite_type' => 'cni',
            'npi' => $npiInvalide,
        ]);

        $reponse->assertSessionHasErrors('npi');
        $this->assertSame(0, Client::count());
    }

    public function test_la_route_de_verification_au_blur_renvoie_le_resultat_sans_creer_de_client(): void
    {
        $agent = $this->agent();
        [$npiInvalide] = VerificateurNpiSimulateur::NPI_TEST_INVALIDES;

        $reponseValide = $this->actingAs($agent, 'agent')->postJson(route('agent.clients.npi.verifier'), ['npi' => '1234567890']);
        $reponseValide->assertOk()->assertJson(['est_valide' => true]);

        $reponseInvalide = $this->actingAs($agent, 'agent')->postJson(route('agent.clients.npi.verifier'), ['npi' => $npiInvalide]);
        $reponseInvalide->assertOk()->assertJson(['est_valide' => false]);

        $this->assertSame(0, Client::count());
    }
}
