<?php

namespace App\Http\Controllers\Agent\Conformite;

use App\Enums\StatutAlerte;
use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Services\Audit\Consignateur;
use Illuminate\Http\RedirectResponse;

/**
 * Traitement manuel d'une alerte "NPI invalide après vérification différée"
 * (07_PROMPT_MODE_DEGRADE_NPI_OCR §2.5) — pas une refonte générale de la gestion des
 * alertes, seulement ce cas précis : « l'outil recommande, l'humain décide ».
 */
class AlerteNpiController extends Controller
{
    public function traiter(Alerte $alerte): RedirectResponse
    {
        $alerte->update([
            'statut' => StatutAlerte::Traitee,
            'traitee_par_agent_id' => auth('agent')->id(),
            'traitee_le' => now(),
        ]);

        Consignateur::enregistrer('agent', auth('agent')->id(), 'traitement_alerte_npi', 'alerte', $alerte->id);

        return redirect()->route('agent.tableau-de-bord.index')->with('statut', 'Alerte NPI marquée comme traitée.');
    }
}
