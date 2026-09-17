<?php

namespace App\Http\Controllers\Agent\Tableau;

use App\Enums\StatutAlerte;
use App\Enums\StatutDeclarationCentif;
use App\Enums\StatutVerificationNpi;
use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\DeclarationCentif;
use App\Models\Operation;
use App\Services\Contexte\ContexteReseau;
use Illuminate\View\View;

class TableauBordController extends Controller
{
    /**
     * Une page, quatre blocs pour la conformité (§7) ; vue simplifiée pour le guichet
     * (§14) — aucune donnée de filtrage/alerte n'atteint jamais ce dernier (problème 6).
     */
    public function index(ContexteReseau $contexte): View
    {
        $agent = auth('agent')->user();
        $reseauId = $contexte->reseauId();

        if (in_array($agent->role->value, ['responsable_lbcft', 'direction'], true)) {
            $alertesOuvertes = Alerte::whereHas('client', fn ($q) => $q->where('reseau_id', $reseauId))
                ->where('statut', '!=', StatutAlerte::Traitee)
                ->orderByRaw("CASE gravite WHEN 'critique' THEN 0 WHEN 'attention' THEN 1 ELSE 2 END")
                ->get();

            return view('agent.tableau.conformite', [
                'dossiersACompleter' => Client::with(['personnePhysique', 'personneMorale'])
                    ->where('reseau_id', $reseauId)
                    ->where('score_completude_kyc', '<', 100)
                    ->oldest()
                    ->limit(10)
                    ->get(),
                'alertesDuJour' => $alertesOuvertes->whereNotIn('type', ['fractionnement_guichet', 'fractionnement_multi_agences'])->take(15),
                'seuilsEtFractionnements' => $alertesOuvertes->whereIn('type', ['fractionnement_guichet', 'fractionnement_multi_agences']),
                'declarationsAVenir' => DeclarationCentif::whereHas('client', fn ($q) => $q->where('reseau_id', $reseauId))
                    ->where('statut', StatutDeclarationCentif::APreparer)
                    ->get(),
                'npiEnAttente' => Client::with(['personnePhysique', 'personneMorale'])
                    ->where('reseau_id', $reseauId)
                    ->where('statut_verification_npi', StatutVerificationNpi::EnAttenteConnexion->value)
                    ->limit(10)
                    ->get(),
            ]);
        }

        return view('agent.tableau.guichet', [
            'operationsDuJour' => Operation::where('agence_id', $agent->agence_id)
                ->whereDate('effectuee_le', today())
                ->count(),
            'clientsACompleter' => Client::with(['personnePhysique', 'personneMorale'])
                ->where('reseau_id', $reseauId)
                ->where('score_completude_kyc', '<', 100)
                ->limit(10)
                ->get(),
        ]);
    }
}
