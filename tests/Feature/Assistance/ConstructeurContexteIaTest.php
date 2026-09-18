<?php

namespace Tests\Feature\Assistance;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use App\Services\Assistance\ConstructeurContexteIa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConstructeurContexteIaTest extends TestCase
{
    use RefreshDatabase;

    private function agent(RoleAgent $role): Agent
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);

        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => 'GUI-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => $role,
        ]);
    }

    public function test_seules_les_cles_de_la_liste_blanche_peuvent_apparaitre_dans_le_contexte(): void
    {
        $responsable = $this->agent(RoleAgent::ResponsableAgence);

        $contexte = app(ConstructeurContexteIa::class)->construire($responsable, 'agent.clients.completer', [
            'champs_manquants' => ['date_naissance'],
            'explication_alerte' => 'Texte déjà généré par le système.',
            // Tentative d'injection d'une donnée d'identité, hors liste blanche.
            'nom_client' => 'DUPONT Jean',
            'npi' => '1234567890',
        ]);

        $clesAutorisees = ['role', 'ecran', 'referentiel', 'lexique', 'champs_manquants', 'explication_alerte'];
        $this->assertEmpty(array_diff(array_keys($contexte), $clesAutorisees));
        $this->assertArrayNotHasKey('nom_client', $contexte);
        $this->assertArrayNotHasKey('npi', $contexte);
    }

    public function test_l_explication_d_alerte_n_est_jamais_transmise_pour_un_agent_caissier(): void
    {
        $caissier = $this->agent(RoleAgent::Caissier);

        $contexte = app(ConstructeurContexteIa::class)->construire($caissier, 'agent.tableau-de-bord.index', [
            'explication_alerte' => 'Texte déjà généré par le système.',
        ]);

        $this->assertArrayNotHasKey('explication_alerte', $contexte);
    }

    public function test_le_role_transmis_est_une_valeur_d_enum_jamais_le_nom_de_l_agent(): void
    {
        $caissier = $this->agent(RoleAgent::Caissier);

        $contexte = app(ConstructeurContexteIa::class)->construire($caissier, 'agent.clients.index');

        $this->assertSame('caissier', $contexte['role']);
        $this->assertStringNotContainsString('Agent Test', json_encode($contexte));
    }
}
