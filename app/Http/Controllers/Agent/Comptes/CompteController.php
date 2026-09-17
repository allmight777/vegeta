<?php

namespace App\Http\Controllers\Agent\Comptes;

use App\Enums\StatutCompte;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Compte;
use App\Services\Audit\Consignateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class CompteController extends Controller
{
    public function stocker(Client $client): RedirectResponse
    {
        $agenceId = auth('agent')->user()->agence_id;

        $compte = Compte::create([
            'client_id' => $client->id,
            'agence_id' => $agenceId,
            'numero' => 'CPT-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'statut' => StatutCompte::Actif,
        ]);

        Consignateur::enregistrer('agent', auth('agent')->id(), 'ouverture_compte', 'compte', $compte->id);

        return redirect()->route('agent.clients.completer', $client)->with('statut', 'Compte ouvert : '.$compte->numero);
    }
}
