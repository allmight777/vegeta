<?php

namespace Tests\Feature\Assistance;

use App\Enums\RoleAgent;
use App\Enums\StatutEscalade;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\EscaladeAssistantIa;
use App\Models\Reseau;
use App\Services\Assistance\BaseConnaissances;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EscaladeAssistantIaTest extends TestCase
{
    use RefreshDatabase;

    private function agent(RoleAgent $role, Agence $agence): Agent
    {
        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => 'GUI-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => $role,
        ]);
    }

    private function agence(): Agence
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);

        return Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
    }

    public function test_une_question_transmise_explicitement_cree_une_ligne_en_attente(): void
    {
        $agence = $this->agence();
        $guichet = $this->agent(RoleAgent::Guichet, $agence);

        $reponse = $this->actingAs($guichet, 'agent')->postJson(route('agent.assistant.escalader'), [
            'question' => 'Quelle est la procédure exacte pour une fusion de deux réseaux ?',
            'ecran' => 'agent.tableau-de-bord.index',
            'reponse_ia' => 'Je n\'ai pas d\'information là-dessus dans ma base de connaissances.',
        ]);

        $reponse->assertOk();
        $this->assertSame(1, EscaladeAssistantIa::count());
        $this->assertSame(StatutEscalade::EnAttente, EscaladeAssistantIa::first()->statut);
    }

    public function test_poser_une_question_seule_ne_cree_jamais_automatiquement_une_escalade(): void
    {
        $agence = $this->agence();
        $guichet = $this->agent(RoleAgent::Guichet, $agence);

        $this->actingAs($guichet, 'agent')->postJson(route('agent.assistant.repondre'), [
            'question' => 'Quelle est la procédure exacte pour une fusion de deux réseaux ?',
            'ecran' => 'agent.tableau-de-bord.index',
        ]);

        $this->assertSame(0, EscaladeAssistantIa::count());
    }

    public function test_une_reponse_de_responsable_alimente_immediatement_la_base_de_connaissances(): void
    {
        $agence = $this->agence();
        $guichet = $this->agent(RoleAgent::Guichet, $agence);
        $responsable = $this->agent(RoleAgent::ResponsableLbcft, $agence);

        $escalade = EscaladeAssistantIa::create([
            'agent_id' => $guichet->id,
            'role_agent' => RoleAgent::Guichet->value,
            'question' => 'Comment gérer une fusion de deux réseaux dans CIF-Empreinte ?',
            'contexte_ecran' => 'agent.tableau-de-bord.index',
            'statut' => StatutEscalade::EnAttente,
        ]);

        $reponse = $this->actingAs($responsable, 'agent')->post(route('agent.assistance.escalades.repondre', $escalade), [
            'reponse_responsable' => 'Contactez la direction technique pour toute fusion de réseaux.',
        ]);

        $reponse->assertRedirect();
        $escalade->refresh();
        $this->assertSame(StatutEscalade::Traitee, $escalade->statut);

        $trouve = app(BaseConnaissances::class)->rechercher('Comment gérer une fusion de deux réseaux ?');
        $this->assertNotNull($trouve);
        $this->assertSame('reponse_responsable', $trouve['source']);
        $this->assertSame('Contactez la direction technique pour toute fusion de réseaux.', $trouve['reponse']);
    }

    public function test_un_guichet_ne_peut_pas_acceder_a_l_ecran_des_escalades(): void
    {
        $agence = $this->agence();
        $guichet = $this->agent(RoleAgent::Guichet, $agence);

        $this->actingAs($guichet, 'agent')->get(route('agent.assistance.escalades.index'))->assertForbidden();
    }
}
