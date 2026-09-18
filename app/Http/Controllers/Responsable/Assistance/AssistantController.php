<?php

namespace App\Http\Controllers\Responsable\Assistance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Assistance\PoserQuestionRequest;
use App\Models\Alerte;
use App\Models\Client;
use App\Services\Assistance\GestionnaireAssistant;
use App\Services\Assistance\OutilsParRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Assistant du responsable d'agence (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §2.1) :
 * même orchestrateur que les autres espaces, mais un jeu d'outils différent
 * (OutilsParRole) — c'est ce jeu d'outils qui définit ce que l'IA peut faire ici, pas le
 * prompt système. Pas d'escalade : ce rôle est déjà la cible des escalades des caissiers
 * (Responsable\Assistance\EscaladesController).
 */
class AssistantController extends Controller
{
    public function repondre(PoserQuestionRequest $request, GestionnaireAssistant $gestionnaire, OutilsParRole $outilsParRole): JsonResponse
    {
        $agent = Auth::guard('agent')->user();
        $donneesEcran = $this->resoudreDonneesEcran($request, $agent->agence_id);

        $resultat = $gestionnaire->traiter(
            $agent,
            $request->validated('question'),
            $request->validated('ecran'),
            $donneesEcran,
            $outilsParRole->pour($agent),
        );

        return response()->json($resultat);
    }

    /**
     * Même défense en profondeur que Agent\Assistance\AssistantController, mais scopée à
     * l'agence du responsable plutôt qu'à tout le réseau (09_PROMPT_TROIS_PROFILS §4).
     *
     * @return array<string, mixed>
     */
    private function resoudreDonneesEcran(PoserQuestionRequest $request, int $agenceId): array
    {
        $donnees = [];

        if ($clientId = $request->validated('client_id')) {
            $client = Client::deLAgence($agenceId)
                ->with(['personnePhysique', 'personneMorale'])
                ->find($clientId);

            $personne = $client?->personneMorale ?? $client?->personnePhysique;
            $donnees['champs_manquants'] = $personne?->champs_manquants ?? [];
        }

        if ($alerteId = $request->validated('alerte_id')) {
            $alerte = Alerte::whereHas('client', fn ($q) => $q->deLAgence($agenceId))->find($alerteId);
            $donnees['explication_alerte'] = $alerte?->explication_texte;
        }

        return $donnees;
    }
}
