<?php

namespace Tests\Feature\Rapports;

use App\Enums\GraviteAlerte;
use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Enums\StatutAlerte;
use App\Enums\StatutCompte;
use App\Enums\TypeAlerte;
use App\Enums\TypeClient;
use App\Enums\TypeOperation;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Identite;
use App\Models\Operation;
use App\Models\PersonnePhysique;
use App\Models\RapportJournalier;
use App\Models\Reseau;
use App\Services\Rapports\GenerateurRapportJournalier;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RapportJournalierTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_caissier_genere_un_pdf_pour_sa_periode(): void
    {
        Storage::fake('local');
        $reseau = Reseau::create(['nom' => 'Réseau test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence test', 'code' => 'AT']);
        $agent = Agent::create(['agence_id' => $agence->id, 'nom' => 'Caissier', 'matricule' => 'CAI-1', 'mot_de_passe' => Hash::make('mot-de-passe'), 'role' => RoleAgent::Caissier]);
        $client = Client::create(['reseau_id' => $reseau->id, 'agence_creation_id' => $agence->id, 'type' => TypeClient::PersonnePhysique, 'nature_relation' => NatureRelation::TitulaireCompte, 'source_creation' => SourceCreation::SaisieAgent]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'TOSSOU', 'prenoms' => 'Alphonsine', 'champs_manquants' => []]);
        $compte = Compte::create(['client_id' => $client->id, 'agence_id' => $agence->id, 'numero' => 'CPT-1', 'statut' => StatutCompte::Actif]);
        Operation::create(['compte_id' => $compte->id, 'agence_id' => $agence->id, 'agent_id' => $agent->id, 'type' => TypeOperation::Depot, 'montant' => 10000, 'mode_paiement' => 'especes', 'devise_code' => 'XOF', 'effectuee_le' => now(), 'canal' => 'guichet']);

        $this->actingAs($agent, 'agent')->post(route('agent.rapports.generer'), [
            'date_debut' => today()->toDateString(), 'date_fin' => today()->toDateString(),
        ])->assertRedirect();

        $rapport = RapportJournalier::firstOrFail();
        $this->assertSame($agence->id, $rapport->agence_id);
        Storage::disk('local')->assertExists($rapport->fichier_pdf_path);
    }

    public function test_les_donnees_du_rapport_relient_le_compte_reactive_et_le_plafond_par_cotation(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau test', 'code' => 'RT2']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence test', 'code' => 'AT2']);

        $identite = Identite::create(['reseau_id' => $reseau->id, 'plafond_quotidien_especes' => 250000]);
        $client = Client::create([
            'reseau_id' => $reseau->id, 'agence_creation_id' => $agence->id, 'identite_id' => $identite->id,
            'type' => TypeClient::PersonnePhysique, 'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'TOSSOU', 'prenoms' => 'Alphonsine', 'champs_manquants' => []]);
        $compte = Compte::create(['client_id' => $client->id, 'agence_id' => $agence->id, 'numero' => 'CPT-DORMANT', 'statut' => StatutCompte::Actif]);

        $agent = Agent::create(['agence_id' => $agence->id, 'nom' => 'Caissier', 'matricule' => 'CAI-2', 'mot_de_passe' => Hash::make('mot-de-passe'), 'role' => RoleAgent::Caissier]);
        Operation::create(['compte_id' => $compte->id, 'agence_id' => $agence->id, 'agent_id' => $agent->id, 'type' => TypeOperation::Depot, 'montant' => 10000, 'mode_paiement' => 'especes', 'devise_code' => 'XOF', 'effectuee_le' => now(), 'canal' => 'guichet']);

        Alerte::create([
            'type' => TypeAlerte::CompteDormantReactive,
            'client_id' => $client->id,
            'gravite' => GraviteAlerte::Attention,
            'explication_texte' => 'Test',
            'faits' => ['mois_inactivite' => 8, 'montant' => 10000, 'compte_id' => $compte->id, 'operation_id' => null],
            'statut' => StatutAlerte::Nouvelle,
        ]);

        $donnees = app(GenerateurRapportJournalier::class)->donnees($agence, CarbonImmutable::today(), CarbonImmutable::today());

        $this->assertSame('CPT-DORMANT', $donnees['comptesParId'][$compte->id]->numero);
        $this->assertSame(250000.0, (float) $donnees['operations']->first()->compte->client->identite->plafond_quotidien_especes);
    }
}
