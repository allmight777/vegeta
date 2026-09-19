<?php

namespace App\Http\Controllers\Controleur\Soupcons;

use App\Enums\AvisTechniqueSoupcon;
use App\Enums\CanalSoupcon;
use App\Enums\IndicateurSoupcon;
use App\Enums\NiveauRisqueSoupcon;
use App\Enums\StatutDossierSoupcon;
use App\Enums\StatutSuggestionSoupcon;
use App\Enums\TypeClientSoupcon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Controleur\EnregistrerDossierSoupconRequest;
use App\Models\Agent;
use App\Models\DossierAnalyseSoupcon;
use App\Models\SuggestionSoupcon;
use App\Services\Audit\Consignateur;
use App\Services\Conformite\PreparateurDossierSoupcon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Fiche d'analyse de soupçon côté contrôleur permanent (18_PROMPT §5.2). Rien n'est transmis tant
 * que le contrôleur n'a pas cliqué sur « Transmettre au responsable d'agence » ; il ne voit
 * jamais la décision du responsable.
 */
class DossierSoupconController extends Controller
{
    public function ouvrir(SuggestionSoupcon $suggestion, PreparateurDossierSoupcon $preparateur): View
    {
        $agent = $this->controleur();
        $this->verifierPerimetre($suggestion, $agent);

        // Consultation d'une fiche client complète : toujours journalisée (CLAUDE.md §5).
        Consignateur::enregistrer('agent', $agent->id, 'consultation_dossier_soupcon', 'suggestion_soupcon', $suggestion->id);

        $suggestion->load('dossier');
        $prefill = $preparateur->prefill($suggestion);
        $dossier = $suggestion->dossier;

        return view('controleur.soupcons.fiche', [
            'suggestion' => $suggestion,
            'dossier' => $dossier,
            'prefill' => $prefill,
            'lecture' => ! $this->modifiable($suggestion, $agent),
            'agent' => $agent,
            'types' => TypeClientSoupcon::cases(),
            'niveaux' => NiveauRisqueSoupcon::cases(),
            'canaux' => CanalSoupcon::cases(),
            'indicateursListe' => IndicateurSoupcon::cases(),
            'avis' => AvisTechniqueSoupcon::cases(),
            'detectes' => $suggestion->indicateurs_detectes ?? [],
        ]);
    }

    public function enregistrerBrouillon(EnregistrerDossierSoupconRequest $requete, SuggestionSoupcon $suggestion): RedirectResponse
    {
        $agent = $this->controleur();
        $this->verifierPerimetre($suggestion, $agent);
        abort_unless($this->modifiable($suggestion, $agent), 403);

        $dossier = $this->sauvegarder($requete, $suggestion, $agent);
        Consignateur::enregistrer('agent', $agent->id, 'dossier_soupcon_brouillon', 'dossier_analyse_soupcon', $dossier->id);

        return redirect()->route('controleur.soupcons.ouvrir', $suggestion)
            ->with('statut', 'Brouillon enregistré. Rien n\'est transmis tant que vous n\'avez pas cliqué sur « Transmettre au responsable d\'agence ».');
    }

    public function transmettre(EnregistrerDossierSoupconRequest $requete, SuggestionSoupcon $suggestion): RedirectResponse
    {
        $agent = $this->controleur();
        $this->verifierPerimetre($suggestion, $agent);
        abort_unless($this->modifiable($suggestion, $agent), 403);

        $dossier = $this->sauvegarder($requete, $suggestion, $agent);
        $dossier->update(['statut' => StatutDossierSoupcon::Transmise, 'transmis_le' => now()]);
        $suggestion->update(['statut' => StatutSuggestionSoupcon::Transmise, 'controleur_id' => $agent->id]);

        Consignateur::enregistrer('agent', $agent->id, 'dossier_soupcon_transmis', 'dossier_analyse_soupcon', $dossier->id);

        return redirect()->route('controleur.tableau-de-bord.index')
            ->with('statut', 'Dossier '.$dossier->reference().' transmis au responsable d\'agence concerné.');
    }

    public function ecarter(SuggestionSoupcon $suggestion): RedirectResponse
    {
        $agent = $this->controleur();
        $this->verifierPerimetre($suggestion, $agent);
        abort_unless($this->modifiable($suggestion, $agent), 403);

        $suggestion->update(['statut' => StatutSuggestionSoupcon::Ecartee, 'controleur_id' => $agent->id]);
        Consignateur::enregistrer('agent', $agent->id, 'suggestion_soupcon_ecartee', 'suggestion_soupcon', $suggestion->id);

        return redirect()->route('controleur.tableau-de-bord.index')->with('statut', 'Suggestion écartée.');
    }

    private function sauvegarder(EnregistrerDossierSoupconRequest $requete, SuggestionSoupcon $suggestion, Agent $agent): DossierAnalyseSoupcon
    {
        $donnees = $requete->validated();

        $dossier = DossierAnalyseSoupcon::updateOrCreate(
            ['suggestion_soupcon_id' => $suggestion->id],
            [
                'client_id' => $suggestion->client_id,
                'controleur_id' => $agent->id,
                'type_client' => $donnees['type_client'],
                'niveau_risque' => $donnees['niveau_risque'],
                'dates_operations' => $donnees['dates_operations'] ?? [],
                'montants_concernes' => array_map('floatval', $donnees['montants_concernes'] ?? []),
                'canal' => $donnees['canal'] ?? null,
                'resume_faits' => $donnees['resume_faits'] ?? null,
                'indicateurs' => $donnees['indicateurs'] ?? [],
                'indicateur_autre_texte' => in_array(IndicateurSoupcon::Autres->value, $donnees['indicateurs'] ?? [], true)
                    ? ($donnees['indicateur_autre_texte'] ?? null) : null,
                'analyse_controleur' => $donnees['analyse_controleur'] ?? null,
                'avis_technique_controleur' => $donnees['avis_technique_controleur'] ?? null,
            ],
        );

        if ($suggestion->statut === StatutSuggestionSoupcon::Nouvelle) {
            $suggestion->update(['statut' => StatutSuggestionSoupcon::EnAnalyse, 'controleur_id' => $agent->id]);
        }

        return $dossier;
    }

    private function controleur(): Agent
    {
        return auth('agent')->user();
    }

    /** Une suggestion d'un autre réseau n'existe pas pour ce contrôleur : 404, jamais 403. */
    private function verifierPerimetre(SuggestionSoupcon $suggestion, Agent $agent): void
    {
        abort_unless($suggestion->reseau_id === $agent->reseauId(), 404);
    }

    private function modifiable(SuggestionSoupcon $suggestion, Agent $agent): bool
    {
        return in_array($suggestion->statut, [StatutSuggestionSoupcon::Nouvelle, StatutSuggestionSoupcon::EnAnalyse], true)
            && ($suggestion->controleur_id === null || $suggestion->controleur_id === $agent->id);
    }
}
