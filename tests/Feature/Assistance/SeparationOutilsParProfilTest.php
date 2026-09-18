<?php

namespace Tests\Feature\Assistance;

use App\Enums\RoleAgent;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use App\Services\Assistance\Outils\OutilConsulterParametreReglementaire;
use App\Services\Assistance\Outils\OutilRechercherClientExistant;
use App\Services\Assistance\Outils\OutilStatistiquesAgregeesAgence;
use App\Services\Assistance\OutilsParRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §8 : un GestionnaireAssistant construit pour
 * un caissier n'a jamais accès à OutilConsulterParametreReglementaire ni aux outils
 * réservés au responsable/admin — vérifié directement sur le jeu d'outils, pas seulement
 * via l'interface (invocation directe interdite, pas seulement cachée).
 */
class SeparationOutilsParProfilTest extends TestCase
{
    use RefreshDatabase;

    private function agent(RoleAgent $role): Agent
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);

        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => 'AGT-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => $role,
        ]);
    }

    public function test_le_caissier_na_jamais_loutil_parametre_reglementaire_ni_les_outils_reserves(): void
    {
        $caissier = $this->agent(RoleAgent::Caissier);

        $outils = app(OutilsParRole::class)->pour($caissier);
        $noms = array_map(fn ($outil) => $outil->nom(), $outils);

        $this->assertNotContains('consulter_parametre_reglementaire', $noms);
        $this->assertNotContains('compter_alertes_du_jour', $noms);
        $this->assertNotContains('rechercher_client_existant', $noms);
        $this->assertNotContains('statistiques_agence', $noms);

        foreach ($outils as $outil) {
            $this->assertNotInstanceOf(OutilConsulterParametreReglementaire::class, $outil);
            $this->assertNotInstanceOf(OutilRechercherClientExistant::class, $outil);
            $this->assertNotInstanceOf(OutilStatistiquesAgregeesAgence::class, $outil);
        }
    }

    public function test_le_responsable_d_agence_a_bien_les_outils_reserves(): void
    {
        $responsable = $this->agent(RoleAgent::ResponsableAgence);

        $noms = array_map(fn ($outil) => $outil->nom(), app(OutilsParRole::class)->pour($responsable));

        $this->assertContains('consulter_parametre_reglementaire', $noms);
        $this->assertContains('compter_alertes_du_jour', $noms);
        $this->assertContains('statistiques_agence', $noms);
    }

    public function test_le_caissier_garde_les_outils_communs(): void
    {
        $caissier = $this->agent(RoleAgent::Caissier);

        $noms = array_map(fn ($outil) => $outil->nom(), app(OutilsParRole::class)->pour($caissier));

        $this->assertContains('recherche_documentaire', $noms);
        $this->assertContains('base_connaissances_produit', $noms);
        $this->assertContains('recherche_web', $noms);
        $this->assertContains('profils_incomplets', $noms);
    }
}
