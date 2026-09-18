<?php

namespace Tests\Feature\Roles;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * 09_PROMPT_TROIS_PROFILS §7 : un compte caissier ne doit jamais atteindre une route de
 * l'espace responsable, même par URL directe — vérifié route par route plutôt que par un
 * seul échantillon, pour couvrir chaque fichier de routes/responsable/*.php.
 */
class SeparationCaissierResponsableTest extends TestCase
{
    use RefreshDatabase;

    private function caissier(): Agent
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);

        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Caissier',
            'matricule' => 'CAI-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Caissier,
        ]);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function nomsDeRoutesResponsable(): array
    {
        return [
            'tableau de bord' => ['responsable.tableau-de-bord.index'],
            'filtrage' => ['responsable.filtrage.index'],
            'escalades' => ['responsable.assistance.escalades.index'],
            'clients (supervision)' => ['responsable.clients.index'],
        ];
    }

    #[DataProvider('nomsDeRoutesResponsable')]
    public function test_un_caissier_recoit_403_sur_une_route_responsable(string $nomDeRoute): void
    {
        $caissier = $this->caissier();

        $reponse = $this->actingAs($caissier, 'agent')->get(route($nomDeRoute));

        $reponse->assertForbidden();
    }

    public function test_un_caissier_recoit_403_sur_lassistant_du_responsable(): void
    {
        $caissier = $this->caissier();

        $reponse = $this->actingAs($caissier, 'agent')->postJson(route('responsable.assistant.repondre'), [
            'question' => 'Test',
            'ecran' => 'responsable.tableau-de-bord.index',
        ]);

        $reponse->assertForbidden();
    }
}
