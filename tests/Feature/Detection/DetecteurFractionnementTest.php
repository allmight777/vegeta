<?php

namespace Tests\Feature\Detection;

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
use App\Models\DeclarationCentif;
use App\Models\Operation;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Services\Detection\DetecteurFractionnement;
use App\Services\Identite\ResolveurIdentite;
use Database\Seeders\Detection\ReglesDetectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Seuils du seeder : seuil_unitaire 5 000 000, seuil_cumul 15 000 000.
 * Un fractionnement = plusieurs dépôts CHACUN sous le seuil unitaire, dont la somme
 * dépasse le seuil de cumul.
 */
class DetecteurFractionnementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReglesDetectionSeeder::class);
    }

    private function creerClientAvecCompte(Agence $agence, string $nom, string $prenoms, ?string $dateNaissance = '1990-01-01', ?string $npi = null): Compte
    {
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        $personne = PersonnePhysique::create([
            'client_id' => $client->id,
            'nom' => $nom,
            'prenoms' => $prenoms,
            'date_naissance' => $dateNaissance,
            'revenus_mensuels_estimes' => 20000000, // plafond élevé : isole les règles testées ici
            'champs_manquants' => [],
        ]);

        if ($npi !== null) {
            $personne->definirNpi($npi);
            $personne->saveQuietly();
        }

        app(ResolveurIdentite::class)->rattacher($client->fresh('personnePhysique'));

        return Compte::create([
            'client_id' => $client->id,
            'agence_id' => $agence->id,
            'numero' => 'CPT-'.uniqid(),
            'statut' => StatutCompte::Actif,
        ]);
    }

    private function operation(Compte $compte, float $montant, Carbon $date, ModePaiement $mode = ModePaiement::Especes): Operation
    {
        $operation = Operation::create([
            'compte_id' => $compte->id,
            'agence_id' => $compte->agence_id,
            'type' => TypeOperation::Depot,
            'montant' => $montant,
            'mode_paiement' => $mode,
            'devise_code' => 'XOF',
            'effectuee_le' => $date,
            'canal' => 'guichet',
        ]);

        app(DetecteurFractionnement::class)->analyserApresOperation($operation->fresh(['compte']));

        return $operation;
    }

    public function test_fractionnement_guichet_declenche_une_alerte_sans_donnee_identite(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R1']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'A', 'code' => 'A1']);
        $compte = $this->creerClientAvecCompte($agence, 'KPADONOU', 'Fidèle');

        $maintenant = now();
        foreach ([30, 20, 10, 0] as $heures) {
            $this->operation($compte, 4800000, $maintenant->copy()->subHours($heures));
        }

        $alerte = Alerte::where('type', 'fractionnement_guichet')->first();
        $this->assertNotNull($alerte);
        $this->assertStringNotContainsString('KPADONOU', json_encode($alerte->faits));
        $this->assertStringNotContainsString('KPADONOU', $alerte->explication_texte);
    }

    public function test_un_gros_depot_unique_n_est_pas_un_fractionnement(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R2']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'A', 'code' => 'A2']);
        $compte = $this->creerClientAvecCompte($agence, 'KPADONOU', 'Fidèle');

        // 16 M en une fois : visible, déclarable, mais pas dissimulé.
        $this->operation($compte, 16000000, now());

        $this->assertSame(0, Alerte::where('type', 'fractionnement_guichet')->count());
    }

    public function test_fractionnement_guichet_ne_se_declenche_pas_sous_le_seuil_de_cumul(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R3']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'A', 'code' => 'A3']);
        $compte = $this->creerClientAvecCompte($agence, 'KPADONOU', 'Fidèle');

        $this->operation($compte, 200000, now());

        $this->assertSame(0, Alerte::where('type', 'fractionnement_guichet')->count());
    }

    public function test_fractionnement_multi_agences_rapproche_par_npi(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R4']);
        $dassa = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Dassa', 'code' => 'DASSA4']);
        $savalou = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Savalou', 'code' => 'SAVALOU4']);

        // Deux fiches distinctes, orthographes différentes, même NPI vérifié.
        $compteDassa = $this->creerClientAvecCompte($dassa, 'KPADONOU', 'Fidèle', '1990-05-12', '1234567890123');
        $compteSavalou = $this->creerClientAvecCompte($savalou, 'KPADENOU', 'Fidel', '1990-05-12', '1234567890123');

        $maintenant = now();
        $this->operation($compteDassa, 4800000, $maintenant->copy()->subDays(2));
        $this->operation($compteSavalou, 4900000, $maintenant->copy()->subDays(1));
        $this->operation($compteDassa, 4700000, $maintenant->copy()->subHours(6));
        $this->operation($compteSavalou, 4800000, $maintenant);

        $alerte = Alerte::where('type', 'fractionnement_multi_agences')->first();
        $this->assertNotNull($alerte);
        $this->assertSame('critique', $alerte->gravite->value);
        $this->assertSame(2, $alerte->faits['nb_agences']);
        $this->assertSame('npi', $alerte->faits['rapprochement']);
        $this->assertSame(2, $alerte->faits['fiches_rapprochees']);
    }

    public function test_les_virements_n_entrent_pas_dans_le_fractionnement(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R5']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'A', 'code' => 'A5']);
        $compte = $this->creerClientAvecCompte($agence, 'ZINSOU', 'Carole');

        $maintenant = now();
        foreach ([30, 20, 10, 0] as $heures) {
            $this->operation($compte, 4800000, $maintenant->copy()->subHours($heures), ModePaiement::Virement);
        }

        $this->assertSame(0, Alerte::where('type', 'fractionnement_guichet')->count());
    }

    public function test_compte_dormant_reactive_declenche_une_alerte_attention(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R6']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'A', 'code' => 'A6']);
        $compte = $this->creerClientAvecCompte($agence, 'DOSSOU', 'Marcelin');
        $compte->update(['derniere_operation_le' => now()->subMonths(14)]);

        $this->operation($compte, 500000, now());

        $alerte = Alerte::where('type', 'compte_dormant_reactive')->first();
        $this->assertNotNull($alerte);
        $this->assertSame('attention', $alerte->gravite->value);
    }

    public function test_seuil_mensuel_centif_cumule_toutes_les_fiches_de_la_personne(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R7']);
        $dantokpa = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Dantokpa', 'code' => 'DAN7']);
        $calavi = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Calavi', 'code' => 'CAL7']);

        $compteA = $this->creerClientAvecCompte($dantokpa, 'ADJOVI', 'Éric', '1985-02-02', '5555555555555');
        $compteB = $this->creerClientAvecCompte($calavi, 'ADJOVI', 'Éric', '1985-02-02', '5555555555555');

        $this->operation($compteA, 9000000, now()->subDays(3));
        $this->operation($compteB, 7000000, now());

        $this->assertSame(1, DeclarationCentif::count());
        $this->assertSame('a_preparer', DeclarationCentif::first()->statut->value);
        $this->assertSame(16000000.0, (float) DeclarationCentif::first()->montant_cumule);
    }
}
