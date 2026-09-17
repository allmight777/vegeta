<?php

namespace App\Http\Controllers\Agent\Authentification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Authentification\ConnexionRequest;
use App\Models\Agent;
use App\Services\Audit\Consignateur;
use App\Services\Securite\IndexAveugle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConnexionController extends Controller
{
    public function creer(): View
    {
        return view('agent.authentification.connexion');
    }

    public function stocker(ConnexionRequest $request, IndexAveugle $indexAveugle): RedirectResponse
    {
        $donnees = $request->validated();
        $idx = $indexAveugle->calculer($donnees['matricule'], 'matricule');
        $agent = Agent::where('matricule_idx', $idx)->first();

        if (! $agent || ! $agent->actif || ! Hash::check($donnees['mot_de_passe'], $agent->mot_de_passe)) {
            Consignateur::enregistrer('agent', $agent?->id, 'connexion_echouee');

            throw ValidationException::withMessages([
                'matricule' => 'Identifiants incorrects ou compte désactivé.',
            ]);
        }

        Auth::guard('agent')->login($agent);
        $request->session()->regenerate();
        Consignateur::enregistrer('agent', $agent->id, 'connexion');

        return redirect()->intended(route('agent.tableau-de-bord.index'));
    }

    public function detruire(Request $request): RedirectResponse
    {
        $agentId = Auth::guard('agent')->id();
        Auth::guard('agent')->logout();
        Consignateur::enregistrer('agent', $agentId, 'deconnexion');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/connexion');
    }
}
