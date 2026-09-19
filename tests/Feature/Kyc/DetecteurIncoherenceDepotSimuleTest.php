<?php

namespace Tests\Feature\Kyc;

use App\Enums\NatureRelation;
use App\Enums\OperateurMobileMonnaie;
use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Enums\SourceValeur;
use App\Enums\TypeAlerte;
use App\Enums\TypeClient;
use App\Mail\AlerteConformiteMail;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\CompteMobileMonnaieSimule;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Services\Kyc\DetecteurIncoherenceDepotSimule;
use App\Services\Securite\IndexAveugle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DetecteurIncoherenceDepotSimuleTest extends TestCase
{
    use RefreshDatabase;

    private function compteSimule(string $telephone, string $nomTitulaire): CompteMobileMonnaieSimule
    {
        return CompteMobileMonnaieSimule::create([
            'telephone' => $telephone,
            'telephone_idx' => app(IndexAveugle::class)->calculer($telephone, 'telephone'),
            'operateur' => OperateurMobileMonnaie::Mtn,
            'nom_titulaire' => $nomTitulaire,
            'source' => SourceValeur::Demo,
        ]);
    }

    private function clientAvecTelephone(Agence $agence, string $telephone, string $nom, string $prenoms): Client
    {
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'agence_creation_id' => $agence->id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);

        PersonnePhysique::create([
            'client_id' => $client->id,
            'nom' => $nom,
            'prenoms' => $prenoms,
            'telephone' => $telephone,
            'champs_manquants' => [],
        ]);

        return $client->fresh(['personnePhysique']);
    }

    public function test_un_ecart_de_nom_leve_une_alerte_et_notifie_les_responsables_de_l_agence(): void
    {
        Mail::fake();

        $this->compteSimule('90000001', 'KPADONOU Fidèle');

        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
        $autreAgence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Autre Agence', 'code' => 'AA']);

        $responsable = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Responsable Test',
            'matricule' => 'RESP-1',
            'email' => 'responsable@example.test',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::ResponsableAgence,
        ]);

        $caissier = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Caissier Test',
            'matricule' => 'CAISS-1',
            'email' => 'caissier@example.test',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Caissier,
        ]);

        $responsableAutreAgence = Agent::create([
            'agence_id' => $autreAgence->id,
            'nom' => 'Responsable Autre Agence',
            'matricule' => 'RESP-2',
            'email' => 'autre@example.test',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::ResponsableAgence,
        ]);

        $client = $this->clientAvecTelephone($agence, '90000001', 'DOSSOU', 'Paul');

        app(DetecteurIncoherenceDepotSimule::class)->verifier($client, $caissier);

        $alerte = Alerte::where('client_id', $client->id)->where('type', TypeAlerte::IncoherenceDepotSimule)->first();
        $this->assertNotNull($alerte);
        $this->assertSame(['operateur', 'score_similarite'], array_keys($alerte->faits));
        $this->assertStringNotContainsString('DOSSOU', json_encode($alerte->faits));
        $this->assertStringNotContainsString('KPADONOU', json_encode($alerte->faits));
        $this->assertStringNotContainsString('DOSSOU', $alerte->explication_texte);
        $this->assertStringNotContainsString('KPADONOU', $alerte->explication_texte);

        Mail::assertQueued(AlerteConformiteMail::class, fn ($mail) => $mail->hasTo('responsable@example.test'));
        Mail::assertNotQueued(AlerteConformiteMail::class, fn ($mail) => $mail->hasTo('caissier@example.test'));
        Mail::assertNotQueued(AlerteConformiteMail::class, fn ($mail) => $mail->hasTo('autre@example.test'));
    }

    public function test_un_nom_correspondant_ne_leve_aucune_alerte(): void
    {
        Mail::fake();

        $this->compteSimule('90000002', 'KPADONOU Fidèle');

        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT2']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT2']);
        $client = $this->clientAvecTelephone($agence, '90000002', 'KPADONOU', 'Fidèle');

        app(DetecteurIncoherenceDepotSimule::class)->verifier($client, null);

        $this->assertDatabaseCount('alertes', 0);
        Mail::assertNothingQueued();
    }

    public function test_un_numero_absent_de_l_annuaire_simule_ne_leve_aucune_alerte(): void
    {
        Mail::fake();

        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT3']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT3']);
        $client = $this->clientAvecTelephone($agence, '90009999', 'DOSSOU', 'Paul');

        app(DetecteurIncoherenceDepotSimule::class)->verifier($client, null);

        $this->assertDatabaseCount('alertes', 0);
        Mail::assertNothingQueued();
    }

    /**
     * Bout en bout via la vraie route HTTP de création (formulaire agent), pas un appel
     * direct au service : vérifie que le câblage réel dans CreateurClient::creer() (pas
     * seulement le service testé isolément ci-dessus) déclenche l'alerte et le mail, sans
     * jamais empêcher la création du client (« l'outil recommande, l'humain décide »).
     */
    public function test_le_parcours_complet_de_creation_via_le_formulaire_leve_l_alerte(): void
    {
        Mail::fake();

        $this->compteSimule('90000001', 'KPADONOU Fidèle');

        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT4']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT4']);

        Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Responsable Test',
            'matricule' => 'RESP-4',
            'email' => 'responsable4@example.test',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::ResponsableAgence,
        ]);

        $caissier = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Caissier Test',
            'matricule' => 'CAISS-4',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Caissier,
        ]);

        $reponse = $this->actingAs($caissier, 'agent')->post(route('agent.clients.stocker'), [
            'type' => 'personne_physique',
            'nature_relation' => 'titulaire_compte',
            'nom' => 'DOSSOU',
            'prenoms' => 'Paul',
            'email' => 'client@exemple.test',
            'npi' => '1234567890',
            'telephone' => '90000001',
            'date_naissance' => '1990-01-01',
            'piece_identite_numero' => 'CIP-99',
            'piece_identite_type' => 'cni',
        ]);

        $reponse->assertRedirect();

        $client = Client::first();
        $this->assertNotNull($client, 'Le client doit être créé même en cas d\'écart de nom.');

        $alerte = Alerte::where('client_id', $client->id)->where('type', TypeAlerte::IncoherenceDepotSimule)->first();
        $this->assertNotNull($alerte, 'L\'alerte doit être levée par le vrai parcours de création, pas seulement par un appel direct au service.');

        Mail::assertQueued(AlerteConformiteMail::class, fn ($mail) => $mail->hasTo('responsable4@example.test'));
    }
}
