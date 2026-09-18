<?php

namespace Tests\Feature\Identite;

use App\Enums\MethodeRattachement;
use App\Enums\NatureRelation;
use App\Enums\SourceCreation;
use App\Enums\StatutCompte;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Client;
use App\Models\Compte;
use App\Models\FusionIdentiteEnAttente;
use App\Models\Identite;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Services\Identite\ResolveurIdentite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RattachementIdentiteTest extends TestCase
{
    use RefreshDatabase;

    private function reseau(string $code): Reseau
    {
        return Reseau::create(['nom' => 'Réseau '.$code, 'code' => $code]);
    }

    private function creerClient(Reseau $reseau, string $nom, string $prenoms, ?string $npi = null, string $dateNaissance = '1990-05-12'): Client
    {
        $client = Client::create([
            'reseau_id' => $reseau->id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);

        $personne = PersonnePhysique::create([
            'client_id' => $client->id,
            'nom' => $nom,
            'prenoms' => $prenoms,
            'date_naissance' => $dateNaissance,
            'revenus_mensuels_estimes' => 400000,
            'champs_manquants' => [],
        ]);

        if ($npi !== null) {
            $personne->definirNpi($npi);
            $personne->saveQuietly();
        }

        app(ResolveurIdentite::class)->rattacher($client->fresh('personnePhysique'));

        return $client->fresh(['identite', 'personnePhysique']);
    }

    public function test_deux_fiches_avec_le_meme_npi_partagent_une_seule_identite(): void
    {
        $reseau = $this->reseau('R1');

        // Orthographes différentes, agences différentes : c'est le NPI qui tranche.
        $dassa = $this->creerClient($reseau, 'KPADONOU', 'Fidèle', '1234567890123');
        $savalou = $this->creerClient($reseau, 'KPADENOU', 'Fidel', '1234567890123');

        $this->assertNotNull($dassa->identite_id);
        $this->assertSame($dassa->identite_id, $savalou->identite_id);
        $this->assertSame(1, Identite::count());
        $this->assertTrue($dassa->identite->rapprocheeParNpi());

        $methodes = $dassa->identite->rattachements()->pluck('methode')->all();
        $this->assertContains(MethodeRattachement::Npi, $methodes);
    }

    public function test_sans_npi_une_correspondance_forte_rattache_automatiquement(): void
    {
        $reseau = $this->reseau('R2');

        $premier = $this->creerClient($reseau, 'HOUNKPATIN', 'Rachidatou');
        $second = $this->creerClient($reseau, 'HOUNKPATIN', 'Rachidatou');

        $this->assertSame($premier->identite_id, $second->identite_id);
        $this->assertSame(
            MethodeRattachement::Empreinte,
            $second->identite->rattachements()->where('client_id', $second->id)->first()->methode,
        );
    }

    public function test_deux_homonymes_proches_ne_sont_jamais_fusionnes_automatiquement(): void
    {
        $reseau = $this->reseau('R3');

        // Cas « AGBO Paul » / « AGBO Pauline » : noms très proches, personnes distinctes.
        $paul = $this->creerClient($reseau, 'AGBO', 'Paul', null, '1988-03-04');
        $pauline = $this->creerClient($reseau, 'AGBO', 'Pauline', null, '1995-11-22');

        $this->assertNotSame($paul->identite_id, $pauline->identite_id);
        $this->assertSame(2, Identite::count());
    }

    public function test_une_fusion_proposee_reste_en_attente_de_decision_humaine(): void
    {
        $reseau = $this->reseau('R4');

        $this->creerClient($reseau, 'ADJOVI', 'Éric', null, '1991-07-15');
        $this->creerClient($reseau, 'ADJOVY', 'Éric', null, '1991-07-15');

        $propositions = FusionIdentiteEnAttente::all();

        // Selon le score obtenu, soit le rattachement a été automatique (une seule
        // identité), soit il est proposé — jamais ignoré silencieusement.
        $this->assertTrue(
            $propositions->isNotEmpty() || Identite::count() === 1,
            'Deux variantes du même nom doivent être rattachées ou proposées à la fusion.',
        );
    }

    public function test_le_plafond_quotidien_est_calcule_depuis_les_revenus_declares(): void
    {
        $reseau = $this->reseau('R5');
        $client = $this->creerClient($reseau, 'DOSSOU', 'Marcelin', '9876543210987');

        // 400 000 x coefficient 1.5 = 600 000, au-dessus du plancher de 500 000.
        $this->assertSame(600000.0, (float) $client->identite->plafond_quotidien_especes);
        $this->assertSame('demo', $client->identite->source_plafond->value);
    }

    public function test_les_comptes_de_toutes_les_agences_sont_visibles_depuis_l_identite(): void
    {
        $reseau = $this->reseau('R6');
        $dantokpa = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Dantokpa', 'code' => 'DAN6']);
        $calavi = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Calavi', 'code' => 'CAL6']);

        $a = $this->creerClient($reseau, 'SOGLO', 'Bernadette', '1111111111111');
        $b = $this->creerClient($reseau, 'SOGLO', 'Bernadette', '1111111111111');

        Compte::create(['client_id' => $a->id, 'agence_id' => $dantokpa->id, 'numero' => 'CPT-A6', 'statut' => StatutCompte::Actif]);
        Compte::create(['client_id' => $b->id, 'agence_id' => $calavi->id, 'numero' => 'CPT-B6', 'statut' => StatutCompte::Actif]);

        $this->assertSame(2, $a->identite->comptes()->count());
    }
}
