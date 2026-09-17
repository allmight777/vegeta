<?php

namespace App\Http\Controllers\Agent\Filtrage;

use App\Enums\StatutAlerte;
use App\Enums\StatutFiltrage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Filtrage\DeciderRequest;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\ResultatFiltrage;
use App\Models\Signataire;
use App\Services\Audit\Consignateur;
use App\Services\Contexte\ContexteReseau;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FiltrageController extends Controller
{
    public function index(ContexteReseau $contexte): View
    {
        $resultats = ResultatFiltrage::with([
            'entreeListe',
            'filtrable' => fn ($morphTo) => $morphTo->morphWith([
                Client::class => ['personnePhysique', 'personneMorale'],
                Signataire::class => ['personneMorale.client'],
            ]),
        ])
            ->where('statut', StatutFiltrage::AVerifier)
            ->get()
            ->filter(function ($resultat) use ($contexte) {
                $cible = $resultat->filtrable;

                return $cible !== null && $this->reseauDe($cible) === $contexte->reseauId();
            })
            ->sortByDesc('score_similarite')
            ->values();

        return view('agent.filtrage.index', ['resultats' => $resultats]);
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

        return redirect()->route('agent.filtrage.index')->with('statut', 'Décision enregistrée.');
    }

    private function reseauDe(Client|Signataire $cible): ?int
    {
        return $cible instanceof Client ? $cible->reseau_id : $cible->personneMorale->client->reseau_id;
    }
}
