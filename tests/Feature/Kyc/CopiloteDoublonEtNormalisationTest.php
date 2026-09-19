<?php

namespace Tests\Feature\Kyc;

use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\JournalAudit;
use App\Models\Reseau;
use App\Services\Kyc\CreateurClient;
use Database\Seeders\Demo\CopiloteSaisieDemoSeeder;
use Database\Seeders\ReferentielDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 16_PROMPT §2.1 (alerte doublon par empreinte) et §2.3 (normalisation d'activité).
 */
class CopiloteDoublonEtNormalisationTest extends TestCase
{
    use RefreshDatabase;

    private function reseauAvecAgences(string $code): array
    {
        $reseau = Reseau::create(['nom' => "Réseau $code", 'code' => $code]);

        return [
            $reseau,
            Agence::create(['reseau_id' => $reseau->id, 'nom' => "Agence de Savalou $code", 'code' => "S$code"]),
            Agence::create(['reseau_id' => $reseau->id, 'nom' => "Agence de Dassa $code", 'code' => "D$code"]),
        ];
    }

    private function caissier(Agence $agence): Agent
    {
        return Agent::create([
            'agence_id' => $agence->id, 'nom' => 'Caissier', 'matricule' => 'CAI-'.$agence->code,
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'), 'role' => RoleAgent::Caissier,
        ]);
    }

    private function creerDossier(Reseau $reseau, Agence $agence, array $champs): void
    {
        $client = app(CreateurClient::class)->creer(
            ['type' => 'personne_physique', 'nature_relation' => 'titulaire_compte'] + $champs,
            $reseau->id,
            SourceCreation::SaisieAgent,
            null,
        );
        $client->update(['agence_creation_id' => $agence->id]);
    }

    public function test_un_nom_proche_dans_le_reseau_leve_l_alerte_sans_reveler_le_dossier(): void
    {
        [$reseau, $savalou, $dassa] = $this->reseauAvecAgences('R1');
        $this->creerDossier($reseau, $savalou, ['nom' => 'ADJAHO', 'prenoms' => 'Koffi', 'date_naissance' => '1985-06-12']);

        $reponse = $this->actingAs($this->caissier($dassa), 'agent')
            ->postJson(route('agent.clients.copilote.doublon'), ['nom' => 'ADJAO', 'prenoms' => 'Kofi']);

        $reponse->assertOk()->assertJson(['doublon' => true]);
        $this->assertStringContainsString('Agence de Savalou R1', $reponse->json('message'));

        // Jamais le nom du dossier existant, ni un identifiant client.
        $brut = $reponse->getContent();
        $this->assertStringNotContainsString('ADJAHO', $brut);
        $this->assertStringNotContainsString('Koffi', $brut);
        $this->assertSame(['doublon', 'message', 'source'], array_keys($reponse->json()));
    }

    public function test_pas_d_alerte_pour_un_autre_nom_ni_une_autre_date_de_naissance(): void
    {
        [$reseau, $savalou, $dassa] = $this->reseauAvecAgences('R2');
        $this->creerDossier($reseau, $savalou, ['nom' => 'ADJAHO', 'prenoms' => 'Koffi', 'date_naissance' => '1985-06-12']);
        $agent = $this->caissier($dassa);

        $this->actingAs($agent, 'agent')->postJson(route('agent.clients.copilote.doublon'), ['nom' => 'KOUGBLENOU', 'prenoms' => 'Jean'])
            ->assertOk()->assertExactJson(['doublon' => false]);

        $this->postJson(route('agent.clients.copilote.doublon'), ['nom' => 'ADJAHO', 'prenoms' => 'Koffi', 'date_naissance' => '1990-01-01'])
            ->assertOk()->assertExactJson(['doublon' => false]);

        $this->postJson(route('agent.clients.copilote.doublon'), ['nom' => 'ADJAHO', 'prenoms' => 'Koffi', 'date_naissance' => '1985-06-12'])
            ->assertOk()->assertJson(['doublon' => true]);
    }

    public function test_un_dossier_d_un_autre_reseau_n_est_jamais_signale(): void
    {
        [$reseauA, $savalouA] = $this->reseauAvecAgences('RA');
        [, , $dassaB] = $this->reseauAvecAgences('RB');
        $this->creerDossier($reseauA, $savalouA, ['nom' => 'ADJAHO', 'prenoms' => 'Koffi']);

        $this->actingAs($this->caissier($dassaB), 'agent')
            ->postJson(route('agent.clients.copilote.doublon'), ['nom' => 'ADJAHO', 'prenoms' => 'Koffi'])
            ->assertOk()->assertExactJson(['doublon' => false]);
    }

    public function test_la_verification_de_doublon_ne_journalise_aucune_identite(): void
    {
        [, , $dassa] = $this->reseauAvecAgences('R3');

        $this->actingAs($this->caissier($dassa), 'agent')
            ->postJson(route('agent.clients.copilote.doublon'), ['nom' => 'SECRETNAME', 'prenoms' => 'Prenomsecret'])->assertOk();

        $this->assertTrue(JournalAudit::where('action', 'copilote_verification_doublon')->exists());
        $this->assertSame(0, JournalAudit::where('cible_id', 'like', '%SECRET%')->orWhere('cible_type', 'like', '%SECRET%')->count());
    }

    public function test_la_normalisation_propose_l_orthographe_dominante_du_reseau(): void
    {
        [$reseau, $savalou, $dassa] = $this->reseauAvecAgences('R4');
        foreach (['AGBO', 'SOGLO', 'ZINSOU'] as $nom) {
            $this->creerDossier($reseau, $dassa, ['nom' => $nom, 'prenoms' => 'Test', 'profession' => 'Commerçante']);
        }
        $agent = $this->caissier($dassa);

        $reponse = $this->actingAs($agent, 'agent')
            ->postJson(route('agent.clients.copilote.normalisation'), ['champ' => 'profession', 'valeur' => 'commercante']);

        $reponse->assertOk()->assertJsonPath('suggestion.valeur', 'Commerçante');
        $this->assertStringContainsString('3 dossiers', $reponse->json('suggestion.message'));

        // Déjà la bonne orthographe : rien à proposer.
        $this->postJson(route('agent.clients.copilote.normalisation'), ['champ' => 'profession', 'valeur' => 'Commerçante'])
            ->assertOk()->assertExactJson(['suggestion' => null]);

        // Valeur sans rapport, ou champ non autorisé.
        $this->postJson(route('agent.clients.copilote.normalisation'), ['champ' => 'profession', 'valeur' => 'Astronaute'])
            ->assertOk()->assertExactJson(['suggestion' => null]);
        $this->postJson(route('agent.clients.copilote.normalisation'), ['champ' => 'nom', 'valeur' => 'x'])->assertStatus(422);
    }

    public function test_une_orthographe_trop_rare_n_est_pas_proposee_et_l_autre_reseau_n_est_pas_compte(): void
    {
        [$reseauA, , $dassaA] = $this->reseauAvecAgences('R5');
        [$reseauB, , $dassaB] = $this->reseauAvecAgences('R6');
        $this->creerDossier($reseauA, $dassaA, ['nom' => 'UN', 'prenoms' => 'Seul', 'profession' => 'Menuisier']);
        foreach (['DEUX', 'TROIS'] as $nom) {
            $this->creerDossier($reseauB, $dassaB, ['nom' => $nom, 'prenoms' => 'Autre', 'profession' => 'Menuisier']);
        }

        $this->actingAs($this->caissier($dassaA), 'agent')
            ->postJson(route('agent.clients.copilote.normalisation'), ['champ' => 'profession', 'valeur' => 'menuisier'])
            ->assertOk()->assertExactJson(['suggestion' => null]);
    }

    public function test_les_endpoints_exigent_un_agent_connecte(): void
    {
        $this->postJson(route('agent.clients.copilote.doublon'), ['nom' => 'X'])->assertUnauthorized();
        $this->postJson(route('agent.clients.copilote.normalisation'), ['champ' => 'profession', 'valeur' => 'x'])->assertUnauthorized();
    }

    public function test_le_seeder_de_demonstration_fournit_de_quoi_declencher_les_trois_comportements(): void
    {
        $this->seed(ReferentielDemoSeeder::class);
        $this->seed(CopiloteSaisieDemoSeeder::class);

        $dassa = Agence::where('code', 'DASSA')->firstOrFail();
        $agent = $this->caissier($dassa);
        $this->actingAs($agent, 'agent');

        $this->postJson(route('agent.clients.copilote.doublon'), ['nom' => 'ADJAO', 'prenoms' => 'Kofi'])
            ->assertJson(['doublon' => true]);
        $this->postJson(route('agent.clients.copilote.normalisation'), ['champ' => 'profession', 'valeur' => 'commercante'])
            ->assertJsonPath('suggestion.valeur', 'Commerçante');
        $this->postJson(route('agent.clients.copilote.coherence'), ['age_calcule' => 25, 'profession' => 'Retraité', 'ratio_depot_revenu' => 83.3, 'piece_expiree' => true])
            ->assertJsonCount(3, 'avertissements');
    }

    public function test_le_formulaire_expose_les_zones_du_copilote(): void
    {
        [, , $dassa] = $this->reseauAvecAgences('R7');

        $this->actingAs($this->caissier($dassa), 'agent')->get(route('agent.clients.creer'))
            ->assertOk()
            ->assertSee('data-copilote-cible="nom"', false)
            ->assertSee('data-copilote-cible="profession"', false)
            ->assertSee('copiloteBlur', false);
    }
}
