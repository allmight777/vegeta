<?php

namespace App\Http\Controllers\Agent\Clients;

use App\Http\Controllers\Controller;
use App\Services\Audit\Consignateur;
use App\Services\Contexte\ContexteReseau;
use App\Services\Kyc\DetecteurDoublonEmpreinte;
use App\Services\Kyc\DetecteurIncoherencesSaisie;
use App\Services\Kyc\SuggesteurNormalisationActivite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Copilote de saisie (12_PROMPT_IA_INTEGREE_PROFONDE §4) — point 1, détection
 * d'incohérences. Déclenché au blur des champs concernés (jamais à chaque frappe).
 *
 * Ne reçoit QUE des caractéristiques dérivées, calculées côté navigateur avant
 * l'envoi — jamais un nom, une date brute, un NPI ou une adresse (CLAUDE.md §5).
 */
class CopiloteSaisieController extends Controller
{
    public function verifierCoherence(Request $request, DetecteurIncoherencesSaisie $detecteur): JsonResponse
    {
        $donnees = $request->validate([
            'age_calcule' => ['nullable', 'integer', 'min:0', 'max:130'],
            'profession' => ['nullable', 'string', 'max:255'],
            'ratio_depot_revenu' => ['nullable', 'numeric', 'min:0'],
            'piece_expiree' => ['boolean'],
        ]);

        $resultat = $detecteur->verifier(
            $donnees['age_calcule'] ?? null,
            $donnees['profession'] ?? null,
            $donnees['ratio_depot_revenu'] ?? null,
            (bool) ($donnees['piece_expiree'] ?? false),
        );

        return response()->json($resultat);
    }

    /**
     * Alerte doublon par empreinte (16_PROMPT §2.1). Le nom saisi n'est ni journalisé ni
     * renvoyé : la réponse ne contient que l'agence du dossier proche.
     */
    public function verifierDoublon(Request $request, DetecteurDoublonEmpreinte $detecteur, ContexteReseau $contexte): JsonResponse
    {
        $donnees = $request->validate([
            'nom' => ['nullable', 'string', 'max:255'],
            'prenoms' => ['nullable', 'string', 'max:255'],
            'date_naissance' => ['nullable', 'date'],
            'client_id_actuel' => ['nullable', 'string', 'max:36'],
        ]);

        $reseauId = $contexte->reseauId();
        $trouve = $reseauId === null
            ? null
            : $detecteur->chercher($donnees['prenoms'] ?? null, $donnees['nom'] ?? null, $donnees['date_naissance'] ?? null, $reseauId, $donnees['client_id_actuel'] ?? null);

        Consignateur::enregistrer('agent', auth('agent')->id(), 'copilote_verification_doublon');

        if ($trouve === null) {
            return response()->json(['doublon' => false]);
        }

        $lieu = $trouve['agence'] ? " ({$trouve['agence']})" : '';

        return response()->json([
            'doublon' => true,
            'message' => "Un dossier très proche existe déjà dans ce réseau{$lieu}. Vérifiez avant de créer un doublon.",
            'source' => 'empreinte_locale',
        ]);
    }

    /**
     * Normalisation des champs d'activité (16_PROMPT §2.3) : simple comptage, sans IA.
     */
    public function suggererNormalisation(Request $request, SuggesteurNormalisationActivite $suggesteur, ContexteReseau $contexte): JsonResponse
    {
        $donnees = $request->validate([
            'champ' => ['required', 'in:profession,activite_1,activite_2'],
            'valeur' => ['nullable', 'string', 'max:255'],
        ]);

        $reseauId = $contexte->reseauId();
        $suggestion = $reseauId === null ? null : $suggesteur->suggerer($donnees['champ'], (string) ($donnees['valeur'] ?? ''), $reseauId);

        if ($suggestion === null) {
            return response()->json(['suggestion' => null]);
        }

        $n = $suggestion['nombre'];

        return response()->json([
            'suggestion' => [
                'valeur' => $suggestion['valeur'],
                'message' => "{$n} dossier".($n > 1 ? 's' : '')." de ce réseau utilisent « {$suggestion['valeur']} » — utiliser cette orthographe ?",
            ],
        ]);
    }
}
