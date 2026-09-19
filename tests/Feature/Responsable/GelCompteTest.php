<?php

namespace Tests\Feature\Responsable;

use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Enums\StatutCompte;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Compte;
use App\Models\JournalAudit;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Loi art. 89 à 91 : le gel doit être une décision humaine tracée, pas seulement le
 * blocage automatique déjà couvert par ClientPolicy::peutValiderOperation. Vérifie
 * aussi que le gel n'est jamais levé silencieusement par une future opération (bug
 * corrigé dans OperationController::stocker, qui écrasait `statut` en `actif`).
 */
class GelCompteTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_responsable_gele_un_compte_avec_motif_et_la_decision_est_tracee(): void
    {
        [$responsable, $compte] = $this->responsableEtCompte();

        $reponse = $this->actingAs($responsable, 'agent')
            ->put(route('responsable.comptes.geler', $compte), [
                'motif' => 'Correspondance forte confirmée en attente de vérification approfondie.',
            ]);

        $reponse->assertRedirect();
        $compte->refresh();

        $this->assertSame(StatutCompte::Gele, $compte->statut);
        $this->assertNotNull($compte->gele_le);
        $this->assertSame($responsable->id, $compte->gele_par_agent_id);
        $this->assertSame(
            'Correspondance forte confirmée en attente de vérification approfondie.',
            $compte->motif_gel
        );
    }

    public function test_le_gel_est_refuse_sans_motif(): void
    {
        [$responsable, $compte] = $this->responsableEtCompte();

        $this->actingAs($responsable, 'agent')
            ->put(route('responsable.comptes.geler', $compte), ['motif' => ''])
            ->assertSessionHasErrors('motif');

        $this->assertSame(StatutCompte::Actif, $compte->fresh()->statut);
    }

    public function test_un_caissier_ne_peut_pas_operer_sur_un_compte_gele(): void
    {
        [$responsable, $compte] = $this->responsableEtCompte();
        $caissier = $this->agent($compte->agence, 'CAI-GEL');

        $this->actingAs($responsable, 'agent')->put(route('responsable.comptes.geler', $compte), [
            'motif' => 'Correspondance forte confirmée en attente de vérification approfondie.',
        ]);

        $reponse = $this->actingAs($caissier, 'agent')->post(route('agent.operations.stocker'), [
            'compte_id' => $compte->id,
            'type' => 'depot',
            'montant' => 10000,
            'mode_paiement' => 'especes',
        ]);

        $reponse->assertRedirect(route('agent.operations.creer'));
        $this->assertSame(0, $compte->operations()->count());
        $this->assertTrue(
            JournalAudit::where('action', 'operation_refusee_compte_gele')->where('cible_id', $compte->id)->exists(),
            'Le refus doit être tracé comme un refus pour compte gelé, pas confondu avec un autre motif de blocage.'
        );
        // Le caissier ne voit jamais la raison exacte du refus (art. 63) : message neutre.
        $this->assertStringNotContainsString('gel', mb_strtolower((string) session('statut')));
    }

    public function test_lever_le_gel_reactive_le_compte_et_trace_la_levee(): void
    {
        [$responsable, $compte] = $this->responsableEtCompte();
        $compte->update([
            'statut' => StatutCompte::Gele,
            'gele_le' => now(),
            'gele_par_agent_id' => $responsable->id,
            'motif_gel' => 'Motif initial.',
        ]);

        $this->actingAs($responsable, 'agent')
            ->put(route('responsable.comptes.lever', $compte))
            ->assertRedirect();

        $compte->refresh();
        $this->assertSame(StatutCompte::Actif, $compte->statut);
        $this->assertNotNull($compte->leve_le);
        $this->assertSame($responsable->id, $compte->leve_par_agent_id);
    }

    public function test_un_responsable_dune_autre_agence_ne_peut_pas_geler(): void
    {
        [, $compte] = $this->responsableEtCompte();
        $autreReseau = Reseau::create(['nom' => 'Autre Réseau', 'code' => 'AR']);
        $autreAgence = Agence::create(['reseau_id' => $autreReseau->id, 'nom' => 'Autre Agence', 'code' => 'AA']);
        $autreResponsable = Agent::create([
            'agence_id' => $autreAgence->id, 'nom' => 'Autre Responsable', 'matricule' => 'RESP-AUTRE',
            'mot_de_passe' => Hash::make('mot-de-passe-solide'), 'role' => RoleAgent::ResponsableAgence,
        ]);

        $this->actingAs($autreResponsable, 'agent')
            ->put(route('responsable.comptes.geler', $compte), ['motif' => 'Tentative hors agence, doit être refusée.'])
            ->assertForbidden();

        $this->assertSame(StatutCompte::Actif, $compte->fresh()->statut);
    }

    /** @return array{0: Agent, 1: Compte} */
    private function responsableEtCompte(): array
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);

        $responsable = Agent::create([
            'agence_id' => $agence->id, 'nom' => 'Responsable', 'matricule' => 'RESP-1',
            'mot_de_passe' => Hash::make('mot-de-passe-solide'), 'role' => RoleAgent::ResponsableAgence,
        ]);

        $client = Client::create([
            'reseau_id' => $reseau->id, 'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte, 'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'KPADONOU', 'prenoms' => 'Fidèle', 'champs_manquants' => []]);
        $compte = Compte::create([
            'client_id' => $client->id, 'agence_id' => $agence->id, 'numero' => 'CPT-GEL-1', 'statut' => StatutCompte::Actif,
        ]);

        return [$responsable, $compte];
    }

    private function agent(Agence $agence, string $matricule): Agent
    {
        return Agent::create([
            'agence_id' => $agence->id, 'nom' => 'Caissier', 'matricule' => $matricule,
            'mot_de_passe' => Hash::make('mot-de-passe-solide'), 'role' => RoleAgent::Caissier,
        ]);
    }
}
