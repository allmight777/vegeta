<?php

namespace Tests\Feature\Kyc;

use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FicheRlbcftVisibiliteTest extends TestCase
{
    use RefreshDatabase;

    private function agent(RoleAgent $role): Agent
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

    private function client(Agent $agent): Client
    {
        $client = Client::create([
            'reseau_id' => $agent->agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'KPADONOU', 'prenoms' => 'Fidèle', 'champs_manquants' => []]);

        return $client;
    }

    public function test_le_guichet_ne_voit_jamais_le_groupe_fiche_rlbcft_sur_creer_et_completer(): void
    {
        $guichet = $this->agent(RoleAgent::Guichet);
        $client = $this->client($guichet);

        $this->actingAs($guichet, 'agent')->get(route('agent.clients.creer'))
            ->assertOk()->assertDontSee('Fiche complémentaire RLBC/FT');

        $this->actingAs($guichet, 'agent')->get(route('agent.clients.completer', $client))
            ->assertOk()->assertDontSee('Fiche complémentaire RLBC/FT');
    }

    public function test_le_guichet_ne_peut_pas_forcer_l_enregistrement_de_la_fiche_rlbcft_meme_en_injectant_les_champs(): void
    {
        $guichet = $this->agent(RoleAgent::Guichet);
        $client = $this->client($guichet);

        $reponse = $this->actingAs($guichet, 'agent')->put(route('agent.clients.mettre-a-jour', $client), [
            'nom' => 'KPADONOU',
            'prenoms' => 'Fidèle',
            'date_naissance' => '1988-03-14',
            'piece_identite_type' => 'cni',
            'piece_identite_numero' => 'CIP-1',
            'fiche_rlbcft' => ['ppe_national' => '1', 'visa_rlbcft_nom' => 'Un Responsable'],
        ]);
        $reponse->assertSessionDoesntHaveErrors();

        $client->refresh();
        $this->assertNull($client->ficheRlbcft);
    }

    public function test_le_responsable_lbcft_voit_le_groupe_et_peut_l_enregistrer(): void
    {
        $responsable = $this->agent(RoleAgent::ResponsableLbcft);
        $client = $this->client($responsable);

        $this->actingAs($responsable, 'agent')->get(route('agent.clients.completer', $client))
            ->assertOk()->assertSee('Fiche complémentaire RLBC/FT');

        $reponse = $this->actingAs($responsable, 'agent')->put(route('agent.clients.mettre-a-jour', $client), [
            'nom' => 'KPADONOU',
            'prenoms' => 'Fidèle',
            'date_naissance' => '1988-03-14',
            'piece_identite_type' => 'cni',
            'piece_identite_numero' => 'CIP-1',
            'fiche_rlbcft' => ['ppe_national' => '1', 'visa_rlbcft_nom' => 'Un Responsable'],
        ]);
        $reponse->assertSessionDoesntHaveErrors();

        $client->refresh();
        $this->assertNotNull($client->ficheRlbcft);
        $this->assertTrue($client->ficheRlbcft->ppe_national);
    }
}
