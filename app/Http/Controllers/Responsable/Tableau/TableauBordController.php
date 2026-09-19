<?php

namespace App\Http\Controllers\Responsable\Tableau;

use App\Enums\StatutAlerte;
use App\Enums\StatutDeclarationCentif;
use App\Enums\StatutVerificationNpi;
use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\DeclarationCentif;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TableauBordController extends Controller
{
    /**
     * Tableau de bord de conformité : dossiers à compléter, alertes du jour,
     * seuils/fractionnements, déclarations CENTIF à venir, NPI en attente — repris à
     * l'identique de l'ancien Agent\Tableau\TableauBordController (branche
     * responsable_lbcft/direction), désormais scopé à l'agence du responsable connecté et
     * non plus à tout le réseau (09_PROMPT_TROIS_PROFILS §4).
     */
    public function index(): View
    {
        $agenceId = Auth::guard('agent')->user()->agence_id;
        $clientsDeLAgence = Client::deLAgence($agenceId);
        $clientIdsDeLAgence = (clone $clientsDeLAgence)->pluck('id')->all();

        // Une alerte de cumul est déclenchée par la dernière opération, qui peut avoir eu
        // lieu dans une autre agence — c'est même tout l'intérêt de la détection
        // multi-agences. Le responsable doit donc voir les alertes de toutes les fiches
        // de ses membres, y compris celles ouvertes ailleurs. Les dossiers à compléter,
        // eux, restent strictement ceux de son agence : ce sont ses tâches à lui.
        $identiteIds = Client::whereIn('id', $clientIdsDeLAgence)
            ->whereNotNull('identite_id')
            ->pluck('identite_id')
            ->all();

        $clientIdsSurveilles = array_values(array_unique(array_merge(
            $clientIdsDeLAgence,
            Client::whereIn('identite_id', $identiteIds)->pluck('id')->all(),
        )));

        // Tout ce qui relève d'un seuil ou d'un cumul va dans le bloc dédié,
        // le reste (filtrage, NPI) reste dans les alertes du jour.
        $typesFractionnement = [
            'fractionnement_guichet',
            'fractionnement_multi_agences',
            'plafond_quotidien_approche',
            'plafond_quotidien_depasse',
        ];

        $alertesOuvertes = Alerte::whereIn('client_id', $clientIdsSurveilles)
            ->where('statut', '!=', StatutAlerte::Traitee)
            ->orderByRaw("CASE gravite WHEN 'critique' THEN 0 WHEN 'attention' THEN 1 ELSE 2 END")
            ->get();

        return view('responsable.tableau.index', [
            'dossiersACompleter' => (clone $clientsDeLAgence)->with(['personnePhysique', 'personneMorale'])
                ->where('score_completude_kyc', '<', 100)
                ->oldest()
                ->limit(10)
                ->get(),
            // Comparaison sur ->value : la colonne est castée en enum, une comparaison
            // directe avec une chaîne échoue silencieusement et vide le bloc.
            'alertesDuJour' => $alertesOuvertes->reject(fn ($alerte) => in_array($alerte->type->value, $typesFractionnement, true))->take(15),
            'seuilsEtFractionnements' => $alertesOuvertes->filter(fn ($alerte) => in_array($alerte->type->value, $typesFractionnement, true))->values(),
            'declarationsAVenir' => DeclarationCentif::whereIn('client_id', $clientIdsSurveilles)
                ->where('statut', StatutDeclarationCentif::APreparer)
                ->get(),
            'npiEnAttente' => (clone $clientsDeLAgence)->with(['personnePhysique', 'personneMorale'])
                ->where('statut_verification_npi', StatutVerificationNpi::EnAttenteConnexion->value)
                ->limit(10)
                ->get(),
        ]);
    }
}
