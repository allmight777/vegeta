<?php

namespace Tests\Feature\Kyc;

use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Models\Admin;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use App\Services\Kyc\CreateurClient;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Demo\CopiloteSaisieDemoSeeder;
use Database\Seeders\ReferentielDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 17_PROMPT §1 : propositions pendant la frappe, insensibles à la casse et aux accents (normalisation
 * PHP, donc identique sur SQLite / PostgreSQL / MySQL), 5 au plus, jamais d'erreur ni de bruit.
 */
class PropositionsPendantLaFrappeTest extends TestCase
{
    use RefreshDatabase;

    private function contexte(string $code = 'PR'): array
    {
        $reseau = Reseau::create(['nom' => "Réseau $code", 'code' => $code]);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => "Agence $code", 'code' => "A$code"]);
        $agent = Agent::create([
            'agence_id' => $agence->id, 'nom' => 'Caissier', 'matricule' => "CAI-$code",
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'), 'role' => RoleAgent::Caissier,
        ]);

        return [$reseau, $agence, $agent];
    }

    private function dossier(Reseau $reseau, Agence $agence, string $nom, array $champs): void
    {
        $client = app(CreateurClient::class)->creer(
            ['type' => 'personne_physique', 'nature_relation' => 'titulaire_compte', 'nom' => $nom, 'prenoms' => 'Test'] + $champs,
            $reseau->id, SourceCreation::SaisieAgent, null,
        );
        $client->update(['agence_creation_id' => $agence->id]);
    }

    private function proposer(Agent $agent, string $champ, string $valeur)
    {
        return $this->actingAs($agent, 'agent')->postJson(route('agent.clients.copilote.propositions'), ['champ' => $champ, 'valeur' => $valeur]);
    }

    public function test_un_prefixe_sans_accent_ni_majuscule_propose_l_orthographe_accentuee(): void
    {
        [$reseau, $agence, $agent] = $this->contexte();
        $this->dossier($reseau, $agence, 'UN', ['profession' => 'Étudiant']);
        $this->dossier($reseau, $agence, 'DEUX', ['profession' => 'Étudiant']);
        $this->dossier($reseau, $agence, 'TROIS', ['profession' => 'Commerçante']);

        $this->proposer($agent, 'profession', 'etu')->assertOk()
            ->assertJsonPath('propositions.0.valeur', 'Étudiant')->assertJsonPath('propositions.0.nombre', 2);
        $this->proposer($agent, 'profession', 'ETU')->assertJsonPath('propositions.0.valeur', 'Étudiant');
        $this->proposer($agent, 'profession', 'Étu')->assertJsonPath('propositions.0.valeur', 'Étudiant');
        $this->proposer($agent, 'profession', 'commercant')->assertJsonPath('propositions.0.valeur', 'Commerçante');
        $this->proposer($agent, 'profession', 'com')->assertJsonPath('propositions.0.valeur', 'Commerçante');
    }

    public function test_les_variantes_de_casse_et_d_accent_sont_regroupees_sous_la_plus_frequente(): void
    {
        [$reseau, $agence, $agent] = $this->contexte();
        foreach (['A', 'B', 'C'] as $nom) {
            $this->dossier($reseau, $agence, $nom, ['profession' => 'Commerçante']);
        }
        $this->dossier($reseau, $agence, 'D', ['profession' => 'commercante']);

        $reponse = $this->proposer($agent, 'profession', 'comm')->assertOk();

        $reponse->assertJsonCount(1, 'propositions')->assertJsonPath('propositions.0.valeur', 'Commerçante')->assertJsonPath('propositions.0.nombre', 4);
    }

    public function test_au_plus_cinq_propositions_les_plus_utilisees_d_abord(): void
    {
        [$reseau, $agence, $agent] = $this->contexte();
        foreach (['Masseur', 'Maçon', 'Marin', 'Marchand', 'Maraîcher', 'Maître', 'Mareyeur'] as $i => $metier) {
            $this->dossier($reseau, $agence, "N$i", ['profession' => $metier]);
        }
        $this->dossier($reseau, $agence, 'BIS', ['profession' => 'Maçon']);

        $reponse = $this->proposer($agent, 'profession', 'ma')->assertOk();

        $reponse->assertJsonCount(5, 'propositions')->assertJsonPath('propositions.0.valeur', 'Maçon');
    }

    public function test_rien_ne_correspond_liste_vide_sans_erreur(): void
    {
        [$reseau, $agence, $agent] = $this->contexte();
        $this->dossier($reseau, $agence, 'UN', ['profession' => 'Enseignant']);

        $this->proposer($agent, 'profession', 'zzz')->assertOk()->assertExactJson(['propositions' => []]);
        // moins de 2 caractères, ou saisie déjà exacte : rien
        $this->proposer($agent, 'profession', 'e')->assertOk()->assertExactJson(['propositions' => []]);
        $this->proposer($agent, 'profession', 'Enseignant')->assertOk()->assertExactJson(['propositions' => []]);
        $this->proposer($agent, 'profession', '')->assertOk()->assertExactJson(['propositions' => []]);
    }

    public function test_les_valeurs_d_un_autre_reseau_ne_sont_jamais_proposees(): void
    {
        [$reseauA, $agenceA] = $this->contexte('RA');
        [, , $agentB] = $this->contexte('RB');
        $this->dossier($reseauA, $agenceA, 'UN', ['profession' => 'Astronaute']);

        $this->proposer($agentB, 'profession', 'astro')->assertOk()->assertExactJson(['propositions' => []]);
    }

    public function test_les_champs_employeur_et_nationalite_sont_pris_en_charge_et_un_champ_identite_refuse(): void
    {
        [$reseau, $agence, $agent] = $this->contexte();
        $this->dossier($reseau, $agence, 'UN', ['employeur' => 'Mairie de Dassa', 'nationalite' => 'Béninoise']);

        $this->proposer($agent, 'employeur', 'mai')->assertJsonPath('propositions.0.valeur', 'Mairie de Dassa');
        $this->proposer($agent, 'nationalite', 'benin')->assertJsonPath('propositions.0.valeur', 'Béninoise');
        $this->proposer($agent, 'nom', 'abc')->assertStatus(422);
        $this->proposer($agent, 'lieu_naissance', 'abc')->assertStatus(422);
    }

    public function test_l_endpoint_exige_un_agent_connecte(): void
    {
        $this->postJson(route('agent.clients.copilote.propositions'), ['champ' => 'profession', 'valeur' => 'etu'])->assertUnauthorized();
    }

    public function test_le_seeder_de_demonstration_couvre_les_prefixes_courants(): void
    {
        $this->seed(ReferentielDemoSeeder::class);
        $this->seed(CopiloteSaisieDemoSeeder::class);
        $agent = Agent::create([
            'agence_id' => Agence::where('code', 'DASSA')->value('id'), 'nom' => 'Caissier', 'matricule' => 'CAI-D',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'), 'role' => RoleAgent::Caissier,
        ]);

        foreach (['com' => 'Commerçante', 'etu' => 'Étudiant', 'agr' => 'Agriculteur', 'cou' => 'Couturière', 'men' => 'Menuisier', 'coi' => 'Coiffeuse'] as $prefixe => $attendu) {
            $this->proposer($agent, 'profession', $prefixe)->assertJsonPath('propositions.0.valeur', $attendu);
        }
    }

    public function test_le_formulaire_expose_la_liste_deroulante_et_la_source_sans_mention_a_confirmer(): void
    {
        [, , $agent] = $this->contexte();

        $page = $this->actingAs($agent, 'agent')->get(route('agent.clients.creer'))->assertOk();

        $page->assertSee('data-copilote-liste="profession"', false)
            ->assertSee('copiloteFrappe', false)
            ->assertSee('class="source-info"', false)
            ->assertSee('Source : Briefing CIF', false)
            ->assertSee('Source : Réglementaire', false)
            ->assertDontSee('à confirmer');
    }

    public function test_l_ecran_admin_des_regles_garde_la_source_complete(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = Admin::create(['reseau_id' => null, 'nom' => 'Admin', 'email' => 'a@test.demo', 'mot_de_passe' => Hash::make('un-mot-de-passe-solide')]);

        // La traçabilité des sources se démontre ici, pas sur l'écran de saisie.
        $this->actingAs($admin, 'admin')->get(route('admin.regles-detection.index'))
            ->assertOk()
            ->assertSee('Briefing CIF — à confirmer');
    }
}
