<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleAgent;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\DocumentIa;
use App\Models\Reseau;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 16_PROMPT §4 : « Résumé du jour » du tableau de bord admin (composé localement à partir des
 * outils d'agrégation) et compteur d'utilisation des documents de la bibliothèque IA.
 */
class ResumeDuJourEtUsageDocumentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['assistance.forcer_simulateur' => true]);
    }

    private function adminDuReseau(?int $reseauId, string $email): Admin
    {
        return Admin::create(['reseau_id' => $reseauId, 'nom' => 'Admin', 'email' => $email, 'mot_de_passe' => Hash::make('un-mot-de-passe-solide')]);
    }

    public function test_le_resume_du_jour_synthetise_dossiers_et_alertes_avec_liens(): void
    {
        $this->seed(DatabaseSeeder::class);
        $alpha = $this->adminDuReseau(Reseau::where('code', 'ALPHA')->value('id'), 'alpha@test.demo');

        $page = $this->actingAs($alpha, 'admin')->get(route('admin.tableau-de-bord.index'));

        $page->assertOk()
            ->assertSee('Résumé du jour')
            ->assertSee('dossiers suivis')
            ->assertSee('profils restent incomplets')
            ->assertSee('alerte ouverte')
            ->assertSee(route('admin.regles-detection.index'), false)
            ->assertSee('Voir la bibliothèque');
    }

    public function test_un_admin_de_reseau_ne_voit_que_les_chiffres_de_son_reseau(): void
    {
        $this->seed(DatabaseSeeder::class);
        $beta = $this->adminDuReseau(Reseau::where('code', '!=', 'ALPHA')->value('id'), 'beta@test.demo');

        $this->actingAs($beta, 'admin')->get(route('admin.tableau-de-bord.index'))
            ->assertOk()
            ->assertSee("Aucun dossier client n'est encore enregistré dans votre périmètre")
            ->assertDontSee('dossiers suivis');
    }

    public function test_le_resume_ne_contient_aucune_identite(): void
    {
        $this->seed(DatabaseSeeder::class);
        $alpha = $this->adminDuReseau(Reseau::where('code', 'ALPHA')->value('id'), 'alpha2@test.demo');

        $page = $this->actingAs($alpha, 'admin')->get(route('admin.tableau-de-bord.index'));

        foreach (['AHOUANDJINOU', 'KPADONOU', 'TCHOKPON', 'DOSSOU'] as $nom) {
            $page->assertDontSee($nom);
        }
    }

    public function test_le_tableau_de_bord_admin_liste_les_reseaux(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->adminDuReseau(null, 'plateforme@test.demo'), 'admin')->get(route('admin.tableau-de-bord.index'))
            ->assertOk()
            ->assertDontSee('Aucun réseau configuré')
            ->assertSee('Réseau Alpha');
    }

    public function test_un_document_utilise_pour_repondre_voit_son_compteur_augmenter(): void
    {
        $this->seed(DatabaseSeeder::class);
        $responsable = Agent::where('role', RoleAgent::ResponsableAgence)->firstOrFail();
        $admin = $this->adminDuReseau(null, 'plateforme2@test.demo');
        $document = DocumentIa::firstOrFail();
        $this->assertSame(0, (int) $document->nombre_utilisations);

        $this->actingAs($admin, 'admin')->get(route('admin.documents-ia.index'))->assertSee('Pas encore utilisé');

        $reponse = $this->actingAs($responsable, 'agent')->postJson(route('responsable.assistant.repondre'), [
            'question' => 'typologies régionales de blanchiment',
            'ecran' => 'tableau',
        ])->assertOk();
        $this->assertStringContainsString('typologies', mb_strtolower($reponse->json('reponse')));

        $this->assertSame(1, (int) $document->fresh()->nombre_utilisations);

        $this->actingAs($admin, 'admin')->get(route('admin.documents-ia.index'))->assertSee('Utilisé 1 fois');
    }
}
