<?php

namespace Tests\Feature\Operations;

use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Enums\StatutCompte;
use App\Enums\TypeClient;
use App\Enums\TypeOperation;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Operation;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HistoriqueCaissierTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_caissier_consulte_et_filtre_uniquement_ses_depots_et_retraits(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
        $caissier = $this->agent($agence, 'CAI-1');
        $autreCaissier = $this->agent($agence, 'CAI-2');

        $compteAdjoua = $this->compte($reseau, $agence, 'ADJOUA', 'Marie', 'CPT-001');
        $compteKoffi = $this->compte($reseau, $agence, 'KOFFI', 'Jean', 'CPT-002');

        Operation::create([
            'compte_id' => $compteAdjoua->id, 'agence_id' => $agence->id, 'agent_id' => $caissier->id,
            'type' => TypeOperation::Depot, 'montant' => 25000, 'mode_paiement' => 'especes',
            'devise_code' => 'XOF', 'effectuee_le' => now(), 'canal' => 'guichet',
        ]);
        Operation::create([
            'compte_id' => $compteKoffi->id, 'agence_id' => $agence->id, 'agent_id' => $autreCaissier->id,
            'type' => TypeOperation::Retrait, 'montant' => 9000, 'mode_paiement' => 'mobile_money',
            'devise_code' => 'XOF', 'effectuee_le' => now(), 'canal' => 'guichet',
        ]);

        $this->actingAs($caissier, 'agent')
            ->get(route('agent.operations.historique', ['client' => 'marie', 'type' => 'depot']))
            ->assertOk()
            ->assertSee('Marie ADJOUA')
            ->assertSee('Dépôt')
            ->assertDontSee('Jean KOFFI');
    }

    private function agent(Agence $agence, string $matricule): Agent
    {
        return Agent::create([
            'agence_id' => $agence->id, 'nom' => 'Caissier', 'matricule' => $matricule,
            'mot_de_passe' => Hash::make('mot-de-passe-solide'), 'role' => RoleAgent::Caissier,
        ]);
    }

    private function compte(Reseau $reseau, Agence $agence, string $nom, string $prenoms, string $numero): Compte
    {
        $client = Client::create([
            'reseau_id' => $reseau->id, 'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte, 'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => $nom, 'prenoms' => $prenoms, 'champs_manquants' => []]);

        return Compte::create(['client_id' => $client->id, 'agence_id' => $agence->id, 'numero' => $numero, 'statut' => StatutCompte::Actif]);
    }
}
