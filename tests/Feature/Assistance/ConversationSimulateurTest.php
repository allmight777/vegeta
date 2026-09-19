<?php

namespace Tests\Feature\Assistance;

use App\Enums\RoleAgent;
use App\Models\Admin;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\RegleDetection;
use App\Models\Reseau;
use App\Services\Assistance\FiltreConformiteReponseIa;
use App\Support\MotsInterditsConformite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Le simulateur (mode démonstration : hors connexion, sans clé) ne répond jamais
 * « je n'ai pas d'information » à une salutation, une formule de politesse ou une question
 * sur l'assistant lui-même — dans les trois espaces.
 */
class ConversationSimulateurTest extends TestCase
{
    use RefreshDatabase;

    private const ECHEC = 'pas d\'information';

    protected function setUp(): void
    {
        parent::setUp();
        config(['assistance.forcer_simulateur' => true]);
    }

    private function agence(): Agence
    {
        $reseau = Reseau::firstOrCreate(['code' => 'RT'], ['nom' => 'Réseau Test']);

        return Agence::firstOrCreate(['reseau_id' => $reseau->id, 'code' => 'AT'], ['nom' => 'Agence Test']);
    }

    private function agent(RoleAgent $role): Agent
    {
        return Agent::create([
            'agence_id' => $this->agence()->id, 'nom' => 'Agent', 'matricule' => 'M-'.$role->value,
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'), 'role' => $role,
        ]);
    }

    /**
     * @return array<string, array{0: string, 1: string}> espace => [garde, route]
     */
    private function espaces(): array
    {
        return [
            'caissier' => [$this->agent(RoleAgent::Caissier), 'agent', 'agent.assistant.repondre'],
            'responsable' => [$this->agent(RoleAgent::ResponsableAgence), 'agent', 'responsable.assistant.repondre'],
            'admin' => [Admin::create(['reseau_id' => null, 'nom' => 'Admin', 'email' => 'a@test.demo', 'mot_de_passe' => Hash::make('un-mot-de-passe-solide')]), 'admin', 'admin.assistant.repondre'],
        ];
    }

    private function deuxEspaces(): array
    {
        $espaces = $this->espaces();

        return [$espaces['caissier'], $espaces['responsable']];
    }

    private function poser($utilisateur, string $garde, string $route, string $question): string
    {
        $reponse = $this->actingAs($utilisateur, $garde)->postJson(route($route), ['question' => $question, 'ecran' => 'tableau'])->assertOk();

        $this->assertSame('simulateur', $reponse->json('source'));
        $this->assertNotSame(FiltreConformiteReponseIa::MESSAGE_NEUTRE, $reponse->json('reponse'), "« $question » a reçu le message d'escalade");
        $this->assertFalse($reponse->json('peut_escalader'), "« $question » propose une escalade");
        if ($route === 'agent.assistant.repondre') {
            $this->assertNull(MotsInterditsConformite::contient((string) $reponse->json('reponse')), "« $question » : terme interdit au caissier");
        }

        return (string) $reponse->json('reponse');
    }

    public function test_les_echanges_courants_n_obtiennent_jamais_le_message_d_echec_dans_les_trois_espaces(): void
    {
        $questions = [
            'salut', 'Salut !', 'SALUT', 'slt', 'bjr', 'cc', 'coucou', 'hey', 'hello', 'yo', 'Bonjour', 'bonsoir', 'saluuuut',
            'bonjour à tous', 'salut l\'assistant',
            'ça va ?', 'comment vas-tu', 'merci', 'Merci beaucoup !', 'ok', 'd\'accord', 'au revoir', 'bye', 'à plus',
            'qui es-tu ?', 'tu fais quoi', 'tu sers à quoi', 'aide', 'help', 'que peux-tu faire ?',
        ];

        foreach ($this->espaces() as $espace => [$utilisateur, $garde, $route]) {
            foreach ([...$questions, ...$questions, ...$questions] as $question) {
                $reponse = $this->poser($utilisateur, $garde, $route, $question);

                $this->assertNotEmpty($reponse, "$espace / $question");
                $this->assertStringNotContainsString(self::ECHEC, $reponse, "$espace / « $question » a reçu le message d'échec");
            }
        }
    }

    public function test_la_presentation_donne_des_exemples_de_questions_adaptes_au_role(): void
    {
        [[$caissier, $gc, $rc], [$responsable, $gr, $rr]] = $this->deuxEspaces();

        $this->assertStringContainsString('score de complétude', $this->poser($caissier, $gc, $rc, 'qui es-tu ?'));
        $this->assertStringContainsString('alertes', $this->poser($responsable, $gr, $rr, 'que peux-tu faire ?'));
    }

    public function test_une_salutation_suivie_d_une_vraie_question_suit_le_chemin_normal(): void
    {
        [$caissier, $garde, $route] = $this->espaces()['caissier'];

        $reponse = $this->poser($caissier, $garde, $route, 'Bonjour, quels sont les profils incomplets ?');

        $this->assertStringContainsString('incomplet', $reponse);
    }

    public function test_les_reponses_varient(): void
    {
        [$caissier, $garde, $route] = $this->espaces()['caissier'];

        $reponses = [];
        for ($i = 0; $i < 25; $i++) {
            $reponses[$this->poser($caissier, $garde, $route, 'salut')] = true;
        }

        $this->assertGreaterThan(1, count($reponses));
    }

    public function test_les_questions_metier_reelles_ne_tombent_pas_sur_le_message_generique(): void
    {
        [[$caissier, $gc, $rc], [$responsable, $gr, $rr]] = $this->deuxEspaces();

        $this->assertStringNotContainsString(self::ECHEC, $this->poser($caissier, $gc, $rc, 'quels sont les profils incomplets ?'));
        $this->assertStringNotContainsString(self::ECHEC, $this->poser($responsable, $gr, $rr, 'combien d\'alertes aujourd\'hui ?'));
        $this->assertStringContainsString('Alertes ouvertes', $this->poser($responsable, $gr, $rr, 'combien d\'alertes aujourd\'hui ?'));
    }

    public function test_une_question_de_seuil_en_langage_naturel_trouve_la_regle_pour_le_responsable(): void
    {
        RegleDetection::create([
            'code' => 'SEUIL_MENSUEL_CENTIF', 'libelle' => 'Seuil mensuel de déclaration CENTIF',
            'parametres' => ['seuil' => 15000000], 'source' => 'demo', 'actif' => true,
        ]);
        [[$responsable, $garde, $route]] = [$this->espaces()['responsable']];

        $reponse = $this->poser($responsable, $garde, $route, 'quel est le seuil de déclaration ?');

        $this->assertStringContainsString('Seuil mensuel de déclaration CENTIF', $reponse);
        $this->assertStringContainsString('source : demo', $reponse);
    }
}
