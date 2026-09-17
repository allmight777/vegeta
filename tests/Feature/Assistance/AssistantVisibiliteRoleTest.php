<?php

namespace Tests\Feature\Assistance;

use App\Enums\RoleAgent;
use App\Models\Admin;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AssistantVisibiliteRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_widget_est_present_sur_une_page_agent(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
        $guichet = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => 'GUI-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Guichet,
        ]);

        $this->actingAs($guichet, 'agent')->get(route('agent.clients.index'))
            ->assertOk()
            ->assertSee('Assistant CIF-Empreinte');
    }

    public function test_le_widget_est_present_sur_une_page_admin(): void
    {
        $admin = Admin::create([
            'reseau_id' => null,
            'nom' => 'Admin Test',
            'email' => 'admin.test@cif.demo',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
        ]);

        $this->actingAs($admin, 'admin')->get(route('admin.tableau-de-bord.index'))
            ->assertOk()
            ->assertSee('Assistant CIF-Empreinte');
    }
}
