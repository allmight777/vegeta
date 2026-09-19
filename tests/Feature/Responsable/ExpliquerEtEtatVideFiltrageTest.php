<?php

namespace Tests\Feature\Responsable;

use App\Enums\RoleAgent;
use App\Enums\StatutFiltrage;
use App\Models\Agent;
use App\Models\DecisionFiltrage;
use App\Models\ResultatFiltrage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 16_PROMPT §3 : sur les données de démonstration, la file de filtrage montre la mémoire de
 * décisions et un bouton « Expliquer » ; sans précédent, l'encart le dit explicitement au
 * lieu de disparaître ; le tableau de bord expose le même bouton sur ses alertes.
 */
class ExpliquerEtEtatVideFiltrageTest extends TestCase
{
    use RefreshDatabase;

    private function responsable(): Agent
    {
        $this->seed(DatabaseSeeder::class);

        return Agent::where('role', RoleAgent::ResponsableAgence)->firstOrFail();
    }

    public function test_la_file_montre_la_memoire_de_decisions_et_le_bouton_expliquer(): void
    {
        $page = $this->actingAs($this->responsable(), 'agent')->get(route('responsable.filtrage.index'));

        $page->assertOk()
            ->assertSee('cas similaires déjà tranchés', false)
            ->assertSee('expliquer-btn', false)
            ->assertSee('Explication locale', false)
            ->assertSee('ressemble fortement à', false);
    }

    public function test_sans_precedent_l_encart_l_indique_au_lieu_de_disparaitre(): void
    {
        $responsable = $this->responsable();
        DecisionFiltrage::query()->delete();

        $this->actingAs($responsable, 'agent')->get(route('responsable.filtrage.index'))
            ->assertOk()
            ->assertSee("Aucun cas similaire tranché pour l'instant", false)
            ->assertDontSee('cas similaires déjà tranchés', false);
    }

    public function test_le_tableau_de_bord_expose_le_bouton_expliquer_sur_les_alertes(): void
    {
        $this->actingAs($this->responsable(), 'agent')->get(route('responsable.tableau-de-bord.index'))
            ->assertOk()
            ->assertSee('expliquer-btn', false);
    }

    public function test_la_suggestion_de_motif_en_mode_simulateur_repond_sans_erreur(): void
    {
        $responsable = $this->responsable();
        config(['assistance.forcer_simulateur' => true]);
        $resultat = ResultatFiltrage::where('statut', StatutFiltrage::AVerifier)->firstOrFail();

        $reponse = $this->actingAs($responsable, 'agent')->postJson(
            route('responsable.filtrage.suggerer-motif', $resultat),
            ['texte' => 'Le client a présenté sa pièce, la date de naissance ne correspond pas à la personne listée'],
        );

        $reponse->assertOk()->assertJsonStructure(['motif_code', 'libelle']);
    }
}
