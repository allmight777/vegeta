<?php

namespace App\Http\Controllers\Responsable\Comptes;

use App\Enums\StatutCompte;
use App\Http\Controllers\Controller;
use App\Http\Requests\Responsable\Comptes\GelerCompteRequest;
use App\Models\Compte;
use App\Services\Audit\Consignateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Gel conservatoire d'un compte (Loi art. 89 à 91) : le blocage automatique sur
 * correspondance forte existe déjà (MoteurFiltrage, ClientPolicy::peutValiderOperation),
 * mais aucune décision humaine explicite n'était tracée jusqu'ici — seulement un refus
 * silencieux d'opération. Ce contrôleur ajoute cette confirmation, strictement réservée
 * au responsable d'agence, jamais visible ni déclenchable par le caissier (art. 63).
 */
class CompteController extends Controller
{
    public function geler(GelerCompteRequest $request, Compte $compte): RedirectResponse
    {
        $agent = Auth::guard('agent')->user();
        abort_unless($compte->agence_id === $agent->agence_id, 403);

        $compte->update([
            'statut' => StatutCompte::Gele,
            'gele_le' => now(),
            'gele_par_agent_id' => $agent->id,
            'motif_gel' => $request->validated('motif'),
        ]);

        Consignateur::enregistrer('agent', $agent->id, 'gel_compte', 'compte', $compte->id);

        return back()->with('statut', 'Compte gelé. Le titulaire n\'est pas informé du motif (Loi art. 89 à 91).');
    }

    public function lever(Compte $compte): RedirectResponse
    {
        $agent = Auth::guard('agent')->user();
        abort_unless($compte->agence_id === $agent->agence_id, 403);

        $compte->update([
            'statut' => StatutCompte::Actif,
            'leve_le' => now(),
            'leve_par_agent_id' => $agent->id,
        ]);

        Consignateur::enregistrer('agent', $agent->id, 'levee_gel_compte', 'compte', $compte->id);

        return back()->with('statut', 'Gel levé, compte réactivé.');
    }
}
