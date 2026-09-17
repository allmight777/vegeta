<?php

namespace Tests\Feature\Assistance;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\JournalAudit;
use App\Models\Reseau;
use App\Services\Assistance\FiltreConformiteReponseIa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FiltreConformiteReponseIaTest extends TestCase
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

    public function test_une_reponse_piegee_n_atteint_jamais_le_guichet_et_est_journalisee(): void
    {
        $guichet = $this->agent(RoleAgent::Guichet);

        $resultat = app(FiltreConformiteReponseIa::class)->filtrer(
            'Ce client fait l\'objet d\'une DOS en cours de traitement.',
            $guichet,
        );

        $this->assertSame(FiltreConformiteReponseIa::MESSAGE_NEUTRE, $resultat);
        $this->assertTrue(JournalAudit::where('action', 'assistant_ia_reponse_filtree')->exists());
    }

    public function test_la_meme_reponse_passe_intacte_pour_un_responsable_lbcft(): void
    {
        $responsable = $this->agent(RoleAgent::ResponsableLbcft);

        $resultat = app(FiltreConformiteReponseIa::class)->filtrer(
            'Ce client fait l\'objet d\'une DOS en cours de traitement.',
            $responsable,
        );

        $this->assertSame('Ce client fait l\'objet d\'une DOS en cours de traitement.', $resultat);
    }

    public function test_une_reponse_sans_mot_interdit_passe_intacte_pour_le_guichet(): void
    {
        $guichet = $this->agent(RoleAgent::Guichet);

        $resultat = app(FiltreConformiteReponseIa::class)->filtrer(
            'Le score de complétude indique la part des champs déjà renseignés.',
            $guichet,
        );

        $this->assertSame('Le score de complétude indique la part des champs déjà renseignés.', $resultat);
    }
}
