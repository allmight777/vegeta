<?php

namespace App\Http\Controllers\Responsable\Soupcons;

use App\Enums\AvisTechniqueSoupcon;
use App\Enums\StatutDossierSoupcon;
use App\Enums\StatutSuggestionSoupcon;
use App\Http\Controllers\Controller;
use App\Mail\DossierSoupconTraiteMail;
use App\Models\Agent;
use App\Models\DossierAnalyseSoupcon;
use App\Services\Audit\Consignateur;
use App\Services\Conformite\AssistantSoupcon;
use App\Services\Conformite\PreparateurDossierSoupcon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Dossiers de soupçon reçus par le responsable d'agence (18_PROMPT §6). Périmètre : uniquement
 * les clients de SA propre agence. Il lit la fiche du contrôleur en lecture seule puis rend sa
 * décision par un clic explicite — jamais d'enregistrement automatique.
 */
class DossierRecuController extends Controller
{
    public function index(): View
    {
        $agent = auth('agent')->user();

        $dossiers = $this->dossiersDeLAgence($agent)
            ->whereIn('statut', [StatutDossierSoupcon::Transmise->value, StatutDossierSoupcon::Traitee->value])
            ->with(['client.personnePhysique', 'client.personneMorale', 'suggestion'])
            ->orderByRaw("CASE statut WHEN 'transmise' THEN 0 ELSE 1 END")
            ->orderByDesc('transmis_le')
            ->get();

        return view('responsable.soupcons.index', ['dossiers' => $dossiers]);
    }

    public function afficher(string $dossier, PreparateurDossierSoupcon $preparateur): View
    {
        $agent = auth('agent')->user();
        $dossier = $this->trouver($dossier, $agent);

        Consignateur::enregistrer('agent', $agent->id, 'consultation_dossier_soupcon', 'dossier_analyse_soupcon', $dossier->id);

        return view('responsable.soupcons.afficher', [
            'dossier' => $dossier,
            'prefill' => $preparateur->prefill($dossier->suggestion),
            'avis' => AvisTechniqueSoupcon::cases(),
            'agent' => $agent,
        ]);
    }

    public function decider(Request $requete, string $dossier): RedirectResponse
    {
        $agent = auth('agent')->user();
        $dossier = $this->trouver($dossier, $agent);
        abort_unless($dossier->statut === StatutDossierSoupcon::Transmise, 403);

        $donnees = $requete->validate([
            'avis_technique_responsable' => ['required', Rule::enum(AvisTechniqueSoupcon::class)],
        ], ['avis_technique_responsable.required' => 'Choisissez un avis technique avant de valider votre décision.']);

        $dossier->update([
            'avis_technique_responsable' => $donnees['avis_technique_responsable'],
            'responsable_id' => $agent->id,
            'decide_le' => now(),
            'statut' => StatutDossierSoupcon::Traitee,
        ]);
        $dossier->suggestion->update(['statut' => StatutSuggestionSoupcon::Traitee]);

        // La décision n'est jamais journalisée dans l'action : seulement le fait qu'elle a été rendue.
        Consignateur::enregistrer('agent', $agent->id, 'dossier_soupcon_decide', 'dossier_analyse_soupcon', $dossier->id);

        $this->notifierControleur($dossier);

        return redirect()->route('responsable.soupcons.index')
            ->with('statut', 'Décision enregistrée pour le dossier '.$dossier->reference().'.');
    }

    public function resumer(string $dossier, AssistantSoupcon $assistant): JsonResponse
    {
        $agent = auth('agent')->user();
        $dossier = $this->trouver($dossier, $agent);

        Consignateur::enregistrer('agent', $agent->id, 'resume_dossier_soupcon', 'dossier_analyse_soupcon', $dossier->id);

        return response()->json($assistant->resumerPourResponsable($dossier, $agent));
    }

    private function notifierControleur(DossierAnalyseSoupcon $dossier): void
    {
        $controleur = $dossier->controleur;

        if ($controleur === null || blank($controleur->email)) {
            Consignateur::enregistrer('systeme', null, 'notification_dossier_traite_sans_adresse', 'dossier_analyse_soupcon', $dossier->id);

            return;
        }

        try {
            Mail::to($controleur->email)->send(new DossierSoupconTraiteMail($dossier->reference()));
            Consignateur::enregistrer('systeme', null, 'notification_dossier_traite', 'dossier_analyse_soupcon', $dossier->id);
        } catch (Throwable $e) {
            // Une notification qui échoue ne remet jamais en cause la décision.
            report($e);
            Consignateur::enregistrer('systeme', null, 'notification_dossier_traite_echec', 'dossier_analyse_soupcon', $dossier->id);
        }
    }

    /** Dossiers des clients de l'agence du responsable uniquement. */
    private function dossiersDeLAgence(Agent $agent)
    {
        return DossierAnalyseSoupcon::query()
            ->whereHas('client', fn ($q) => $q->deLAgence((int) $agent->agence_id));
    }

    /** Un dossier d'une autre agence, ou non encore transmis, n'existe pas pour ce responsable : 404. */
    private function trouver(string $id, Agent $agent): DossierAnalyseSoupcon
    {
        return $this->dossiersDeLAgence($agent)
            ->whereIn('statut', [StatutDossierSoupcon::Transmise->value, StatutDossierSoupcon::Traitee->value])
            ->with(['client.personnePhysique', 'client.personneMorale', 'suggestion', 'controleur', 'responsable'])
            ->findOrFail($id);
    }
}
