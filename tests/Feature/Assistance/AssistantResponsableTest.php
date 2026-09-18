<?php

namespace Tests\Feature\Assistance;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §2.1 : le responsable d'agence a son propre
 * contrôleur assistant (Responsable\Assistance\AssistantController), distinct de celui du
 * caissier — la route répond, et le widget qui le sert n'expose pas de bouton d'escalade
 * (le responsable est déjà la cible des escalades des caissiers).
 */
class AssistantResponsableTest extends TestCase
{
    use RefreshDatabase;

    private function responsable(): Agent
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);

        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Responsable',
            'matricule' => 'RES-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::ResponsableAgence,
        ]);
    }

    public function test_le_responsable_dagence_peut_poser_une_question_a_son_assistant(): void
    {
        $responsable = $this->responsable();

        $reponse = $this->actingAs($responsable, 'agent')->postJson(route('responsable.assistant.repondre'), [
            'question' => 'Comment fonctionne le score de complétude ?',
            'ecran' => 'responsable.tableau-de-bord.index',
        ]);

        $reponse->assertOk();
        $reponse->assertJsonStructure(['reponse', 'source', 'peut_escalader']);
    }

    public function test_la_page_du_tableau_de_bord_responsable_affiche_le_widget_sans_bouton_descalade(): void
    {
        $responsable = $this->responsable();

        $reponse = $this->actingAs($responsable, 'agent')->get(route('responsable.tableau-de-bord.index'));

        $reponse->assertOk();
        $reponse->assertSee('Assistant CIF-Empreinte');
        // urlEscalader vaut null pour le responsable (pas de cible d'escalade au-dessus
        // de lui, il est déjà celle des caissiers) — @js(null) rend littéralement "null".
        $reponse->assertSee('urlEscalader: null', false);
    }
}
