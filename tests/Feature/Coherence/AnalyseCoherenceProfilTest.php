<?php

namespace Tests\Feature\Coherence;

use App\Enums\ModePaiement;
use App\Enums\NatureRelation;
use App\Enums\SourceCreation;
use App\Enums\StatutCompte;
use App\Enums\TypeAlerte;
use App\Enums\TypeClient;
use App\Enums\TypeOperation;
use App\Models\Agence;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Operation;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Services\Coherence\AnalyseurCoherenceProfil;
use App\Services\Coherence\NarrateurFaisceau;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyseCoherenceProfilTest extends TestCase
{
    use RefreshDatabase;

    private function compte(string $profession, float $revenus): Compte
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

        PersonnePhysique::create([
            'client_id' => $client->id,
            'nom' => 'AGOSSOU',
            'prenoms' => 'Rémi',
            'date_naissance' => '1990-06-12',
            'profession' => $profession,
            'revenus_mensuels_estimes' => $revenus,
            'champs_manquants' => [],
        ]);

        return Compte::create([
            'client_id' => $client->id,
            'agence_id' => $agence->id,
            'numero' => 'CPT-TEST-'.uniqid(),
            'statut' => StatutCompte::Actif,
        ]);
    }

    private function operation(Compte $compte, TypeOperation $type, float $montant, Carbon $date, ModePaiement $mode): void
    {
        Operation::create([
            'compte_id' => $compte->id,
            'agence_id' => $compte->agence_id,
            'type' => $type,
            'montant' => $montant,
            'mode_paiement' => $mode,
            'devise_code' => 'XOF',
            'effectuee_le' => $date,
            'canal' => 'guichet',
        ]);
    }

    /** Douze allers-retours mobile money sur un profil « cultivateur » à 45 000 XOF/mois. */
    private function comportementDeRelais(Compte $compte): void
    {
        for ($i = 0; $i < 12; $i++) {
            $arrivee = now()->subDays(26 - ($i * 2))->setTime(21, 30);
            $this->operation($compte, TypeOperation::Depot, 190000, $arrivee, ModePaiement::MobileMoney);
            $this->operation($compte, TypeOperation::Retrait, 175000, $arrivee->copy()->addHours(19), ModePaiement::MobileMoney);
        }
    }

    public function test_les_trois_indicateurs_se_declenchent_et_forment_un_faisceau_critique(): void
    {
        $compte = $this->compte('Cultivateur', 45000);
        $this->comportementDeRelais($compte);

        $faisceau = app(AnalyseurCoherenceProfil::class)->analyser($compte->client->fresh());

        $this->assertTrue($faisceau->estConstitue());
        $this->assertSame(3, $faisceau->nombreConstats());
        $this->assertSame('critique', $faisceau->gravite()->value);

        $codes = array_map(fn ($c) => $c->code, $faisceau->constats());
        $this->assertContains('ecart_flux_revenus', $codes);
        $this->assertContains('compte_de_passage', $codes);
        $this->assertContains('incoherence_activite_canal', $codes);
    }

    public function test_le_signalement_va_au_responsable_avec_les_faits_chiffres(): void
    {
        $compte = $this->compte('Cultivateur', 45000);
        $this->comportementDeRelais($compte);

        $alerte = app(AnalyseurCoherenceProfil::class)->signaler($compte->client->fresh());

        $this->assertNotNull($alerte);
        $this->assertSame(TypeAlerte::IncoherenceProfil, $alerte->type);
        $this->assertSame($compte->client->id, $alerte->client_id);

        // Chaque constat doit être refaisable à la main par un contrôleur.
        $this->assertSame(3, $alerte->faits['nombre_constats']);
        $this->assertArrayHasKey('constats', $alerte->faits);

        $ecart = collect($alerte->faits['constats'])->firstWhere('code', 'ecart_flux_revenus');
        $this->assertEquals(2280000, $ecart['fait']['cumul_depots']); // JSON : le .0 est perdu au round-trip
        $this->assertEquals(45000, $ecart['fait']['revenus_mensuels_declares']);
        $this->assertGreaterThan(50, $ecart['fait']['ratio']);
    }

    /**
     * LE test qui compte : un membre parfaitement normal ne doit JAMAIS être
     * signalé. Un dispositif qui crie au loup vide la file du responsable de
     * tout sens et produit moins de conformité, pas plus.
     */
    public function test_un_client_coherent_n_est_pas_signale(): void
    {
        $compte = $this->compte('Cultivateur', 300000);

        // Un vrai cultivateur : dépôts d'espèces modestes, gardés sur le compte.
        for ($i = 0; $i < 6; $i++) {
            $this->operation($compte, TypeOperation::Depot, 80000, now()->subDays(25 - ($i * 4)), ModePaiement::Especes);
        }

        $faisceau = app(AnalyseurCoherenceProfil::class)->analyser($compte->client->fresh());

        $this->assertFalse($faisceau->estConstitue());
        $this->assertNull(app(AnalyseurCoherenceProfil::class)->signaler($compte->client->fresh()));
        $this->assertSame(0, Alerte::where('type', TypeAlerte::IncoherenceProfil)->count());
    }

    /**
     * Un indice isolé est du bruit : un commerçant qui dépose beaucoup après une
     * bonne saison n'est pas un blanchisseur.
     */
    public function test_un_seul_constat_ne_suffit_pas_a_signaler(): void
    {
        $compte = $this->compte('Commerçante', 100000);

        // Écart de flux seul : gros dépôts en espèces, conservés, canal attendu.
        for ($i = 0; $i < 4; $i++) {
            $this->operation($compte, TypeOperation::Depot, 400000, now()->subDays(20 - ($i * 4)), ModePaiement::Especes);
        }

        $faisceau = app(AnalyseurCoherenceProfil::class)->analyser($compte->client->fresh());

        $this->assertSame(1, $faisceau->nombreConstats());
        $this->assertFalse($faisceau->estConstitue(), 'Un indice isolé ne doit jamais déclencher un signalement.');
    }

    public function test_le_signalement_ne_se_duplique_pas_a_chaque_campagne(): void
    {
        $compte = $this->compte('Cultivateur', 45000);
        $this->comportementDeRelais($compte);

        $analyseur = app(AnalyseurCoherenceProfil::class);
        $analyseur->signaler($compte->client->fresh());
        $analyseur->signaler($compte->client->fresh());
        $analyseur->signaler($compte->client->fresh());

        $this->assertSame(1, Alerte::where('type', TypeAlerte::IncoherenceProfil)->count());
    }

    public function test_sans_revenu_declare_l_ecart_de_flux_ne_se_prononce_pas(): void
    {
        $compte = $this->compte('Cultivateur', 0);
        $this->comportementDeRelais($compte);

        $faisceau = app(AnalyseurCoherenceProfil::class)->analyser($compte->client->fresh());

        $codes = array_map(fn ($c) => $c->code, $faisceau->constats());
        $this->assertNotContains('ecart_flux_revenus', $codes, 'Sans revenu déclaré, le ratio n\'a pas de sens : c\'est un défaut de complétude KYC, pas un soupçon.');
    }

    public function test_la_remediation_kyc_propose_des_actions_concretes(): void
    {
        $compte = $this->compte('Cultivateur', 45000);
        $this->comportementDeRelais($compte);

        $faisceau = app(AnalyseurCoherenceProfil::class)->analyser($compte->client->fresh());
        $actions = app(NarrateurFaisceau::class)->actionsRemediation($faisceau);

        $this->assertNotEmpty($actions);
        $this->assertSame($actions, array_unique($actions), 'Aucune action ne doit être proposée deux fois.');
    }
}
