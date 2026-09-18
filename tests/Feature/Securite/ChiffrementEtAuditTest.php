<?php

namespace Tests\Feature\Securite;

use App\Enums\NatureRelation;
use App\Enums\SourceCreation;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Client;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Services\Audit\Consignateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChiffrementEtAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_champs_identite_sont_chiffres_en_base(): void
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Centrale', 'code' => 'AC']);

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
            'telephone' => '90000000',
            'champs_manquants' => [],
        ]);

        $brut = DB::table('personnes_physiques')->where('id', $personne->id)->first();

        $this->assertNotSame('KPADONOU', $brut->nom);
        $this->assertStringNotContainsString('KPADONOU', $brut->nom);
        $this->assertStringStartsWith('v1:', $brut->nom);
        $this->assertNotEmpty($brut->nom_idx);

        $this->assertStringNotContainsString('90000000', $brut->telephone);
        $this->assertStringStartsWith('v1:', $brut->telephone);
        $this->assertNotEmpty($brut->telephone_idx);

        $recharge = PersonnePhysique::find($personne->id);
        $this->assertSame('KPADONOU', $recharge->nom);
        $this->assertSame('Fidèle', $recharge->prenoms);
        $this->assertSame('90000000', $recharge->telephone);
    }

    public function test_le_journal_audit_est_chaine_et_detecte_une_alteration(): void
    {
        Consignateur::enregistrer('agent', 1, 'connexion');
        Consignateur::enregistrer('agent', 1, 'consultation', 'client', 'abc-123');
        Consignateur::enregistrer('agent', 1, 'deconnexion');

        $this->assertNull(Consignateur::premiereRupture());

        DB::table('journal_audit')->where('id', 2)->update(['action' => 'falsifie']);

        $this->assertSame(2, Consignateur::premiereRupture());
    }

    public function test_le_journal_audit_est_immuable(): void
    {
        $ligne = Consignateur::enregistrer('agent', 1, 'connexion');

        $this->expectException(\RuntimeException::class);
        $ligne->update(['action' => 'autre']);
    }
}
