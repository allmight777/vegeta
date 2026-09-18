<?php

namespace Tests\Feature\Identite;

use App\Enums\ModePaiement;
use App\Enums\NatureRelation;
use App\Enums\SourceCreation;
use App\Enums\StatutCompte;
use App\Enums\TypeClient;
use App\Enums\TypeOperation;
use App\Models\Agence;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\Compte;
use App\Models\CumulJournalier;
use App\Models\Operation;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Services\Detection\DetecteurFractionnement;
use App\Services\Identite\ResolveurIdentite;
use Database\Seeders\Detection\ReglesDetectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlafondQuotidienTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReglesDetectionSeeder::class);
    }

    /** Même personne (même NPI), deux fiches, deux agences : le cas des bases non centralisées. */
    private function personneAvecDeuxComptes(): array
    {
        $reseau = Reseau::create(['nom' => 'FECECAM démo', 'code' => 'DEMO']);
        $dantokpa = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Dantokpa', 'code' => 'DAN']);
        $calavi = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Calavi', 'code' => 'CAL']);

        $comptes = [];
        foreach ([$dantokpa, $calavi] as $index => $agence) {
            $client = Client::create([
                'reseau_id' => $reseau->id,
                'type' => TypeClient::PersonnePhysique,
                'nature_relation' => NatureRelation::TitulaireCompte,
                'source_creation' => SourceCreation::SaisieAgent,
            ]);

            $personne = PersonnePhysique::create([
                'client_id' => $client->id,
                'nom' => 'KPADONOU',
                'prenoms' => 'Fidèle',
                'date_naissance' => '1990-05-12',
                'revenus_mensuels_estimes' => 1000000, // plafond = 1 500 000
                'champs_manquants' => [],
            ]);
            $personne->definirNpi('1234567890123');
            $personne->saveQuietly();

            app(ResolveurIdentite::class)->rattacher($client->fresh('personnePhysique'));

            $comptes[] = Compte::create([
                'client_id' => $client->id,
                'agence_id' => $agence->id,
                'numero' => 'CPT-'.$index,
                'statut' => StatutCompte::Actif,
            ]);
        }

        return $comptes;
    }

    private function deposer(Compte $compte, float $montant, ModePaiement $mode = ModePaiement::Especes): Operation
    {
        $operation = Operation::create([
            'compte_id' => $compte->id,
            'agence_id' => $compte->agence_id,
            'type' => TypeOperation::Depot,
            'montant' => $montant,
            'mode_paiement' => $mode,
            'devise_code' => 'XOF',
            'effectuee_le' => now(),
            'canal' => 'guichet',
        ]);

        app(DetecteurFractionnement::class)->analyserApresOperation($operation->fresh(['compte']));

        return $operation;
    }

    public function test_le_cumul_du_jour_agrege_les_comptes_de_toutes_les_agences(): void
    {
        [$dantokpa, $calavi] = $this->personneAvecDeuxComptes();

        $this->deposer($dantokpa, 400000);
        $this->deposer($calavi, 300000);

        $cumul = CumulJournalier::where('mode_paiement', 'especes')->first();

        $this->assertNotNull($cumul);
        $this->assertSame(700000.0, $cumul->totalRetenu());
        $this->assertSame(2, $cumul->nb_comptes);
        $this->assertSame(2, $cumul->nb_agences);
    }

    public function test_le_depassement_du_plafond_leve_une_alerte_critique_en_temps_reel(): void
    {
        [$dantokpa, $calavi] = $this->personneAvecDeuxComptes();

        $this->deposer($dantokpa, 800000);
        $this->deposer($calavi, 800000); // total 1 600 000 > plafond 1 500 000

        $alerte = Alerte::where('type', 'plafond_quotidien_depasse')->first();

        $this->assertNotNull($alerte);
        $this->assertSame('critique', $alerte->gravite->value);
        $this->assertSame(2, $alerte->faits['nb_agences']);
        $this->assertSame('npi', $alerte->faits['rapprochement']);
        $this->assertStringContainsString('2 agences', $alerte->explication_texte);
        // Non-divulgation : aucun nom dans les faits ni dans la phrase (art. 63).
        $this->assertStringNotContainsString('KPADONOU', json_encode($alerte->faits));
        $this->assertStringNotContainsString('KPADONOU', $alerte->explication_texte);
    }

    public function test_une_approche_du_plafond_leve_une_alerte_attention(): void
    {
        [$dantokpa] = $this->personneAvecDeuxComptes();

        $this->deposer($dantokpa, 1300000); // 86 % du plafond

        $this->assertSame(1, Alerte::where('type', 'plafond_quotidien_approche')->count());
        $this->assertSame(0, Alerte::where('type', 'plafond_quotidien_depasse')->count());
    }

    public function test_un_virement_n_entre_pas_dans_le_plafond_especes(): void
    {
        [$dantokpa] = $this->personneAvecDeuxComptes();

        $this->deposer($dantokpa, 5000000, ModePaiement::Virement);

        $this->assertSame(0, Alerte::whereIn('type', ['plafond_quotidien_approche', 'plafond_quotidien_depasse'])->count());
    }

    public function test_une_seule_alerte_par_jour_et_par_personne(): void
    {
        [$dantokpa, $calavi] = $this->personneAvecDeuxComptes();

        $this->deposer($dantokpa, 900000);
        $this->deposer($calavi, 900000);
        $this->deposer($dantokpa, 900000);

        $this->assertSame(1, Alerte::where('type', 'plafond_quotidien_depasse')->count());
    }
}
