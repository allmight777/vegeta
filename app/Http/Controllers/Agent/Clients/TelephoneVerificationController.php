<?php

namespace App\Http\Controllers\Agent\Clients;

use App\Http\Controllers\Controller;
use App\Services\Audit\Consignateur;
use App\Services\Contexte\ContexteReseau;
use App\Services\Kyc\VerificateurTelephoneExistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Vérification au blur (Alpine fetch), même principe que NpiVerificationController :
 * ne fait que signaler un doublon probable au sein du réseau de l'agent connecté,
 * jamais une consultation de la fiche complète trouvée. Le numéro de téléphone n'est
 * jamais journalisé en clair.
 */
class TelephoneVerificationController extends Controller
{
    public function verifier(Request $request, VerificateurTelephoneExistant $verificateur, ContexteReseau $contexte): JsonResponse
    {
        $request->validate([
            'telephone' => ['required', 'string', 'max:20'],
            'nom_saisi' => ['nullable', 'string', 'max:255'],
            'client_id_actuel' => ['nullable', 'string', 'max:36'],
        ]);

        $resultat = $verificateur->verifier(
            $request->string('telephone')->toString(),
            $contexte->reseauId(),
            $request->input('nom_saisi'),
            $request->input('client_id_actuel'),
        );

        Consignateur::enregistrer('agent', auth('agent')->id(), 'verification_telephone', null, null);

        return response()->json([
            'deja_enregistre' => $resultat->dejaEnregistre,
            'avertissement_nom' => $resultat->avertissementNom,
        ]);
    }
}
