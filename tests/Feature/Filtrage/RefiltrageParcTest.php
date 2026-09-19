<?php

namespace Tests\Feature\Filtrage;

use App\Enums\NatureRelation;
use App\Enums\SourceCreation;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\EntreeListe;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Services\Empreinte\ServiceEmpreinte;
use App\Services\Filtrage\RefiltrageParc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Le scénario réglementaire du refiltrage : un membre déjà enregistré, une liste
 * publiée APRÈS son entrée en relation. Sans refiltrage, il n'est jamais détecté.
 */
class RefiltrageParcTest extends TestCase
{
    use RefreshDatabase;

    private function clientExistant(string $nom, string $prenoms): Client
    {
        $reseau = Reseau::firstOrCreate(['code' => 'ALPHA'], ['nom' => 'Réseau Alpha']);
        $agence = Agence::firstOrCreate(
            ['reseau_id' => $reseau->id, 'code' => 'DASSA'],
            ['nom' => 'Agence de Dassa']
        );

        $client = Client::create([
            'reseau_id' => $reseau->id,
            'agence_creation_id' => $agence->id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);

        $personne = new PersonnePhysique([
            'nom' => $nom,
            'prenoms' => $prenoms,
            'date_naissance' => '1985-09-03',
            'champs_manquants' => [],
        ]);
        $personne->client_id = $client->id;
        app(ServiceEmpreinte::class)->calculerPourPersonnePhysique($personne);
        $personne->save();

        return $client->fresh();
    }

    public function test_un_client_deja_enregistre_est_detecte_quand_la_liste_est_publiee_apres_lui(): void
    {
        $client = $this->clientExistant('AHOUANDJINOU', 'Rachidatou');

        // À cet instant, aucune liste ne le vise.
        $this->assertSame(0, Alerte::count());

        // La liste est publiée APRÈS : le membre ne repassera plus jamais par la
        // création de dossier, donc plus jamais par MoteurFiltrage::filtrer().
        EntreeListe::create([
            'source' => 'demo',
            'nom' => 'AWOUANDJINOU Rachidatou',
            'categorie' => 'Extrait fictif — test',
            'version_liste' => 'TEST-1',
            'importee_le' => now(),
        ]);

        $rapport = app(RefiltrageParc::class)->refiltrerTout();

        $this->assertSame(1, $rapport->dossiersTraites());
        $this->assertSame(1, $rapport->correspondancesTrouvees());
        $this->assertSame(1, Alerte::count());
        $this->assertSame($client->id, Alerte::first()->client_id);
    }

    public function test_une_translitteration_differente_est_bien_rapprochee(): void
    {
        $this->clientExistant('AHOUANDJINOU', 'Rachidatou');

        EntreeListe::create([
            'source' => 'demo',
            'nom' => 'AWOUANDJINOU Rachidatou',
            'categorie' => 'Extrait fictif — test',
            'version_liste' => 'TEST-1',
            'importee_le' => now(),
        ]);

        app(RefiltrageParc::class)->refiltrerTout();

        $alerte = Alerte::first();

        $this->assertNotNull($alerte);
        $this->assertGreaterThanOrEqual(0.70, $alerte->faits['score_similarite']);
        $this->assertLessThan(1.0, $alerte->faits['score_similarite'], 'Le rapprochement doit être flou, pas une égalité de chaînes.');
    }

    public function test_un_client_sans_rapport_n_est_pas_alerte(): void
    {
        $this->clientExistant('ADJOVI', 'Éric');

        EntreeListe::create([
            'source' => 'demo',
            'nom' => 'AWOUANDJINOU Rachidatou',
            'categorie' => 'Extrait fictif — test',
            'version_liste' => 'TEST-1',
            'importee_le' => now(),
        ]);

        $rapport = app(RefiltrageParc::class)->refiltrerTout();

        $this->assertSame(0, $rapport->correspondancesTrouvees());
        $this->assertSame(0, Alerte::count());
    }

    public function test_le_delai_reglementaire_est_mesure_et_signale_quand_il_est_depasse(): void
    {
        $this->clientExistant('AHOUANDJINOU', 'Rachidatou');

        EntreeListe::create([
            'source' => 'demo',
            'nom' => 'AWOUANDJINOU Rachidatou',
            'categorie' => 'Extrait fictif — test',
            'version_liste' => 'TEST-1',
            'importee_le' => now(),
        ]);

        $aTemps = app(RefiltrageParc::class)->refiltrerTout(now()->subHours(3));
        $this->assertTrue($aTemps->echeanceRespectee());
        $this->assertStringContainsString('échéance respectée', $aTemps->resume());

        $enRetard = app(RefiltrageParc::class)->refiltrerTout(now()->subHours(40));
        $this->assertFalse($enRetard->echeanceRespectee());
        $this->assertStringContainsString('ÉCHÉANCE DÉPASSÉE', $enRetard->resume());
    }

    public function test_le_refiltrage_est_idempotent_et_ne_duplique_pas_les_alertes(): void
    {
        $this->clientExistant('AHOUANDJINOU', 'Rachidatou');

        EntreeListe::create([
            'source' => 'demo',
            'nom' => 'AWOUANDJINOU Rachidatou',
            'categorie' => 'Extrait fictif — test',
            'version_liste' => 'TEST-1',
            'importee_le' => now(),
        ]);

        app(RefiltrageParc::class)->refiltrerTout();
        $apresPremier = Alerte::count();

        app(RefiltrageParc::class)->refiltrerTout();

        $this->assertSame($apresPremier, Alerte::count(), 'Un second passage ne doit pas re-alerter sur la même correspondance.');
    }

    public function test_les_empreintes_manquantes_des_entrees_de_liste_sont_recalculables(): void
    {
        EntreeListe::create([
            'source' => 'demo',
            'nom' => 'AWOUANDJINOU Rachidatou',
            'categorie' => 'Extrait fictif — test',
            'version_liste' => 'TEST-1',
            'importee_le' => now(),
        ]);

        // Simule une entrée arrivée par un chemin qui aurait contourné le hook.
        EntreeListe::query()->update(['empreinte_nom' => null]);
        $this->assertSame(1, EntreeListe::whereNull('empreinte_nom')->count());

        $corrigees = app(RefiltrageParc::class)->recalculerEmpreintesListes();

        $this->assertSame(1, $corrigees);
        $this->assertSame(0, EntreeListe::whereNull('empreinte_nom')->count());
    }
}
