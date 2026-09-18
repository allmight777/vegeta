<?php

namespace App\Http\Controllers\Admin\Agents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Agents\CreerAgentRequest;
use App\Models\Agence;
use App\Models\Agent;
use App\Services\Audit\Consignateur;
use App\Services\Securite\IndexAveugle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Gestion des comptes caissier et responsable d'agence (09_PROMPT_TROIS_PROFILS §5) —
 * seul l'administrateur crée/désactive ces comptes (table 3, "Création/désactivation de
 * comptes"). Pas d'infrastructure e-mail dans ce dépôt : le mot de passe généré n'est
 * affiché qu'une seule fois, immédiatement après création ou réinitialisation.
 */
class AgentController extends Controller
{
    public function index(Request $request): View
    {
        $agences = $this->agencesVisibles();

        $agents = Agent::with('agence')
            ->whereIn('agence_id', $agences->pluck('id'))
            ->when($request->filled('agence_id'), fn ($q) => $q->where('agence_id', $request->integer('agence_id')))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('admin.agents.index', [
            'agents' => $agents,
            'agences' => $agences,
        ]);
    }

    public function creer(): View
    {
        return view('admin.agents.creer', ['agences' => $this->agencesVisibles()]);
    }

    public function stocker(CreerAgentRequest $request, IndexAveugle $indexAveugle): RedirectResponse
    {
        $donnees = $request->validated();

        abort_unless($this->agencesVisibles()->pluck('id')->contains((int) $donnees['agence_id']), 403);

        $idx = $indexAveugle->calculer($donnees['matricule'], 'matricule');

        if (Agent::where('matricule_idx', $idx)->exists()) {
            throw ValidationException::withMessages(['matricule' => 'Ce matricule est déjà utilisé.']);
        }

        $motDePasse = Str::password(16);

        $agent = Agent::create([
            'agence_id' => $donnees['agence_id'],
            'nom' => $donnees['nom'],
            'matricule' => $donnees['matricule'],
            'mot_de_passe' => Hash::make($motDePasse),
            'role' => $donnees['role'],
            'civilite' => $donnees['civilite'] ?? 'non_precise',
            'actif' => true,
        ]);

        Consignateur::enregistrer('admin', auth('admin')->id(), 'creation_agent', 'agent', $agent->id);

        return redirect()->route('admin.agents.index')->with('statut', 'Compte créé.')->with('identifiants_generes', [
            'matricule' => $donnees['matricule'],
            'mot_de_passe' => $motDePasse,
        ]);
    }

    public function activerOuDesactiver(Agent $agent): RedirectResponse
    {
        abort_unless($this->agencesVisibles()->pluck('id')->contains($agent->agence_id), 403);

        $agent->update(['actif' => ! $agent->actif]);

        Consignateur::enregistrer('admin', auth('admin')->id(), $agent->actif ? 'activation_agent' : 'desactivation_agent', 'agent', $agent->id);

        return redirect()->route('admin.agents.index')->with('statut', $agent->actif ? 'Compte activé.' : 'Compte désactivé.');
    }

    public function reinitialiserMotDePasse(Agent $agent): RedirectResponse
    {
        abort_unless($this->agencesVisibles()->pluck('id')->contains($agent->agence_id), 403);

        $motDePasse = Str::password(16);
        $agent->update(['mot_de_passe' => Hash::make($motDePasse)]);

        Consignateur::enregistrer('admin', auth('admin')->id(), 'reinitialisation_mot_de_passe_agent', 'agent', $agent->id);

        return redirect()->route('admin.agents.index')->with('statut', 'Mot de passe réinitialisé.')->with('identifiants_generes', [
            'matricule' => null,
            'mot_de_passe' => $motDePasse,
        ]);
    }

    private function agencesVisibles()
    {
        $admin = auth('admin')->user();

        return $admin->estAdminPlateforme()
            ? Agence::with('reseau')->orderBy('nom')->get()
            : Agence::with('reseau')->where('reseau_id', $admin->reseau_id)->orderBy('nom')->get();
    }
}
