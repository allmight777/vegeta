<?php

namespace App\Http\Controllers\Responsable\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\Securite\IndexAveugle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Supervision en lecture seule des dossiers clients de l'agence (09_PROMPT_TROIS_PROFILS
 * §3) : le responsable d'agence consulte, il ne crée ni ne complète — ces actions restent
 * sur Agent\Clients\ClientController (y compris pour la fiche RLBC/FT, que ce rôle peut
 * continuer à renseigner via le même formulaire partagé qu'avant la restructuration).
 */
class ClientController extends Controller
{
    public function index(Request $request, IndexAveugle $indexAveugle): View
    {
        $agenceId = Auth::guard('agent')->user()->agence_id;
        $recherche = trim((string) $request->string('q'));

        $clients = Client::deLAgence($agenceId)
            ->with(['personnePhysique', 'personneMorale'])
            ->when($request->boolean('a_completer'), fn ($q) => $q->where('score_completude_kyc', '<', 100))
            ->when($recherche !== '', function ($query) use ($recherche, $indexAveugle) {
                $idxNom = $indexAveugle->calculer($recherche, 'nom');
                $idxRaisonSociale = $indexAveugle->calculer($recherche, 'raison_sociale');

                $query->where(function ($query) use ($idxNom, $idxRaisonSociale) {
                    $query->whereHas('personnePhysique', fn ($q) => $q->where('nom_idx', $idxNom))
                        ->orWhereHas('personneMorale', fn ($q) => $q->where('raison_sociale_idx', $idxRaisonSociale));
                });
            })
            ->orderBy('score_completude_kyc')
            ->paginate(20)
            ->withQueryString();

        return view('responsable.clients.index', ['clients' => $clients, 'recherche' => $recherche]);
    }

    public function afficher(Client $client): View
    {
        $agenceId = Auth::guard('agent')->user()->agence_id;
        abort_unless(Client::deLAgence($agenceId)->whereKey($client->id)->exists(), 404);

        $client->load(['personnePhysique.mandataires', 'personneMorale.signataires', 'comptes.agence']);

        return view('responsable.clients.afficher', ['client' => $client]);
    }
}
