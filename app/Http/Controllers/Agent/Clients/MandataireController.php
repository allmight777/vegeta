<?php

namespace App\Http\Controllers\Agent\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Clients\MandataireRequest;
use App\Models\Mandataire;
use App\Models\PersonnePhysique;
use App\Services\Audit\Consignateur;
use Illuminate\Http\RedirectResponse;

class MandataireController extends Controller
{
    public function stocker(MandataireRequest $request, PersonnePhysique $personnePhysique): RedirectResponse
    {
        if ($personnePhysique->mandataires()->count() >= 3) {
            return redirect()->route('agent.clients.completer', $personnePhysique->client_id)
                ->withErrors('Un client ne peut avoir plus de 3 mandataires désignés.');
        }

        $mandataire = Mandataire::create([
            'personne_physique_id' => $personnePhysique->id,
            'nom' => $request->validated('nom'),
            'prenoms' => $request->validated('prenoms'),
            'lien_parente' => $request->validated('lien_parente'),
        ]);

        Consignateur::enregistrer('agent', auth('agent')->id(), 'ajout_mandataire', 'mandataire', $mandataire->id);

        return redirect()->route('agent.clients.completer', $personnePhysique->client_id)
            ->with('statut', 'Mandataire ajouté.');
    }

    public function detruire(Mandataire $mandataire): RedirectResponse
    {
        $clientId = $mandataire->personnePhysique->client_id;
        Consignateur::enregistrer('agent', auth('agent')->id(), 'suppression_mandataire', 'mandataire', $mandataire->id);
        $mandataire->delete();

        return redirect()->route('agent.clients.completer', $clientId)->with('statut', 'Mandataire retiré.');
    }
}
