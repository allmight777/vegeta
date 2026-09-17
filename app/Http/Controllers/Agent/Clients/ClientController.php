<?php

namespace App\Http\Controllers\Agent\Clients;

use App\Enums\SourceCreation;
use App\Enums\TypeClient;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Clients\CompleterClientRequest;
use App\Http\Requests\Agent\Clients\CreerClientRequest;
use App\Models\Client;
use App\Models\PersonneMorale;
use App\Models\PersonnePhysique;
use App\Services\Audit\Consignateur;
use App\Services\Contexte\ContexteReseau;
use App\Services\Filtrage\MoteurFiltrage;
use App\Services\Kyc\CalculateurCompletude;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request, ContexteReseau $contexte): View
    {
        $clients = Client::with(['personnePhysique', 'personneMorale'])
            ->where('reseau_id', $contexte->reseauId())
            ->when($request->boolean('a_completer'), fn ($q) => $q->where('score_completude_kyc', '<', 100))
            ->orderBy('score_completude_kyc')
            ->paginate(20);

        return view('agent.clients.index', ['clients' => $clients]);
    }

    public function creer(): View
    {
        return view('agent.clients.creer');
    }

    public function stocker(CreerClientRequest $request, ContexteReseau $contexte, CalculateurCompletude $completude, MoteurFiltrage $moteurFiltrage): RedirectResponse
    {
        $donnees = $request->validated();

        $client = Client::create([
            'reseau_id' => $contexte->reseauId(),
            'type' => $donnees['type'],
            'nature_relation' => $donnees['nature_relation'],
            'source_creation' => SourceCreation::SaisieAgent,
        ]);

        if ($donnees['type'] === TypeClient::PersonnePhysique->value) {
            PersonnePhysique::create([
                'client_id' => $client->id,
                'nom' => $donnees['nom'],
                'prenoms' => $donnees['prenoms'],
                'champs_manquants' => [],
            ]);
        } else {
            PersonneMorale::create([
                'client_id' => $client->id,
                'raison_sociale' => $donnees['raison_sociale'],
                'champs_manquants' => [],
            ]);
        }

        $completude->evaluer($client->fresh(['personnePhysique', 'personneMorale']));
        $moteurFiltrage->filtrer($client->fresh());

        Consignateur::enregistrer('agent', auth('agent')->id(), 'creation_client', 'client', $client->id);

        return redirect()->route('agent.clients.completer', $client)->with('statut', 'Client créé. Complétez sa fiche.');
    }

    public function completer(Client $client): View
    {
        $client->load(['personnePhysique', 'personneMorale.signataires', 'comptes']);

        return view('agent.clients.completer', [
            'client' => $client,
            'champsKyc' => config('champs_kyc_obligatoires.'.$client->type->value),
        ]);
    }

    public function mettreAJour(CompleterClientRequest $request, Client $client, CalculateurCompletude $completude, MoteurFiltrage $moteurFiltrage): RedirectResponse
    {
        $donnees = $request->validated();

        if ($client->type === TypeClient::PersonnePhysique) {
            $client->personnePhysique?->fill(array_intersect_key($donnees, PersonnePhysique::CHAMPS_COMPLETABLES))->save();
        } else {
            $client->personneMorale?->fill(array_intersect_key($donnees, array_flip(['raison_sociale', 'forme_juridique', 'rccm', 'ifu'])))->save();
        }

        $moteurFiltrage->filtrer($client->fresh());
        $resultat = $completude->evaluer($client->fresh(['personnePhysique', 'personneMorale']));

        Consignateur::enregistrer('agent', auth('agent')->id(), 'mise_a_jour_kyc', 'client', $client->id);

        return redirect()->route('agent.clients.completer', $client)
            ->with('statut', "Fiche mise à jour — complétude {$resultat['score']} %.");
    }
}
