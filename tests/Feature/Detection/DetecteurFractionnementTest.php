<?php

namespace Tests\Feature\Detection;

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
use Database\Seeders\Detection\ReglesDetectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DetecteurFractionnementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReglesDetectionSeeder::class);
    }

    private function creerClientAvecCompte(Agence $agence, string $nom, string $prenoms, ?string $dateNaissance = '1990-01-01'): Compte
    {
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create([
            'client_id' => $client->id,
            'nom' => $nom,
            'prenoms' => $prenoms,
            'date_naissance' => $dateNaissance,
            'champs_manquants' => [],
        ]);

        return Compte::create([
            'client_id' => $client->id,
            'agence_id' => $agence->id,
            'numero' => 'CPT-'.uniqid(),
            'statut' => StatutCompte::Actif,
        ]);
    }

    private function operation(Compte $compte, float $montant, Carbon $date): Operation
    {
        return Operation::create([
            'compte_id' => $compte->id,
            'agence_id' => $compte->agence_id,
            'type' => TypeOperation::Depot,
            'montant' => $montant,
            'devise_code' => 'XOF',
            'effectuee_le' => $date,
            'canal' => 'guichet',
        ]);
    }

    public function test_fractionnement_guichet_declenche_une_alerte_sans_donnee_identite(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R1']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'A', 'code' => 'A1']);
        $compte = $this->creerClientAvecCompte($agence, 'KPADONOU', 'Fidèle');

        $maintenant = now();
        $op1 = $this->operation($compte, 600000, $maintenant->copy()->subHours(10));
        app(DetecteurFractionnement::class)->analyserApresOperation($op1->fresh(['compte']));

        $op2 = $this->operation($compte, 600000, $maintenant);
        app(DetecteurFractionnement::class)->analyserApresOperation($op2->fresh(['compte']));

        $alerte = Alerte::where('type', 'fractionnement_guichet')->first();
        $this->assertNotNull($alerte);
        $this->assertStringNotContainsString('KPADONOU', json_encode($alerte->faits));
        $this->assertStringNotContainsString('KPADONOU', $alerte->explication_texte);
    }

    public function test_fractionnement_guichet_ne_se_declenche_pas_sous_le_seuil(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R2']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'A', 'code' => 'A2']);
        $compte = $this->creerClientAvecCompte($agence, 'KPADONOU', 'Fidèle');

        $op = $this->operation($compte, 200000, now());
        app(DetecteurFractionnement::class)->analyserApresOperation($op->fresh(['compte']));

        $this->assertSame(0, Alerte::where('type', 'fractionnement_guichet')->count());
    }

    public function test_fractionnement_multi_agences_scenario_a(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R3']);
        $dassa = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Dassa', 'code' => 'DASSA3']);
        $savalou = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Savalou', 'code' => 'SAVALOU3']);

        $compteDassa = $this->creerClientAvecCompte($dassa, 'KPADONOU', 'Fidèle', '1990-05-12');
        $compteSavalou = $this->creerClientAvecCompte($savalou, 'KPADENOU', 'Fidel', '1990-05-12');

        $maintenant = now();
        $op1 = $this->operation($compteDassa, 900000, $maintenant->copy()->subDays(2));
        app(DetecteurFractionnement::class)->analyserApresOperation($op1->fresh(['compte']));

        $op2 = $this->operation($compteSavalou, 950000, $maintenant);
        app(DetecteurFractionnement::class)->analyserApresOperation($op2->fresh(['compte']));

        $alerte = Alerte::where('type', 'fractionnement_multi_agences')->first();
        $this->assertNotNull($alerte);
        $this->assertSame('critique', $alerte->gravite->value);
        $this->assertSame(2, $alerte->faits['nb_agences']);
    }

    public function test_compte_dormant_reactive_declenche_une_alerte_attention(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R4']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'A', 'code' => 'A4']);
        $compte = $this->creerClientAvecCompte($agence, 'DOSSOU', 'Marcelin');
        $compte->update(['derniere_operation_le' => now()->subMonths(14)]);

        $op = $this->operation($compte, 500000, now());
        app(DetecteurFractionnement::class)->analyserApresOperation($op->fresh(['compte']));

        $alerte = Alerte::where('type', 'compte_dormant_reactive')->first();
        $this->assertNotNull($alerte);
        $this->assertSame('attention', $alerte->gravite->value);
    }

    public function test_seuil_mensuel_centif_cree_une_declaration(): void
    {
        $reseau = Reseau::create(['nom' => 'R', 'code' => 'R5']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'A', 'code' => 'A5']);
        $compte = $this->creerClientAvecCompte($agence, 'ADJOVI', 'Éric');

        $op = $this->operation($compte, 16000000, now());
        app(DetecteurFractionnement::class)->analyserApresOperation($op->fresh(['compte']));

        $this->assertSame(1, DeclarationCentif::count());
        $this->assertSame('a_preparer', DeclarationCentif::first()->statut->value);
    }
}
