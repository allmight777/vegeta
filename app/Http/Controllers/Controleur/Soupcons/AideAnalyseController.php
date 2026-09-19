<?php

namespace App\Http\Controllers\Controleur\Soupcons;

use App\Http\Controllers\Controller;
use App\Models\SuggestionSoupcon;
use App\Services\Audit\Consignateur;
use App\Services\Conformite\AssistantSoupcon;
use Illuminate\Http\JsonResponse;

/** « Aide à l'analyse » (18_PROMPT §5.3) : explique pourquoi le système a suspecté ce profil. */
class AideAnalyseController extends Controller
{
    public function __invoke(SuggestionSoupcon $suggestion, AssistantSoupcon $assistant): JsonResponse
    {
        $agent = auth('agent')->user();
        abort_unless($suggestion->reseau_id === $agent->reseauId(), 404);

        $resultat = $assistant->expliquerPourControleur($suggestion, $agent);
        Consignateur::enregistrer('agent', $agent->id, 'aide_analyse_soupcon', 'suggestion_soupcon', $suggestion->id);

        return response()->json($resultat);
    }
}
