<?php

namespace App\Http\Controllers\Agent\Clients;

use App\Http\Controllers\Controller;
use App\Services\Audit\Consignateur;
use App\Services\Kyc\SimulateurDepotMobileMonnaie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Vérification au blur (Alpine fetch), même principe que TelephoneVerificationController.
 * Contrairement à celui-ci, le nom retourné n'est PAS masqué : il ne s'agit pas de la
 * fiche d'un autre client interne mais d'un référentiel externe simulé (voir
 * SimulateurDepotMobileMonnaie) — exactement ce qu'affiche un vrai écran de confirmation
 * MTN/Moov/Celtiis, que le caissier doit pouvoir comparer visuellement au nom qu'il saisit.
 */
class SimulationDepotMobileMonnaieController extends Controller
{
    public function verifier(Request $request, SimulateurDepotMobileMonnaie $simulateur): JsonResponse
    {
        $request->validate([
            'telephone' => ['required', 'string', 'max:20'],
        ]);

        $resultat = $simulateur->simuler($request->string('telephone')->toString());

        Consignateur::enregistrer('agent', auth('agent')->id(), 'simulation_depot_mobile_monnaie', null, null);

        return response()->json([
            'trouve' => $resultat->trouve,
            'operateur' => $resultat->operateur?->value,
            'operateur_libelle' => $resultat->operateur?->libelle(),
            'nom_titulaire' => $resultat->nomTitulaire,
        ]);
    }
}
