<?php

namespace App\Http\Controllers\Admin\Tableau;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use App\Services\Assistance\ResumeDuJourAdmin;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class TableauBordController extends Controller
{
    public function index(ResumeDuJourAdmin $resume): View
    {
        $admin = auth('admin')->user();

        // Jamais d'écran cassé à cause du résumé : repli sur une phrase claire.
        try {
            $resumeDuJour = $resume->composer($admin);
        } catch (Throwable) {
            $resumeDuJour = [['texte' => 'Le résumé du jour n\'est pas disponible pour le moment.', 'lien' => null, 'libelle_lien' => null]];
        }

        $agentsParReseau = DB::table('agents')
            ->join('agences', 'agences.id', '=', 'agents.agence_id')
            ->where('agents.actif', true)
            ->groupBy('agences.reseau_id')
            ->selectRaw('agences.reseau_id as reseau_id, count(*) as total')
            ->pluck('total', 'reseau_id');

        $reseaux = Reseau::query()
            ->when(! $admin->estAdminPlateforme(), fn ($q) => $q->where('id', $admin->reseau_id))
            ->withCount('agences')
            ->orderBy('nom')
            ->get()
            ->each(fn (Reseau $reseau) => $reseau->agents_count = (int) ($agentsParReseau[$reseau->id] ?? 0));

        return view('admin.tableau.index', [
            'resumeDuJour' => $resumeDuJour,
            'reseaux' => $reseaux,
            'nombreReseaux' => Reseau::count(),
            'nombreAgences' => Agence::count(),
            'nombreAdmins' => Admin::count(),
            'nombreAgents' => Agent::where('actif', true)->count(),
        ]);
    }
}
