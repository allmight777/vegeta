<?php

namespace Tests\Feature\Authentification;

use App\Enums\RoleAgent;
use App\Models\Admin;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConnexionTest extends TestCase
{
    use RefreshDatabase;

    private function agence(): Agence
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);

        return Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
    }

    public function test_un_agent_peut_se_connecter_avec_son_matricule(): void
    {
        $agent = Agent::create([
            'agence_id' => $this->agence()->id,
            'nom' => 'Test Agent',
            'matricule' => 'GUI-9999',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Guichet,
            'actif' => true,
        ]);

        $reponse = $this->post('/connexion', [
            'matricule' => 'GUI-9999',
            'mot_de_passe' => 'un-mot-de-passe-solide',
        ]);

        $reponse->assertRedirect(route('agent.tableau-de-bord.index'));
        $this->assertAuthenticatedAs($agent, 'agent');
    }

    public function test_un_mauvais_mot_de_passe_est_refuse(): void
    {
        Agent::create([
            'agence_id' => $this->agence()->id,
            'nom' => 'Test Agent',
            'matricule' => 'GUI-9998',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Guichet,
            'actif' => true,
        ]);

        $reponse = $this->post('/connexion', [
            'matricule' => 'GUI-9998',
            'mot_de_passe' => 'mauvais',
        ]);

        $reponse->assertSessionHasErrors('matricule');
        $this->assertGuest('agent');
    }

    public function test_un_admin_peut_se_connecter_avec_son_email(): void
    {
        $admin = Admin::create([
            'reseau_id' => null,
            'nom' => 'Admin Test',
            'email' => 'admin@test.demo',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'actif' => true,
        ]);

        $reponse = $this->post('/admin/connexion', [
            'email' => 'admin@test.demo',
            'mot_de_passe' => 'un-mot-de-passe-solide',
        ]);

        $reponse->assertRedirect(route('admin.tableau-de-bord.index'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_un_agent_desactive_ne_peut_pas_se_connecter(): void
    {
        Agent::create([
            'agence_id' => $this->agence()->id,
            'nom' => 'Agent Désactivé',
            'matricule' => 'GUI-9997',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Guichet,
            'actif' => false,
        ]);

        $reponse = $this->post('/connexion', [
            'matricule' => 'GUI-9997',
            'mot_de_passe' => 'un-mot-de-passe-solide',
        ]);

        $reponse->assertSessionHasErrors('matricule');
        $this->assertGuest('agent');
    }

    public function test_les_deux_espaces_sont_etanches(): void
    {
        $agent = Agent::create([
            'agence_id' => $this->agence()->id,
            'nom' => 'Test Agent',
            'matricule' => 'GUI-9996',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Guichet,
            'actif' => true,
        ]);

        $this->actingAs($agent, 'agent');

        $this->get('/admin')->assertRedirect('/admin/connexion');
    }
}
