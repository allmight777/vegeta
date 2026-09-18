<?php

namespace Tests\Feature\Roles;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 09_PROMPT_TROIS_PROFILS §7 : `agents:migrer-roles` fusionne les anciens rôles vers les
 * deux nouveaux, sans jamais supprimer de compte — y compris quand un doublon
 * `responsable_lbcft` + `direction` existe sur la même agence.
 */
class MigrationRolesTest extends TestCase
{
    use RefreshDatabase;

    private function agence(): Agence
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);

        return Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
    }

    private function creerAvecAncienRole(Agence $agence, string $matricule, string $ancienRole): Agent
    {
        // Les anciennes valeurs sont dépréciées dans l'enum PHP mais restent des chaînes
        // valides en base — on écrit directement la colonne pour simuler l'état
        // pré-migration, sans passer par le cast RoleAgent qui n'accepterait qu'une
        // valeur d'enum côté écriture applicative normale.
        $agent = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => $matricule,
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Caissier,
        ]);
        DB::table('agents')->where('id', $agent->id)->update(['role' => $ancienRole]);

        return $agent->fresh();
    }

    public function test_la_commande_migre_les_trois_anciens_roles_sans_perte_de_compte(): void
    {
        $agence = $this->agence();
        $guichet = $this->creerAvecAncienRole($agence, 'GUI-1', 'guichet');
        $lbcft = $this->creerAvecAncienRole($agence, 'LBC-1', 'responsable_lbcft');
        $direction = $this->creerAvecAncienRole($agence, 'DIR-1', 'direction');

        $nombreAvant = Agent::count();

        $this->artisan('agents:migrer-roles')->assertSuccessful();

        $this->assertSame($nombreAvant, Agent::count());

        $this->assertSame('caissier', $guichet->fresh()->role->value);
        $this->assertSame('responsable_agence', $lbcft->fresh()->role->value);
        $this->assertSame('responsable_agence', $direction->fresh()->role->value);

        $this->assertSame(0, DB::table('agents')->whereIn('role', ['guichet', 'responsable_lbcft', 'direction'])->count());
    }

    public function test_un_doublon_responsable_lbcft_et_direction_sur_la_meme_agence_devient_deux_comptes_responsable_agence(): void
    {
        $agence = $this->agence();
        $lbcft = $this->creerAvecAncienRole($agence, 'LBC-2', 'responsable_lbcft');
        $direction = $this->creerAvecAncienRole($agence, 'DIR-2', 'direction');

        $this->artisan('agents:migrer-roles');

        $this->assertNotNull(Agent::find($lbcft->id));
        $this->assertNotNull(Agent::find($direction->id));
        $this->assertSame(2, Agent::where('agence_id', $agence->id)->where('role', 'responsable_agence')->count());
    }

    public function test_la_commande_est_idempotente(): void
    {
        $agence = $this->agence();
        $this->creerAvecAncienRole($agence, 'GUI-3', 'guichet');

        $this->artisan('agents:migrer-roles')->assertSuccessful();
        $nombreApresPremierPassage = Agent::count();

        $this->artisan('agents:migrer-roles')->assertSuccessful();

        $this->assertSame($nombreApresPremierPassage, Agent::count());
        $this->assertSame(0, DB::table('agents')->whereIn('role', ['guichet', 'responsable_lbcft', 'direction'])->count());
    }
}
