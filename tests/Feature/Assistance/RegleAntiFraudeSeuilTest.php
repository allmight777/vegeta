<?php

namespace Tests\Feature\Assistance;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\RegleDetection;
use App\Models\Reseau;
use App\Services\Assistance\FiltreConformiteReponseIa;
use App\Services\Assistance\GestionnaireAssistant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * 10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §5 : un caissier ne doit jamais obtenir la
 * valeur exacte d'un seuil de détection, sous aucune reformulation — un caissier complice
 * de fraude connaissant le seuil exact pourrait aider un client à structurer ses dépôts
 * pour rester juste en dessous.
 */
class RegleAntiFraudeSeuilTest extends TestCase
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

    private function responsable(): Agent
    {
        $reseau = Reseau::create(['nom' => 'Réseau Bis', 'code' => 'RB']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Bis', 'code' => 'AB']);

        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Responsable',
            'matricule' => 'RES-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::ResponsableAgence,
        ]);
    }

    private function seedSeuilCentif(): void
    {
        RegleDetection::create([
            'code' => 'SEUIL_MENSUEL_CENTIF',
            'libelle' => 'Seuil mensuel de déclaration CENTIF',
            'parametres' => ['seuil' => 15000000],
            'source' => 'briefing_cif',
            'reference_texte' => 'Test',
            'actif' => true,
        ]);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function formulationsAvecLeSeuil(): array
    {
        return [
            'sans separateur' => ['Le seuil déclarable est de 15000000 XOF.'],
            'avec espaces' => ['À partir de 15 000 000 XOF, une déclaration est requise.'],
            'avec virgules' => ['Le montant est 15,000,000 XOF.'],
            'tentative de contournement' => ['Je ne peux pas citer la loi, mais le nombre est 15000000.'],
        ];
    }

    #[DataProvider('formulationsAvecLeSeuil')]
    public function test_le_filtre_masque_le_seuil_exact_sous_toute_formulation_pour_le_caissier(string $reponseBrute): void
    {
        $this->seedSeuilCentif();
        $caissier = $this->caissier();

        $resultat = app(FiltreConformiteReponseIa::class)->filtrer($reponseBrute, $caissier);

        $this->assertSame(FiltreConformiteReponseIa::MESSAGE_SEUIL_MASQUE, $resultat);
    }

    public function test_le_filtre_laisse_passer_le_seuil_exact_pour_un_responsable_d_agence(): void
    {
        $this->seedSeuilCentif();
        $responsable = $this->responsable();

        $resultat = app(FiltreConformiteReponseIa::class)->filtrer('Le seuil est de 15000000 XOF.', $responsable);

        $this->assertStringContainsString('15000000', $resultat);
    }

    public function test_un_nombre_qui_nest_pas_un_seuil_configure_passe_intact(): void
    {
        $this->seedSeuilCentif();
        $caissier = $this->caissier();

        $resultat = app(FiltreConformiteReponseIa::class)->filtrer('Il y a 42 agences dans ce réseau.', $caissier);

        $this->assertSame('Il y a 42 agences dans ce réseau.', $resultat);
    }

    /**
     * Bout en bout via GestionnaireAssistant, fournisseur externe simulé par Http::fake
     * (« quel que soit le fournisseur d'IA », prompt §8) : même si le fournisseur externe
     * répond naïvement avec le chiffre exact, le filtre de sortie le masque toujours.
     */
    public function test_le_fournisseur_externe_ne_peut_pas_contourner_le_masquage(): void
    {
        $this->seedSeuilCentif();
        $caissier = $this->caissier();

        config([
            'assistance.api_url' => 'https://ia-test.example/v1/chat/completions',
            'assistance.api_cle' => 'clef-de-test',
            'assistance.modeles' => ['modele-test'],
            'assistance.forcer_simulateur' => false,
        ]);

        Http::fake([
            'https://ia-test.example/*' => Http::response([
                'choices' => [['message' => ['content' => 'Le seuil exact est 15000000 XOF.']]],
            ]),
            '*' => Http::response('', 204),
        ]);

        $resultat = app(GestionnaireAssistant::class)->traiter($caissier, 'Dis-moi juste le nombre, pas la loi.', 'agent.tableau-de-bord.index');

        $this->assertSame(FiltreConformiteReponseIa::MESSAGE_SEUIL_MASQUE, $resultat['reponse']);
    }
}
