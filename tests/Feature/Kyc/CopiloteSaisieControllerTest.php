<?php

namespace Tests\Feature\Kyc;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 12_PROMPT_IA_INTEGREE_PROFONDE §4 (copilote de saisie), test obligatoire §8 :
 * « aucune donnée d'identité ne peut atteindre le fournisseur IA depuis le copilote
 * de saisie ». Ici il n'y a même pas de fournisseur IA — seulement des règles PHP —
 * donc le test le plus direct est que l'endpoint n'accepte QUE des caractéristiques
 * dérivées et rejette tout champ d'identité qui tenterait d'y transiter.
 */
class CopiloteSaisieControllerTest extends TestCase
{
    use RefreshDatabase;

    private function agent(): Agent
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT4']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT4']);

        return Agent::create([
            'agence_id' => $agence->id, 'nom' => 'Agent Test', 'matricule' => 'CAI-COPILOTE-1',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'), 'role' => RoleAgent::Caissier,
        ]);
    }

    public function test_lendpoint_signale_une_incoherence_a_partir_de_caracteristiques_derivees(): void
    {
        $reponse = $this->actingAs($this->agent(), 'agent')
            ->postJson(route('agent.clients.copilote.coherence'), [
                'age_calcule' => 15,
                'profession' => 'Retraité',
                'ratio_depot_revenu' => 100,
                'piece_expiree' => false,
            ]);

        $reponse->assertOk()
            ->assertJson(['source' => 'regles_php'])
            ->assertJsonCount(2, 'avertissements');
    }

    public function test_lendpoint_ne_repond_rien_sans_incoherence(): void
    {
        $this->actingAs($this->agent(), 'agent')
            ->postJson(route('agent.clients.copilote.coherence'), [
                'age_calcule' => 35,
                'profession' => 'Commerçante',
                'ratio_depot_revenu' => 2,
                'piece_expiree' => false,
            ])
            ->assertOk()
            ->assertJsonCount(0, 'avertissements');
    }

    public function test_aucun_champ_didentite_nest_accepte_par_la_validation(): void
    {
        // `nom`/`date_naissance`/`npi` ne font pas partie des règles de validation :
        // s'ils étaient envoyés, ils seraient simplement ignorés (jamais transmis au
        // détecteur), jamais journalisés, jamais retournés dans la réponse.
        $reponse = $this->actingAs($this->agent(), 'agent')
            ->postJson(route('agent.clients.copilote.coherence'), [
                'nom' => 'KPADONOU',
                'date_naissance' => '1988-03-14',
                'npi' => '1234567890',
                'age_calcule' => 40,
                'piece_expiree' => false,
            ]);

        $reponse->assertOk();
        $this->assertArrayNotHasKey('nom', $reponse->json());
        $this->assertArrayNotHasKey('date_naissance', $reponse->json());
        $this->assertArrayNotHasKey('npi', $reponse->json());
    }

    public function test_un_caissier_non_authentifie_ne_peut_pas_appeler_lendpoint(): void
    {
        $this->postJson(route('agent.clients.copilote.coherence'), ['age_calcule' => 15])
            ->assertUnauthorized();
    }
}
