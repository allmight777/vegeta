<?php

namespace App\Http\Controllers\Admin\Agents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Agents\CreerAgentRequest;
use App\Mail\ListeIdentifiantsMail;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\UserPassword;
use App\Services\Audit\Consignateur;
use App\Services\Securite\IndexAveugle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Gestion des comptes caissier et responsable d'agence (09_PROMPT_TROIS_PROFILS §5) —
 * seul l'administrateur crée/désactive ces comptes (table 3, "Création/désactivation de
 * comptes"). Pas d'infrastructure e-mail dans ce dépôt : le mot de passe généré n'est
 * affiché qu'une seule fois, immédiatement après création ou réinitialisation, et
 * conservé chiffré dans users_password pour consultation ultérieure par l'admin.
 */
class AgentController extends Controller
{
    public function index(Request $request): View
    {
        $agences = $this->agencesVisibles();

        $recherche = trim((string) $request->string('q'));

        $agents = Agent::with('agence.reseau')
            ->whereIn('agence_id', $agences->pluck('id'))
            ->when($request->filled('agence_id'), fn ($q) => $q->where('agence_id', $request->integer('agence_id')))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->when($recherche !== '', function ($query) use ($recherche) {
                $query->where(function ($query) use ($recherche) {
                    $query->where('nom', 'like', "%{$recherche}%")
                        ->orWhere('matricule', 'like', "%{$recherche}%")
                        ->orWhere('email', 'like', "%{$recherche}%");
                });
            })
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('admin.agents.index', [
            'agents' => $agents,
            'agences' => $agences,
            'recherche' => $recherche,
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

        UserPassword::create([
            'agent_id' => $agent->id,
            'mot_de_passe_chiffre' => Crypt::encryptString($motDePasse),
        ]);

        Consignateur::enregistrer('admin', auth('admin')->id(), 'creation_agent', 'agent', $agent->id);

        return redirect()
            ->route('admin.agents.index')
            ->with('statut', 'Compte créé.')
            ->with('identifiants_generes', [
                'matricule' => $donnees['matricule'],
                'mot_de_passe' => $motDePasse,
            ]);
    }

    public function activerOuDesactiver(Agent $agent): RedirectResponse
    {
        abort_unless($this->agencesVisibles()->pluck('id')->contains($agent->agence_id), 403);

        $agent->update(['actif' => ! $agent->actif]);

        Consignateur::enregistrer(
            'admin',
            auth('admin')->id(),
            $agent->actif ? 'activation_agent' : 'desactivation_agent',
            'agent',
            $agent->id
        );

        return redirect()
            ->route('admin.agents.index')
            ->with('statut', $agent->actif ? 'Compte activé.' : 'Compte désactivé.');
    }

    public function reinitialiserMotDePasse(Agent $agent): RedirectResponse
    {
        abort_unless($this->agencesVisibles()->pluck('id')->contains($agent->agence_id), 403);

        $motDePasse = Str::password(16);
        $agent->update(['mot_de_passe' => Hash::make($motDePasse)]);

        UserPassword::updateOrCreate(
            ['agent_id' => $agent->id],
            ['mot_de_passe_chiffre' => Crypt::encryptString($motDePasse)]
        );

        Consignateur::enregistrer('admin', auth('admin')->id(), 'reinitialisation_mot_de_passe_agent', 'agent', $agent->id);

        return redirect()
            ->route('admin.agents.index')
            ->with('statut', 'Mot de passe réinitialisé.')
            ->with('identifiants_generes', [
                'matricule' => null,
                'mot_de_passe' => $motDePasse,
            ]);
    }

    /**
     * Envoi par e-mail d'un PDF protégé contenant la liste filtrée des identifiants.
     *
     * Toutes les erreurs sont remontées en flash (back()->withErrors) plutôt qu'en 422
     * brut : l'utilisateur reste sur la page agents avec un message clair et peut
     * ajuster ses filtres avant de réessayer. Aucune fuite d'information en cas de
     * filtre vide (pas d'information sur le nombre d'agents global).
     */
    public function envoyerPdf(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'email' => 'required|email',
            'mot_de_passe_pdf' => 'required|string|min:4',
            'agence_id' => 'nullable|exists:agences,id',
            'role' => 'nullable|in:caissier,responsable_agence',
        ]);

        $agencesVisibles = $this->agencesVisibles()->pluck('id');

        $agents = Agent::with(['agence.reseau', 'motDePasse'])
            ->whereIn('agence_id', $agencesVisibles)
            ->when($donnees['agence_id'] ?? null, fn ($q, $v) => $q->where('agence_id', $v))
            ->when($donnees['role'] ?? null, fn ($q, $v) => $q->where('role', $v))
            ->orderBy('nom')
            ->get();

        if ($agents->isEmpty()) {
            return back()->withErrors([
                'email' => 'Aucun agent ne correspond aux filtres sélectionnés. Ajustez les filtres avant d\'envoyer la liste.',
            ])->withInput();
        }

        $lignes = $agents->map(fn ($agent) => [
            'nom' => $agent->nom,
            'matricule' => $agent->matricule,
            'mot_de_passe' => $agent->motDePasse
                ? Crypt::decryptString($agent->motDePasse->mot_de_passe_chiffre)
                : '—',
        ]);

        $cheminPdf = $this->genererPdfProtege($lignes, $donnees['mot_de_passe_pdf']);

        try {
            Mail::to($donnees['email'])->send(
                new ListeIdentifiantsMail($cheminPdf, $donnees['mot_de_passe_pdf'])
            );
        } finally {
            // Nettoyage systématique du fichier temporaire, même si l'envoi échoue.
            if (file_exists($cheminPdf)) {
                @unlink($cheminPdf);
            }
        }

        Consignateur::enregistrer('admin', auth('admin')->id(), 'envoi_pdf_identifiants', 'agent', null);

        return back()->with('statut', 'PDF envoyé à '.$donnees['email'].'.');
    }

    /**
     * Génère un PDF protégé par mot de passe (ouverture uniquement) à partir des
     * lignes fournies. Le fichier est stocké dans storage/app/temp et retourné sous
     * forme de chemin absolu — l'appelant est responsable de sa suppression.
     *
     * @param  Collection<int, array{nom:string,matricule:string,mot_de_passe:string}>  $lignes
     */
    private function genererPdfProtege($lignes, string $motDePasse): string
    {
        $dossierTemp = storage_path('app/temp');

        if (! is_dir($dossierTemp)) {
            mkdir($dossierTemp, 0700, true);
        }

        $mpdf = new Mpdf([
            'tempDir' => $dossierTemp,
        ]);

        $mpdf->WriteHTML(
            view('admin.agents.pdf-liste-identifiants', ['lignes' => $lignes])->render()
        );

        // Protection par mot de passe à l'ouverture (permissions vides = tout est
        // permis une fois le mot de passe saisi ; mot de passe maître vide).
        $mpdf->SetProtection([], $motDePasse, '');

        $chemin = $dossierTemp.DIRECTORY_SEPARATOR.Str::uuid().'.pdf';
        $mpdf->Output($chemin, Destination::FILE);

        return $chemin;
    }

    /**
     * Retourne les agences visibles par l'administrateur connecté :
     *   - admin plateforme : toutes les agences ;
     *   - admin réseau    : uniquement les agences de son réseau.
     */
    private function agencesVisibles()
    {
        $admin = auth('admin')->user();

        return $admin->estAdminPlateforme()
            ? Agence::with('reseau')->orderBy('nom')->get()
            : Agence::with('reseau')->where('reseau_id', $admin->reseau_id)->orderBy('nom')->get();
    }
}
