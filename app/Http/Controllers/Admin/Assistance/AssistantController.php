<?php

namespace App\Http\Controllers\Admin\Assistance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Assistance\PoserQuestionRequest;
use App\Services\Assistance\GestionnaireAssistant;
use Illuminate\Http\JsonResponse;

class AssistantController extends Controller
{
    public function repondre(PoserQuestionRequest $request, GestionnaireAssistant $gestionnaire): JsonResponse
    {
        $resultat = $gestionnaire->traiter(
            $request->user('admin'),
            $request->validated('question'),
            $request->validated('ecran'),
        );

        return response()->json($resultat);
    }
}
