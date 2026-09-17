<?php

namespace App\Http\Controllers\Agent\Clients;

use App\Contracts\VerificateurNpi;
use App\Http\Controllers\Controller;
use App\Services\Audit\Consignateur;
use App\Services\Empreinte\ComparateurEmpreinte;
use App\Services\Empreinte\GenerateurEmpreinte;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Vérification au blur (Alpine fetch), en plus de la règle serveur obligatoire
 * (NpiValideRegle) sur le FormRequest — jamais seulement côté client
 * (06_PROMPT_FORMULAIRE_CLIENT_ENRICHI §6.2). Le NPI n'est jamais journalisé en clair.
 */
class NpiVerificationController extends Controller
{
    public function verifier(Request $request, VerificateurNpi $verificateur, GenerateurEmpreinte $generateur, ComparateurEmpreinte $comparateur): JsonResponse
    {
        $request->validate(['npi' => ['required', 'string', 'max:20']]);

        $resultat = $verificateur->verifier($request->string('npi'));

        $avertissementNom = null;
        if ($resultat->estValide && filled($resultat->nomOfficiel) && filled($request->input('nom_saisi'))) {
            $score = $comparateur->dice(
                $generateur->encoderNom((string) $request->input('nom_saisi')),
                $generateur->encoderNom($resultat->nomOfficiel),
            );

            if ($score < 0.7) {
                $avertissementNom = 'Le nom saisi diffère sensiblement du nom officiel associé à ce NPI.';
            }
        }

        Consignateur::enregistrer('agent', auth('agent')->id(), 'verification_npi', null, null);

        return response()->json([
            'est_valide' => $resultat->estValide,
            'avertissement_nom' => $avertissementNom,
        ]);
    }
}
