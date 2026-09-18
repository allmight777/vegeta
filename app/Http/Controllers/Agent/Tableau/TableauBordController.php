<?php

namespace App\Http\Controllers\Agent\Tableau;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Operation;
use App\Services\Contexte\ContexteReseau;
use Illuminate\View\View;

class TableauBordController extends Controller
{
    /**
     * Tableau de bord du caissier : uniquement l'activité de sa caisse et les dossiers à
     * compléter — aucune donnée de filtrage/alerte n'atteint jamais ce rôle (Loi art. 63,
     * problème 6). Le tableau de bord de conformité est désormais sous
     * Responsable\Tableau\TableauBordController (09_PROMPT_TROIS_PROFILS §4).
     */
    public function index(ContexteReseau $contexte): View
    {
        $agent = auth('agent')->user();

        $finSemaine = today();
        $debutSemaine = $finSemaine->copy()->subDays(6);

        $operationsParJour = Operation::where('agence_id', $agent->agence_id)
            ->whereBetween('effectuee_le', [$debutSemaine->copy()->startOfDay(), $finSemaine->copy()->endOfDay()])
            ->get()
            ->groupBy(fn (Operation $operation) => $operation->effectuee_le->toDateString());

        $joursSemaine = [];
        $operationsSemaine = [];

        for ($jour = $debutSemaine->copy(); $jour->lte($finSemaine); $jour->addDay()) {
            $joursSemaine[] = $jour->translatedFormat('D');
            $operationsSemaine[] = $operationsParJour->get($jour->toDateString(), collect())->count();
        }

        return view('agent.tableau.index', [
            'operationsDuJour' => Operation::where('agence_id', $agent->agence_id)
                ->whereDate('effectuee_le', today())
                ->count(),
            'operationsSemaine' => $operationsSemaine,
            'joursSemaine' => $joursSemaine,
            'clientsACompleter' => Client::with(['personnePhysique', 'personneMorale'])
                ->where('reseau_id', $contexte->reseauId())
                ->where('score_completude_kyc', '<', 100)
                ->limit(10)
                ->get(),
        ]);
    }
}
