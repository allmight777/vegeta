<?php

namespace App\Http\Controllers\Agent\Clients;

use App\Enums\StatutFiltrage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Clients\SignataireRequest;
use App\Models\PersonneMorale;
use App\Models\Signataire;
use App\Services\Audit\Consignateur;
use App\Services\Filtrage\MoteurFiltrage;
use Illuminate\Http\RedirectResponse;

class SignataireController extends Controller
{
    public function stocker(SignataireRequest $request, PersonneMorale $personneMorale, MoteurFiltrage $moteurFiltrage): RedirectResponse
    {
        $signataire = Signataire::create([
            'personne_morale_id' => $personneMorale->id,
            'nom' => $request->validated('nom'),
            'role' => $request->validated('role'),
            'pourcentage_detention' => $request->validated('pourcentage_detention'),
            'statut_filtrage' => StatutFiltrage::AVerifier,
        ]);

        // Chaque signataire, mandataire et bénéficiaire effectif est contrôlé au même
        // titre que le client principal, avant toute validation de la fiche (§5.4).
        $moteurFiltrage->filtrer($signataire->fresh());

        Consignateur::enregistrer('agent', auth('agent')->id(), 'ajout_signataire', 'signataire', $signataire->id);

        return redirect()->route('agent.clients.completer', $personneMorale->client_id)
            ->with('statut', 'Signataire ajouté — à vérifier par la conformité.');
    }

    public function detruire(Signataire $signataire): RedirectResponse
    {
        $clientId = $signataire->personneMorale->client_id;
        Consignateur::enregistrer('agent', auth('agent')->id(), 'suppression_signataire', 'signataire', $signataire->id);
        $signataire->delete();

        return redirect()->route('agent.clients.completer', $clientId)->with('statut', 'Signataire retiré.');
    }
}
