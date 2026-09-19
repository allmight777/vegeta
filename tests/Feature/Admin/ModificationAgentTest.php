<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleAgent;
use App\Models\Admin;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModificationAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_l_admin_ouvre_le_formulaire_et_modifie_un_agent(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
        $admin = Admin::create(['reseau_id' => null, 'nom' => 'Admin', 'email' => 'admin@test.demo', 'mot_de_passe' => Hash::make('un-mot-de-passe-solide')]);
        $agent = Agent::create([
            'agence_id' => $agence->id, 'nom' => 'Kofi Mensah', 'matricule' => 'CAI-777', 'email' => 'kofi@exemple.test',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'), 'role' => RoleAgent::Caissier,
        ]);
        $ancienHash = $agent->mot_de_passe;

        $this->actingAs($admin, 'admin')->get(route('admin.agents.modifier', $agent))
            ->assertOk()->assertSee('Kofi Mensah')->assertSee('CAI-777');

        $this->put(route('admin.agents.mettre-a-jour', $agent), [
            'nom' => 'Kofi Nouveau', 'matricule' => 'CAI-778', 'role' => 'caissier',
            'email' => 'nouveau@exemple.test', 'civilite' => 'm', 'agence_id' => $agence->id,
        ])->assertRedirect(route('admin.agents.index'));

        $agent->refresh();
        $this->assertSame('Kofi Nouveau', $agent->nom);
        $this->assertSame('CAI-778', $agent->matricule);
        $this->assertSame($ancienHash, $agent->mot_de_passe);
    }
}
