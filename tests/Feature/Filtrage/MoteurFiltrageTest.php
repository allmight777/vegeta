<?php

namespace Tests\Feature\Filtrage;

use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\RoleSignataire;
use App\Enums\SourceCreation;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\EntreeListe;
use App\Models\PersonneMorale;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Models\Signataire;
use App\Services\Filtrage\MoteurFiltrage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MoteurFiltrageTest extends TestCase
{
    use RefreshDatabase;

    private function agence(): Agence
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);

        return Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
    }

    public function test_un_client_correspondant_a_une_entree_de_liste_genere_une_alerte(): void
    {
        EntreeListe::create(['source' => 'demo', 'nom' => 'AHOUANDJINOU Rachidatou', 'categorie' => 'Test']);

        $agence = $this->agence();
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'AHOUANDJINOU', 'prenoms' => 'Rachidatou', 'champs_manquants' => []]);

        $resultats = app(MoteurFiltrage::class)->filtrer($client->fresh());

        $this->assertCount(1, $resultats);
        $this->assertSame(1, Alerte::where('client_id', $client->id)->count());
        $alerte = Alerte::where('client_id', $client->id)->first();
        $this->assertSame('critique', $alerte->gravite->value);
        $this->assertStringNotContainsString('AHOUANDJINOU', $alerte->explication_texte);
    }

    public function test_un_nom_sans_rapport_ne_genere_aucune_correspondance(): void
    {
        EntreeListe::create(['source' => 'demo', 'nom' => 'AHOUANDJINOU Rachidatou', 'categorie' => 'Test']);

        $agence = $this->agence();
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'KPADONOU', 'prenoms' => 'Fidèle', 'champs_manquants' => []]);

        $resultats = app(MoteurFiltrage::class)->filtrer($client->fresh());

        $this->assertCount(0, $resultats);
        $this->assertSame(0, Alerte::where('client_id', $client->id)->count());
    }

    public function test_un_signataire_correspondant_est_marque_a_verifier_et_bloque_la_fiche(): void
    {
        EntreeListe::create(['source' => 'ppe_benin', 'nom' => 'ALIDOU Bertin', 'categorie' => 'Test']);

        $agence = $this->agence();
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonneMorale,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        $morale = PersonneMorale::create(['client_id' => $client->id, 'raison_sociale' => 'SARL DEMO', 'champs_manquants' => []]);
        $signataire = Signataire::create([
            'personne_morale_id' => $morale->id,
            'nom' => 'ALIDOU Bertin',
            'role' => RoleSignataire::Signataire,
            'statut_filtrage' => 'a_verifier',
        ]);

        app(MoteurFiltrage::class)->filtrer($signataire->fresh());

        $this->assertSame('a_verifier', $signataire->fresh()->statut_filtrage->value);

        $agent = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent',
            'matricule' => 'GUI-1',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Caissier,
        ]);
        $this->assertFalse($agent->can('peutValiderOperation', $client->fresh()));
    }

    public function test_un_signataire_sans_correspondance_est_ecarte_automatiquement(): void
    {
        $agence = $this->agence();
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonneMorale,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        $morale = PersonneMorale::create(['client_id' => $client->id, 'raison_sociale' => 'SARL DEMO', 'champs_manquants' => []]);
        $signataire = Signataire::create([
            'personne_morale_id' => $morale->id,
            'nom' => 'PERSONNE SANS RAPPORT',
            'role' => RoleSignataire::Signataire,
            'statut_filtrage' => 'a_verifier',
        ]);

        app(MoteurFiltrage::class)->filtrer($signataire->fresh());

        $this->assertSame('ecarte', $signataire->fresh()->statut_filtrage->value);
    }

    public function test_un_guichet_ne_peut_pas_acceder_a_l_ecran_de_decision(): void
    {
        $agence = $this->agence();
        $guichet = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Guichet',
            'matricule' => 'GUI-2',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Caissier,
        ]);

        $this->actingAs($guichet, 'agent')->get('/espace/responsable/filtrage')->assertForbidden();
    }

    public function test_la_conformite_peut_confirmer_une_correspondance(): void
    {
        EntreeListe::create(['source' => 'demo', 'nom' => 'AHOUANDJINOU Rachidatou', 'categorie' => 'Test']);

        $agence = $this->agence();
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'AHOUANDJINOU', 'prenoms' => 'Rachidatou', 'champs_manquants' => []]);
        $resultat = app(MoteurFiltrage::class)->filtrer($client->fresh())->first();

        $conformite = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Conformité',
            'matricule' => 'LBC-2',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::ResponsableAgence,
        ]);

        $reponse = $this->actingAs($conformite, 'agent')->put("/espace/responsable/filtrage/{$resultat->id}/decider", [
            'statut' => 'confirme',
            'motif' => 'Correspondance vérifiée manuellement avec la pièce d\'identité.',
        ]);

        $reponse->assertRedirect(route('responsable.filtrage.index'));
        $this->assertSame('confirme', $resultat->fresh()->statut->value);
        $this->assertSame('traitee', Alerte::where('resultat_filtrage_id', $resultat->id)->first()->statut->value);
    }
}
