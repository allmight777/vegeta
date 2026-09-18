<?php

namespace App\Http\Controllers\Responsable\Filtrage;

use App\Enums\StatutAlerte;
use App\Enums\StatutFiltrage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Filtrage\DeciderRequest;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\ResultatFiltrage;
use App\Models\Signataire;
use App\Services\Audit\Consignateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FiltrageController extends Controller
{
    public function index(): View
    {
        $agenceId = Auth::guard('agent')->user()->agence_id;
        $clientIdsDeLAgence = Client::deLAgence($agenceId)->pluck('id')->all();

        $resultats = ResultatFiltrage::with([
            'entreeListe',
            'filtrable' => fn ($morphTo) => $morphTo->morphWith([
                Client::class => ['personnePhysique', 'personneMorale'],
                Signataire::class => ['personneMorale.client'],
            ]),
        ])
            ->where('statut', StatutFiltrage::AVerifier)
            ->get()
            ->filter(function ($resultat) use ($clientIdsDeLAgence) {
                $client = $this->clientDe($resultat->filtrable);

                return $client !== null && in_array($client->id, $clientIdsDeLAgence, true);
            })
            ->sortByDesc('score_similarite')
            ->values();

        return view('responsable.filtrage.index', ['resultats' => $resultats]);
    }

    public function decider(DeciderRequest $request, ResultatFiltrage $resultatFiltrage): RedirectResponse
    {
        $donnees = $request->validated();
        $agentId = auth('agent')->id();

        $resultatFiltrage->update([
            'statut' => $donnees['statut'],
            'verifie_par_agent_id' => $agentId,
            'verifie_le' => now(),
            'motif_ecart' => $donnees['motif'],
        ]);

        $cible = $resultatFiltrage->filtrable;
        if ($cible instanceof Signataire) {
            $cible->update(['statut_filtrage' => $donnees['statut']]);
        }
        if ($cible instanceof Client && $donnees['statut'] === 'confirme' && $cible->statut_ppe->value === 'ppe_a_verifier') {
            $cible->update(['statut_ppe' => 'ppe_confirme']);
        }

        Alerte::where('resultat_filtrage_id', $resultatFiltrage->id)->update([
            'statut' => StatutAlerte::Traitee,
            'traitee_par_agent_id' => $agentId,
            'traitee_le' => now(),
        ]);

        Consignateur::enregistrer('agent', $agentId, 'decision_filtrage', 'resultat_filtrage', $resultatFiltrage->id);

        return redirect()->route('responsable.filtrage.index')->with('statut', 'Décision enregistrée.');
    }

    private function clientDe(Client|Signataire|null $cible): ?Client
    {
        return match (true) {
            $cible instanceof Client => $cible,
            $cible instanceof Signataire => $cible->personneMorale?->client,
            default => null,
        };
    }
}
