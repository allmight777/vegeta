<?php

namespace Tests\Feature\Kyc;

use App\Contracts\DetecteurConnectivite;
use App\Enums\RoleAgent;
use App\Enums\StatutVerificationNpi;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Reseau;
use App\Models\VerificationNpiEnAttente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VerificationNpiModeDegradeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->app->bind(DetecteurConnectivite::class, fn () => new class implements DetecteurConnectivite
        {
            public function estEnLigne(): bool
            {
                return false;
            }
        });
    }

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

    public function test_un_npi_plausible_hors_connexion_cree_le_client_en_attente_de_rattrapage(): void
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

        $client = Client::first();
        $this->assertNotNull($client, 'Le client doit être créé même hors connexion.');
        $this->assertSame(StatutVerificationNpi::EnAttenteConnexion, $client->statut_verification_npi);
        $this->assertSame(1, VerificationNpiEnAttente::where('client_id', $client->id)->count());
    }

    public function test_un_npi_au_format_manifestement_invalide_reste_bloque_meme_hors_connexion(): void
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
            'npi' => 'ABC',
        ]);

        $reponse->assertSessionHasErrors('npi');
        $this->assertSame(0, Client::count());
        $this->assertSame(0, VerificationNpiEnAttente::count());
    }
}
