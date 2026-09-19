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

/**
 * 15_PROMPT §2.3 : la recherche d'agents ne dépend d'aucune insensibilité à la casse
 * héritée de MySQL, et fonctionne sur les colonnes chiffrées (matricule, e-mail).
 */
class RechercheAgentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_recherche_ignore_la_casse_et_couvre_nom_matricule_et_email(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
        $admin = Admin::create(['reseau_id' => null, 'nom' => 'Admin', 'email' => 'admin@test.demo', 'mot_de_passe' => Hash::make('un-mot-de-passe-solide')]);

        foreach ([['Kofi Mensah', 'CAI-777', 'kofi@exemple.test'], ['Awa Diallo', 'CAI-888', 'awa@exemple.test']] as [$nom, $matricule, $email]) {
            Agent::create([
                'agence_id' => $agence->id, 'nom' => $nom, 'matricule' => $matricule, 'email' => $email,
                'mot_de_passe' => Hash::make('un-mot-de-passe-solide'), 'role' => RoleAgent::Caissier,
            ]);
        }

        foreach (['MENSAH', 'cai-777', 'KOFI@EXEMPLE'] as $terme) {
            $this->actingAs($admin, 'admin')->get(route('admin.agents.index', ['q' => $terme]))
                ->assertOk()->assertSee('Kofi Mensah')->assertDontSee('Awa Diallo');
        }

        $this->get(route('admin.agents.index', ['q' => 'introuvable']))->assertOk()->assertDontSee('Kofi Mensah');
    }
}
