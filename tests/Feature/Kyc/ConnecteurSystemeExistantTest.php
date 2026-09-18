<?php

namespace Tests\Feature\Kyc;

use App\Enums\NatureRelation;
use App\Enums\RoleAgent;
use App\Enums\SourceCreation;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Services\Empreinte\ServiceEmpreinte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConnecteurSystemeExistantTest extends TestCase
{
    use RefreshDatabase;

    private function agenceEtAgent(): array
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
        $agent = Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => 'GUI-'.uniqid(),
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => RoleAgent::Caissier,
        ]);

        return [$agence, $agent];
    }

    private function client(Agence $agence, array $champs): Client
    {
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
        ]);
        $personne = PersonnePhysique::create(array_merge(['client_id' => $client->id, 'champs_manquants' => []], $champs));
        app(ServiceEmpreinte::class)->calculerPourPersonnePhysique($personne);
        $personne->save();

        return $client->fresh(['personnePhysique']);
    }

    public function test_appliquer_ne_modifie_que_les_champs_coches_et_jamais_un_champ_deja_rempli_sans_confirmation(): void
    {
        [$agence, $agent] = $this->agenceEtAgent();

        // "Système existant" simulé par un autre enregistrement déjà complet.
        $this->client($agence, [
            'nom' => 'KPADONOU', 'prenoms' => 'Fidèle', 'date_naissance' => '1988-03-14',
            'adresse' => 'Cotonou, quartier X', 'profession' => 'Commerçant',
        ]);

        // Client à compléter : même identité, mais adresse déjà renseignée (différente) et profession vide.
        $clientACompleter = $this->client($agence, [
            'nom' => 'KPADONOU', 'prenoms' => 'Fidèle', 'date_naissance' => '1988-03-14',
            'adresse' => 'Adresse déjà saisie par l\'agent',
        ]);

        $reponseRecherche = $this->actingAs($agent, 'agent')->get(route('agent.clients.systeme-existant.rechercher', $clientACompleter));
        $reponseRecherche->assertOk();

        $reponseAppliquer = $this->actingAs($agent, 'agent')->post(route('agent.clients.systeme-existant.appliquer', $clientACompleter), [
            'champs' => ['profession' => '1'],
            'valeurs' => ['profession' => 'Commerçant', 'adresse' => 'Cotonou, quartier X'],
        ]);
        $reponseAppliquer->assertRedirect();

        $clientACompleter->personnePhysique->refresh();
        $this->assertSame('Commerçant', $clientACompleter->personnePhysique->profession);
        $this->assertSame('Adresse déjà saisie par l\'agent', $clientACompleter->personnePhysique->adresse);
    }
}
