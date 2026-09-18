<?php

namespace Tests\Feature\Roles;

use App\Enums\GraviteAlerte;
use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Enums\StatutAlerte;
use App\Enums\TypeAlerte;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 09_PROMPT_TROIS_PROFILS §7 : un responsable d'agence ne voit que les alertes/dossiers de
 * sa propre agence, jamais ceux d'une autre agence du même réseau (§4, §9 « ce qu'il ne faut
 * surtout pas faire »). Les clients n'ayant pas d'`agence_id` propre, le scope repose sur
 * `clients.agence_creation_id` (docs/DECISIONS.md §17).
 */
class PorteeAgenceResponsableTest extends TestCase
{
    use RefreshDatabase;

    private function client(int $reseauId, int $agenceCreationId, string $nom): Client
    {
        $client = Client::create([
            'reseau_id' => $reseauId,
            'agence_creation_id' => $agenceCreationId,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => $nom, 'prenoms' => 'Test', 'champs_manquants' => []]);

        return $client;
    }

    public function test_le_responsable_ne_voit_que_les_alertes_de_sa_propre_agence(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agenceA = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence A', 'code' => 'AA']);
        $agenceB = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence B', 'code' => 'AB']);

        $clientA = $this->client($reseau->id, $agenceA->id, 'DOSSIER-AGENCE-A');
        $clientB = $this->client($reseau->id, $agenceB->id, 'DOSSIER-AGENCE-B');

        Alerte::create([
            'type' => TypeAlerte::FiltragePpe,
            'client_id' => $clientA->id,
            'gravite' => GraviteAlerte::Attention,
            'explication_texte' => 'Alerte visible de l\'agence A',
            'faits' => [],
            'statut' => StatutAlerte::Nouvelle,
        ]);
        Alerte::create([
            'type' => TypeAlerte::FiltragePpe,
            'client_id' => $clientB->id,
            'gravite' => GraviteAlerte::Attention,
            'explication_texte' => 'Alerte invisible de l\'agence B',
            'faits' => [],
            'statut' => StatutAlerte::Nouvelle,
        ]);

        $responsableA = Agent::create([
            'agence_id' => $agenceA->id,
            'nom' => 'Responsable A',
            'matricule' => 'RES-A1',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::ResponsableAgence,
        ]);

        $reponse = $this->actingAs($responsableA, 'agent')->get(route('responsable.tableau-de-bord.index'));

        $reponse->assertOk();
        $reponse->assertSee('Alerte visible de l\'agence A');
        $reponse->assertDontSee('Alerte invisible de l\'agence B');
    }

    public function test_le_responsable_ne_voit_que_les_dossiers_a_completer_de_sa_propre_agence(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agenceA = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence A', 'code' => 'AA']);
        $agenceB = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence B', 'code' => 'AB']);

        $this->client($reseau->id, $agenceA->id, 'KPADONOU');
        $this->client($reseau->id, $agenceB->id, 'HOUNSOU');

        $responsableA = Agent::create([
            'agence_id' => $agenceA->id,
            'nom' => 'Responsable A',
            'matricule' => 'RES-A2',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::ResponsableAgence,
        ]);

        $reponse = $this->actingAs($responsableA, 'agent')->get(route('responsable.tableau-de-bord.index'));

        $reponse->assertOk();
        $reponse->assertSee('KPADONOU');
        $reponse->assertDontSee('HOUNSOU');
    }
}
