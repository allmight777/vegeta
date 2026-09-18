<?php

namespace App\Http\Controllers\Agent\Assistance;

use App\Enums\StatutEscalade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Assistance\EscaladerRequest;
use App\Http\Requests\Agent\Assistance\PoserQuestionRequest;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\EscaladeAssistantIa;
use App\Services\Assistance\GestionnaireAssistant;
use App\Services\Assistance\OutilsParRole;
use App\Services\Audit\Consignateur;
use App\Services\Contexte\ContexteReseau;
use App\Support\MotsInterditsConformite;
use Illuminate\Http\JsonResponse;

class AssistantController extends Controller
{
    private const QUESTION_FILTREE = '[question filtrée — contenu non enregistré]';

    public function repondre(PoserQuestionRequest $request, GestionnaireAssistant $gestionnaire, ContexteReseau $contexte, OutilsParRole $outilsParRole): JsonResponse
    {
        $agent = $request->user('agent');
        $donneesEcran = $this->resoudreDonneesEcran($request, $contexte);

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
     * Escalade explicite déclenchée par un clic de l'agent — jamais automatique
     * (08_PROMPT §5). La question est revérifiée par le même filtre que les réponses
     * avant tout enregistrement.
     */
    public function escalader(EscaladerRequest $request): JsonResponse
    {
        $agent = $request->user('agent');
        $question = $request->validated('question');

        $escalade = EscaladeAssistantIa::create([
            'agent_id' => $agent->id,
            'role_agent' => $agent->role->value,
            'question' => MotsInterditsConformite::contient($question) !== null ? self::QUESTION_FILTREE : $question,
            'contexte_ecran' => $request->validated('ecran'),
            'reponse_ia' => $request->validated('reponse_ia'),
            'statut' => StatutEscalade::EnAttente,
        ]);

        Consignateur::enregistrer('agent', $agent->id, 'escalade_assistant_ia', 'escalade_assistant_ia', $escalade->id);

        return response()->json(['statut' => 'transmise']);
    }

    /**
     * Le contrôleur résout lui-même les données d'écran côté serveur — jamais depuis un
     * payload navigateur — en plus de la liste fermée déjà appliquée par
     * ConstructeurContexteIa (défense en profondeur, 08_PROMPT §5).
     *
     * @return array<string, mixed>
     */
    private function resoudreDonneesEcran(PoserQuestionRequest $request, ContexteReseau $contexte): array
    {
        $donnees = [];
        $reseauId = $contexte->reseauId();

        if ($clientId = $request->validated('client_id')) {
            $client = Client::with(['personnePhysique', 'personneMorale'])
                ->where('reseau_id', $reseauId)
                ->find($clientId);

            $personne = $client?->personneMorale ?? $client?->personnePhysique;
            $donnees['champs_manquants'] = $personne?->champs_manquants ?? [];
        }

        if ($alerteId = $request->validated('alerte_id')) {
            $alerte = Alerte::whereHas('client', fn ($q) => $q->where('reseau_id', $reseauId))->find($alerteId);
            $donnees['explication_alerte'] = $alerte?->explication_texte;
        }

        return $donnees;
    }
}
