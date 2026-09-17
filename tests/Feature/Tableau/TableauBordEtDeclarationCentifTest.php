<?php

namespace Tests\Feature\Tableau;

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
use App\Models\DeclarationCentif;
use App\Models\Operation;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TableauBordEtDeclarationCentifTest extends TestCase
{
    use RefreshDatabase;

    private function agence(): Agence
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);

        return Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
    }

    public function test_le_tableau_de_bord_conformite_affiche_les_quatre_blocs(): void
    {
        $agence = $this->agence();
        $conformite = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Conformité',
            'matricule' => 'LBC-9',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::ResponsableLbcft,
        ]);

        $reponse = $this->actingAs($conformite, 'agent')->get(route('agent.tableau-de-bord.index'));

        $reponse->assertOk();
        $reponse->assertSee('Dossiers à compléter');
        $reponse->assertSee('Alertes du jour');
        $reponse->assertSee('Seuils et fractionnements');
        $reponse->assertSee('Déclarations CENTIF à venir');
    }

    public function test_le_tableau_de_bord_guichet_est_simplifie_et_sans_alertes(): void
    {
        $agence = $this->agence();
        $guichet = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Guichet',
            'matricule' => 'GUI-9',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Guichet,
        ]);

        $reponse = $this->actingAs($guichet, 'agent')->get(route('agent.tableau-de-bord.index'));

        $reponse->assertOk();
        $reponse->assertSee('Opérations du jour');
        $reponse->assertDontSee('Alertes du jour');
    }

    public function test_generer_une_declaration_centif_produit_un_pdf_avec_bandeau_demo(): void
    {
        Storage::fake('local');

        $agence = $this->agence();
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'ADJOVI', 'prenoms' => 'Éric', 'champs_manquants' => []]);
        $compte = Compte::create(['client_id' => $client->id, 'agence_id' => $agence->id, 'numero' => 'CPT-1', 'statut' => StatutCompte::Actif]);
        Operation::create([
            'compte_id' => $compte->id,
            'agence_id' => $agence->id,
            'type' => TypeOperation::Depot,
            'montant' => 16000000,
            'devise_code' => 'XOF',
            'effectuee_le' => now(),
            'canal' => 'guichet',
        ]);
        $declaration = DeclarationCentif::create([
            'client_id' => $client->id,
            'montant_cumule' => 16000000,
            'periode' => now()->format('Y-m'),
            'statut' => 'a_preparer',
        ]);

        $conformite = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Conformité',
            'matricule' => 'LBC-8',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Direction,
        ]);

        $reponse = $this->actingAs($conformite, 'agent')->post("/espace/conformite/declarations-centif/{$declaration->id}/generer");

        $reponse->assertRedirect(route('agent.tableau-de-bord.index'));
        $declaration->refresh();
        $this->assertSame('generee', $declaration->statut->value);
        $this->assertNotNull($declaration->fichier_pdf_path);
        Storage::disk('local')->assertExists($declaration->fichier_pdf_path);
    }
}
