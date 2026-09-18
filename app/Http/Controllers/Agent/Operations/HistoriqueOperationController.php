<?php

namespace App\Http\Controllers\Agent\Operations;

use App\Http\Controllers\Controller;
use App\Models\Operation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HistoriqueOperationController extends Controller
{
    /**
     * Historique strictement personnel du caissier connecté. Les filtres de client sont
     * évalués après déchiffrement des champs d'identité, qui ne peuvent pas être recherchés
     * en SQL avec LIKE.
     */
    public function index(Request $request): View
    {
        $filtres = $request->validate([
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'client' => ['nullable', 'string', 'max:150'],
            'compte' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:depot,retrait'],
            'mode_paiement' => ['nullable', 'in:especes,virement,mobile_money'],
            'montant_min' => ['nullable', 'numeric', 'min:0'],
            'montant_max' => ['nullable', 'numeric', 'gte:montant_min'],
        ]);

        $operations = Operation::query()
            ->with(['compte.client.personnePhysique', 'compte.client.personneMorale'])
            ->where('agent_id', auth('agent')->id())
            ->when($filtres['date_debut'] ?? null, fn ($query, $date) => $query->whereDate('effectuee_le', '>=', $date))
            ->when($filtres['date_fin'] ?? null, fn ($query, $date) => $query->whereDate('effectuee_le', '<=', $date))
            ->when($filtres['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filtres['mode_paiement'] ?? null, fn ($query, $mode) => $query->where('mode_paiement', $mode))
            ->when($filtres['montant_min'] ?? null, fn ($query, $montant) => $query->where('montant', '>=', $montant))
            ->when($filtres['montant_max'] ?? null, fn ($query, $montant) => $query->where('montant', '<=', $montant))
            ->latest('effectuee_le')
            ->get()
            ->filter(function (Operation $operation) use ($filtres): bool {
                $compte = $operation->compte;
                $client = $compte?->client;

                if (filled($filtres['compte'] ?? null) && ! Str::contains(
                    Str::lower((string) $compte?->numero),
                    Str::lower($filtres['compte'])
                )) {
                    return false;
                }

                if (! filled($filtres['client'] ?? null)) {
                    return true;
                }

                $identite = $client?->nomAffichage() ?? '';

                return Str::contains(Str::lower($identite), Str::lower($filtres['client']));
            })
            ->values();

        $parPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $historique = new LengthAwarePaginator(
            $operations->forPage($page, $parPage)->values(),
            $operations->count(),
            $parPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('agent.operations.historique', compact('historique', 'filtres'));
    }
}
