<?php

namespace App\Http\Controllers\Responsable\Assistance;

use App\Enums\StatutEscalade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Assistance\RepondreEscaladeRequest;
use App\Models\EscaladeAssistantIa;
use App\Services\Audit\Consignateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Traitement manuel des questions escaladées (08_PROMPT §5) — réservé au responsable
 * d'agence, même garde que le tableau de bord conformité (routes/responsable/*.php).
 * Une fois répondue, la paire alimente Services\Assistance\BaseConnaissances pour le
 * simulateur.
 */
class EscaladesController extends Controller
{
    public function index(): View
    {
        return view('responsable.assistance.escalades', [
            'escalades' => EscaladeAssistantIa::where('statut', StatutEscalade::EnAttente->value)
                ->oldest()
                ->get(),
        ]);
    }

    public function repondre(RepondreEscaladeRequest $request, EscaladeAssistantIa $escalade): RedirectResponse
    {
        $escalade->update([
            'reponse_responsable' => $request->validated('reponse_responsable'),
            'statut' => StatutEscalade::Traitee,
            'traitee_par_agent_id' => auth('agent')->id(),
            'traitee_le' => now(),
        ]);

        Consignateur::enregistrer('agent', auth('agent')->id(), 'reponse_escalade_assistant_ia', 'escalade_assistant_ia', $escalade->id);

        return redirect()->route('responsable.assistance.escalades.index')->with('statut', 'Réponse enregistrée — ajoutée à la base de connaissances.');
    }
}
