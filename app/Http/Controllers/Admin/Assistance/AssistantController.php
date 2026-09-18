<?php

namespace App\Http\Controllers\Admin\Assistance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Assistance\PoserQuestionRequest;
use App\Services\Assistance\GestionnaireAssistant;
use App\Services\Assistance\OutilsParRole;
use Illuminate\Http\JsonResponse;

class AssistantController extends Controller
{
    public function repondre(PoserQuestionRequest $request, GestionnaireAssistant $gestionnaire, OutilsParRole $outilsParRole): JsonResponse
    {
        $admin = $request->user('admin');

        $resultat = $gestionnaire->traiter(
            $admin,
            $request->validated('question'),
            $request->validated('ecran'),
            [],
            $outilsParRole->pour($admin),
        );

        return response()->json($resultat);
    }
}
