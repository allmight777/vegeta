<?php

namespace Tests\Feature\NonDivulgation;

use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\DeclarationCentif;
use App\Models\EntreeListe;
use App\Models\JournalAudit;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Services\Filtrage\MoteurFiltrage;
use App\Support\MotsInterditsConformite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NonDivulgationGuichetTest extends TestCase
{
    use RefreshDatabase;

    private function agence(): Agence
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);

        return Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
    }

    private function guichet(Agence $agence, string $matricule = 'GUI-9'): Agent
    {
        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Guichet',
            'matricule' => $matricule,
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Guichet,
        ]);
    }

    public function test_la_fiche_d_un_client_gele_ne_contient_aucun_mot_interdit_pour_le_guichet(): void
    {
        EntreeListe::create(['source' => 'demo', 'nom' => 'AHOUANDJINOU Rachidatou', 'categorie' => 'Sanction fictive']);

        $agence = $this->agence();
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'AHOUANDJINOU', 'prenoms' => 'Rachidatou', 'champs_manquants' => []]);
        app(MoteurFiltrage::class)->filtrer($client->fresh());

        $guichet = $this->guichet($agence);

        $reponse = $this->actingAs($guichet, 'agent')->get(route('agent.clients.completer', $client));

        $reponse->assertOk();
        $html = $reponse->getContent();
        $motTrouve = MotsInterditsConformite::contient($html);
        $this->assertNull($motTrouve, "Le mot interdit « {$motTrouve} » apparaît sur la page guichet.");
        $reponse->assertSee('Vérification complémentaire requise');
    }

    public function test_le_guichet_ne_peut_pas_acceder_au_filtrage_et_la_tentative_est_journalisee(): void
    {
        $agence = $this->agence();
        $guichet = $this->guichet($agence, 'GUI-10');

        $this->actingAs($guichet, 'agent')->get('/espace/filtrage')->assertForbidden();

        $this->assertTrue(
            JournalAudit::where('action', 'tentative_acces_refusee')->where('acteur_id', $guichet->id)->exists()
        );
    }

    public function test_le_guichet_ne_peut_pas_generer_une_declaration_centif(): void
    {
        $agence = $this->agence();
        $guichet = $this->guichet($agence, 'GUI-11');

        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        $declaration = DeclarationCentif::create([
            'client_id' => $client->id,
            'montant_cumule' => 16000000,
            'periode' => now()->format('Y-m'),
            'statut' => 'a_preparer',
        ]);

        $this->actingAs($guichet, 'agent')
            ->post("/espace/conformite/declarations-centif/{$declaration->id}/generer")
            ->assertForbidden();
    }
}
