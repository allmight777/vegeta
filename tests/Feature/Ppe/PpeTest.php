<?php

namespace Tests\Feature\Ppe;

use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Enums\StatutCompte;
use App\Enums\StatutPpe;
use App\Enums\TypeAlerte;
use App\Enums\TypeClient;
use App\Mail\AlerteConformiteMail;
use App\Mail\ListePpeMail;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\Compte;
use App\Models\DocumentPpe;
use App\Models\PartageListePpe;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PpeTest extends TestCase
{
    use RefreshDatabase;

    private function contexte(bool $ppe): array
    {
        $reseau = Reseau::create(['nom' => 'Réseau test', 'code' => 'RP']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence PPE', 'code' => 'AP']);
        $caissier = Agent::create(['agence_id' => $agence->id, 'nom' => 'Caissier', 'matricule' => 'CAI-P', 'mot_de_passe' => Hash::make('mot-de-passe'), 'role' => RoleAgent::Caissier]);
        $responsable = Agent::create(['agence_id' => $agence->id, 'nom' => 'Resp', 'matricule' => 'RESP-P', 'email' => 'resp@example.test', 'mot_de_passe' => Hash::make('mot-de-passe'), 'role' => RoleAgent::ResponsableAgence, 'actif' => true]);
        $client = Client::create([
            'reseau_id' => $reseau->id, 'agence_creation_id' => $agence->id, 'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte, 'source_creation' => SourceCreation::SaisieAgent,
            'ppe_declare' => $ppe, 'statut_ppe' => $ppe ? StatutPpe::PpeAVerifier : StatutPpe::NonPpe,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'DOSSOU', 'prenoms' => 'Marc', 'date_naissance' => '1975-05-05', 'piece_identite_numero' => 'P123', 'champs_manquants' => []]);
        $compte = Compte::create(['client_id' => $client->id, 'agence_id' => $agence->id, 'numero' => 'CPT-P', 'statut' => StatutCompte::Actif]);

        return compact('agence', 'caissier', 'responsable', 'client', 'compte');
    }

    public function test_un_depot_d_un_franc_par_une_ppe_alerte_le_responsable(): void
    {
        Mail::fake();
        $c = $this->contexte(true);

        $this->actingAs($c['caissier'], 'agent')->post(route('agent.operations.stocker'), [
            'compte_id' => $c['compte']->id, 'type' => 'depot', 'montant' => 1, 'mode_paiement' => 'especes',
        ]);

        $alerte = Alerte::where('type', TypeAlerte::DepotPpe->value)->firstOrFail();
        $this->assertSame($c['agence']->id, $alerte->agence_id);
        $this->assertStringNotContainsString('DOSSOU', $alerte->explication_texte);
        Mail::assertQueued(AlerteConformiteMail::class, fn ($m) => $m->hasTo('resp@example.test'));
    }

    public function test_un_depot_d_un_client_non_ppe_ne_declenche_pas_d_alerte_ppe(): void
    {
        Mail::fake();
        $c = $this->contexte(false);

        $this->actingAs($c['caissier'], 'agent')->post(route('agent.operations.stocker'), [
            'compte_id' => $c['compte']->id, 'type' => 'depot', 'montant' => 1, 'mode_paiement' => 'especes',
        ]);

        $this->assertSame(0, Alerte::where('type', TypeAlerte::DepotPpe->value)->count());
    }

    public function test_une_ppe_declaree_exige_une_piece_justificative(): void
    {
        $c = $this->contexte(false);

        $this->actingAs($c['caissier'], 'agent')->post(route('agent.clients.stocker'), [
            'type' => 'personne_physique', 'nature_relation' => 'titulaire_compte', 'ppe_declare' => '1',
            'nom' => 'AKPO', 'prenoms' => 'Eric', 'date_naissance' => '1980-01-01', 'piece_identite_numero' => 'X1',
        ])->assertSessionHasErrors('documents_ppe');
    }

    public function test_la_creation_d_une_ppe_enregistre_les_pieces_et_le_statut(): void
    {
        Storage::fake('local');
        Mail::fake();
        $c = $this->contexte(false);

        $this->actingAs($c['caissier'], 'agent')->post(route('agent.clients.stocker'), [
            'type' => 'personne_physique', 'nature_relation' => 'titulaire_compte', 'ppe_declare' => '1',
            'nom' => 'AKPO', 'prenoms' => 'Eric', 'date_naissance' => '1980-01-01', 'piece_identite_numero' => 'X1',
            'telephone' => '0197000000', 'email' => 'eric@example.test', 'piece_identite_type' => 'cni', 'npi' => '1234567890',
            'documents_ppe' => [UploadedFile::fake()->create('decret.pdf', 100, 'application/pdf')],
        ])->assertSessionHasNoErrors();

        $nouveau = Client::where('id', '!=', $c['client']->id)->firstOrFail();
        $this->assertTrue($nouveau->ppe_declare);
        $this->assertSame(StatutPpe::PpeAVerifier, $nouveau->statut_ppe);
        $this->assertSame(1, DocumentPpe::where('client_id', $nouveau->id)->count());
    }

    public function test_le_responsable_envoie_la_liste_des_ppe_avec_un_code_d_acces(): void
    {
        Storage::fake('local');
        Mail::fake();
        $c = $this->contexte(true);

        $this->actingAs($c['responsable'], 'agent')->post(route('responsable.clients.ppe.envoyer'), [
            'email' => 'destinataire@example.test', 'code_acces' => 'Code-Solide-2026!', 'code_acces_confirmation' => 'Code-Solide-2026!',
        ])->assertSessionHasNoErrors();

        $partage = PartageListePpe::firstOrFail();
        Storage::disk('local')->assertExists($partage->fichier_pdf_path);
        Mail::assertSent(ListePpeMail::class, fn ($m) => $m->hasTo('destinataire@example.test'));

        $this->post(route('ppe.partages.telecharger', $partage->jeton_partage), ['code_acces' => 'faux'])->assertSessionHasErrors('code_acces');
        $this->post(route('ppe.partages.telecharger', $partage->jeton_partage), ['code_acces' => 'Code-Solide-2026!'])->assertOk();
    }

    public function test_le_responsable_voit_exporte_et_detaille_ses_ppe(): void
    {
        $c = $this->contexte(true);
        $r = $this->actingAs($c['responsable'], 'agent');

        $r->get(route('responsable.ppe.index'))->assertOk()->assertSee('DOSSOU');
        $r->get(route('responsable.ppe.index', ['du' => today()->addDay()->toDateString()]))->assertOk()->assertDontSee('DOSSOU');
        $r->get(route('responsable.ppe.exporter', 'xlsx'))->assertOk();
        $r->get(route('responsable.ppe.exporter', 'pdf'))->assertOk();
        $r->get(route('responsable.ppe.detail', $c['client']))->assertOk()->assertSee('Aucune opération');
    }
}
