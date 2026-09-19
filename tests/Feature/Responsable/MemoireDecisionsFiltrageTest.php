<?php

namespace Tests\Feature\Responsable;

use App\Contracts\ProviderIa;
use App\Enums\MotifDecisionFiltrage;
use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Enums\SourceListeType;
use App\Enums\StatutFiltrage;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\DecisionFiltrage;
use App\Models\EntreeListe;
use App\Models\Identite;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Models\ResultatFiltrage;
use App\Services\Assistance\SelecteurProviderIa;
use App\Services\Filtrage\MemoireDecisions;
use App\Services\Filtrage\SuggereurMotifDecision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * 12_PROMPT_IA_INTEGREE_PROFONDE §6 — "mémoire de décisions". Le comptage des cas
 * similaires (point 1) doit fonctionner sans IA du tout ; la suggestion de motif
 * (point 2) est une amélioration ergonomique qui ne doit jamais pré-remplir une
 * décision ni laisser fuiter une identité vers le fournisseur externe.
 */
class MemoireDecisionsFiltrageTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_comptage_des_cas_similaires_ne_depend_daucune_ia(): void
    {
        [$reseau, $agence, $responsable] = $this->reseauAgenceResponsable();
        $entree = $this->entreeListe();

        // Deux décisions déjà prises sur cette même entrée de liste, pour deux clients
        // différents, avec des motifs différents — le motif dominant doit être le plus
        // fréquent.
        $this->creerDecision($entree, $reseau, MotifDecisionFiltrage::HomonymeSimple, $responsable);
        $this->creerDecision($entree, $reseau, MotifDecisionFiltrage::HomonymeSimple, $responsable);
        $this->creerDecision($entree, $reseau, MotifDecisionFiltrage::PieceIdentiteVerifiee, $responsable);

        $resultat = app(MemoireDecisions::class)->casSimilaires($entree, $reseau->id);

        $this->assertSame(3, $resultat['total']);
        $this->assertSame(MotifDecisionFiltrage::HomonymeSimple, $resultat['parMotif'][0]['motif']);
        $this->assertSame(2, $resultat['parMotif'][0]['nombre']);
    }

    public function test_les_cas_dun_autre_reseau_ne_sont_pas_comptes(): void
    {
        [$reseau, , $responsable] = $this->reseauAgenceResponsable();
        $autreReseau = Reseau::create(['nom' => 'Autre Réseau', 'code' => 'AR2']);
        $entree = $this->entreeListe();

        $this->creerDecision($entree, $autreReseau, MotifDecisionFiltrage::HomonymeSimple, $responsable);

        $resultat = app(MemoireDecisions::class)->casSimilaires($entree, $reseau->id);

        $this->assertSame(0, $resultat['total']);
    }

    public function test_la_suggestion_ia_ne_preremplit_jamais_rien_et_le_fournisseur_reste_muet_hors_connexion(): void
    {
        [, $agence, $responsable] = $this->reseauAgenceResponsable();
        $entree = $this->entreeListe();
        $client = $this->client($agence);

        $motif = app(SuggereurMotifDecision::class)->suggerer(
            'La pièce a été contrôlée physiquement au guichet hier.',
            $client,
            $entree,
            $responsable,
        );

        // Simulateur (hors connexion par défaut dans les tests) : ne trouve rien dans
        // la base de connaissances pour ce texte, dégrade proprement à `null`.
        $this->assertNull($motif);
    }

    public function test_un_motif_qui_nomme_le_client_ou_la_personne_listee_najamais_atteint_le_fournisseur(): void
    {
        [, $agence, $responsable] = $this->reseauAgenceResponsable();
        $entree = $this->entreeListe('KOUASSI DOSSOU');
        $client = $this->client($agence, 'KOUASSI', 'Paul');

        $providerQuiNeDoitJamaisEtreAppele = new class implements ProviderIa
        {
            public function repondre(string $question, array $contexte, array $outils, $utilisateur, array $historique = []): string
            {
                throw new \RuntimeException('Le fournisseur IA ne doit jamais être appelé quand le texte contient un nom.');
            }
        };

        $selecteur = new class($providerQuiNeDoitJamaisEtreAppele) extends SelecteurProviderIa
        {
            public function __construct(private readonly ProviderIa $provider)
            {
                //
            }

            public function choisir(): ProviderIa
            {
                return $this->provider;
            }
        };

        $suggereur = new SuggereurMotifDecision($selecteur);

        $motif = $suggereur->suggerer(
            'Il s\'agit bien de Kouassi, notre client de longue date, pas la personne recherchée.',
            $client,
            $entree,
            $responsable,
        );

        $this->assertNull($motif);
    }

    public function test_lendpoint_de_suggestion_ne_modifie_jamais_le_resultat_de_filtrage(): void
    {
        [, $agence, $responsable] = $this->reseauAgenceResponsable();
        $entree = $this->entreeListe();
        $client = $this->client($agence);
        $resultat = ResultatFiltrage::create([
            'filtrable_type' => Client::class,
            'filtrable_id' => $client->id,
            'entree_liste_id' => $entree->id,
            'score_similarite' => 0.9,
            'statut' => StatutFiltrage::AVerifier,
        ]);

        $this->actingAs($responsable, 'agent')
            ->postJson(route('responsable.filtrage.suggerer-motif', $resultat), [
                'texte' => 'Un motif quelconque de test, assez long pour passer la validation.',
            ])
            ->assertOk()
            ->assertJsonStructure(['motif_code', 'libelle']);

        $this->assertSame(StatutFiltrage::AVerifier, $resultat->fresh()->statut);
    }

    /** @return array{0: Reseau, 1: Agence, 2: Agent} */
    private function reseauAgenceResponsable(): array
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT2']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT2']);
        $responsable = Agent::create([
            'agence_id' => $agence->id, 'nom' => 'Responsable', 'matricule' => 'RESP-MD-1',
            'mot_de_passe' => Hash::make('mot-de-passe-solide'), 'role' => RoleAgent::ResponsableAgence,
        ]);

        return [$reseau, $agence, $responsable];
    }

    private function entreeListe(string $nom = 'DOSSOU Jean'): EntreeListe
    {
        return EntreeListe::create([
            'source' => SourceListeType::Demo,
            'nom' => $nom,
            'version_liste' => 'v1',
        ]);
    }

    private function client(Agence $agence, string $nom = 'ADJOVI', string $prenoms = 'Fidèle'): Client
    {
        $client = Client::create([
            'reseau_id' => $agence->reseau_id, 'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte, 'source_creation' => SourceCreation::SaisieAgent,
            'agence_creation_id' => $agence->id,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => $nom, 'prenoms' => $prenoms, 'champs_manquants' => []]);

        return $client;
    }

    private function creerDecision(EntreeListe $entree, Reseau $reseau, MotifDecisionFiltrage $motif, Agent $agent): void
    {
        $identite = Identite::create(['reseau_id' => $reseau->id]);

        DecisionFiltrage::create([
            'cle_decision' => Str::random(64),
            'identite_id' => $identite->id,
            'entree_liste_id' => $entree->id,
            'portee' => 'identite',
            'source_liste' => $entree->source->value,
            'version_liste' => $entree->version_liste,
            'statut' => StatutFiltrage::Ecarte,
            'motif_code' => $motif,
            'decide_par_agent_id' => $agent->id,
            'decide_le' => now(),
            'expire_le' => now()->addDays(90),
        ]);
    }
}
