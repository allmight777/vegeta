<?php

namespace Tests\Feature\Kyc;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Reseau;
use App\Services\Kyc\CalculateurCompletude;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FormulaireClientEnrichiTest extends TestCase
{
    use RefreshDatabase;

    private function agenceEtAgent(RoleAgent $role = RoleAgent::Guichet): Agent
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);

        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => 'GUI-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => $role,
        ]);
    }

    public function test_la_creation_d_une_personne_physique_calcule_le_total_des_versements(): void
    {
        $agent = $this->agenceEtAgent();

        $reponse = $this->actingAs($agent, 'agent')->post(route('agent.clients.stocker'), [
            'type' => 'personne_physique',
            'nature_relation' => 'titulaire_compte',
            'nom' => 'KPADONOU',
            'prenoms' => 'Fidèle',
            'date_naissance' => '1988-03-14',
            'piece_identite_numero' => 'CIP-1',
            'piece_identite_type' => 'cni',
            'droit_adhesion' => '1000',
            'part_sociale' => '5000',
            'depot_especes' => '2000',
        ]);

        $reponse->assertRedirect();

        $client = Client::first();
        $this->assertNotNull($client);
        $this->assertSame('8000.00', (string) $client->personnePhysique->total_versements_initiaux);
    }

    public function test_une_personne_morale_sans_beneficiaire_effectif_ni_signataire_reste_bloquante(): void
    {
        $agent = $this->agenceEtAgent();

        $reponse = $this->actingAs($agent, 'agent')->post(route('agent.clients.stocker'), [
            'type' => 'personne_morale',
            'nature_relation' => 'titulaire_compte',
            'raison_sociale' => 'SARL DEMO',
        ]);
        $reponse->assertRedirect();

        $client = Client::first();
        $this->assertLessThan(100, $client->score_completude_kyc);
        $manquants = app(CalculateurCompletude::class)->champsBloquantsManquants($client->fresh(['personneMorale']));
        $this->assertContains('au_moins_un_signataire', $manquants);
    }

    public function test_la_page_liste_des_clients_n_est_pas_cassee_et_propose_le_bouton_uploader(): void
    {
        $agent = $this->agenceEtAgent();

        $reponse = $this->actingAs($agent, 'agent')->get(route('agent.clients.index'));

        $reponse->assertOk();
        $reponse->assertSee('Nouveau client');
        $reponse->assertSee(route('agent.clients.import.creer'), false);
    }
}
