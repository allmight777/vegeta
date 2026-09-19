<?php

// app/Http/Controllers/Agent/Clients/ClientLookupController.php

namespace App\Http\Controllers\Agent\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Compte;
use App\Services\Contexte\ContexteReseau;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ClientLookupController extends Controller
{
    /**
     * Retourne les informations du client rattaché à un compte, pour pré-remplir
     * le formulaire d'opération au select : nom, prénoms, date de naissance, NPI,
     * pièce d'identité, expiration, filiation (père/mère).
     *
     * Aucune donnée sensible brute n'est renvoyée : le NPI est affiché sous forme
     * masquée, seule la validité de la pièce est calculée côté serveur.
     */
    public function afficher(Request $request, ContexteReseau $contexte): JsonResponse
    {
        $compteId = $request->string('compte_id')->toString();

        if ($compteId === '') {
            return response()->json(['erreur' => 'Compte manquant.'], 422);
        }

        $compte = Compte::with([
            'client.personnePhysique',
            'client.personneMorale',
        ])
            ->whereHas('client', fn ($q) => $q->where('reseau_id', $contexte->reseauId()))
            ->find($compteId);

        if ($compte === null) {
            return response()->json(['erreur' => 'Compte introuvable.'], 404);
        }

        $client = $compte->client;

        if ($client->type->value === 'personne_morale') {
            $pm = $client->personneMorale;

            return response()->json([
                'type' => 'personne_morale',
                'raison_sociale' => (string) $pm?->raison_sociale,
                'numero_rccm' => (string) $pm?->rccm,
                'numero_ifu' => (string) $pm?->ifu,
                'compte_numero' => $compte->numero,
                'agence' => $compte->agence?->nom,
                'alerte_piece' => null,
                'statut_conformite' => $client->statutConformiteAffichable() ? 'ok' : 'verification',
            ]);
        }

        $pp = $client->personnePhysique;

        if ($pp === null) {
            return response()->json(['erreur' => 'Fiche client incomplète.'], 422);
        }

        // Calcul de la validité de la pièce d'identité
        $alertePiece = null;
        $expiration = $pp->piece_identite_expiration;

        if ($expiration !== null) {
            if ($expiration instanceof Carbon) {
                $dateExpiration = $expiration;
            } else {
                try {
                    $dateExpiration = Carbon::parse((string) $expiration);
                } catch (\Throwable) {
                    $dateExpiration = null;
                }
            }

            if ($dateExpiration !== null) {
                if ($dateExpiration->isPast()) {
                    $alertePiece = [
                        'niveau' => 'expiree',
                        'message' => 'Pièce d\'identité EXPIRÉE le '.$dateExpiration->format('d/m/Y').'. Renouvellement obligatoire avant toute opération.',
                    ];
                } elseif (($jours = (int) ceil(now()->diffInDays($dateExpiration, false))) <= 30) {
                    $alertePiece = [
                        'niveau' => 'bientot',
                        'message' => 'Pièce d\'identité expire dans '.$jours.' jour(s) — à renouveler bientôt.',
                    ];
                }
            }
        }

        $statutPiece = match (true) {
            $alertePiece !== null => $alertePiece['niveau'],
            $expiration !== null => 'valide',
            default => 'inconnue',
        };

        return response()->json([
            'type' => 'personne_physique',
            'piece_statut' => $statutPiece,
            'nom' => (string) $pp->nom,
            'prenoms' => (string) $pp->prenoms,
            'nom_complet' => trim(($pp->prenoms ?? '').' '.($pp->nom ?? '')),
            'date_naissance' => $pp->date_naissance ? Carbon::parse($pp->date_naissance)->format('d/m/Y') : null,
            'lieu_naissance' => (string) $pp->lieu_naissance,
            'pays_naissance' => (string) $pp->pays_naissance,
            'nationalite' => (string) $pp->nationalite,
            'sexe' => (string) $pp->sexe,
            'pere' => (string) $pp->pere,
            'mere' => (string) $pp->mere,
            'profession' => (string) $pp->profession,
            'piece_type' => (string) $pp->piece_identite_type,
            'piece_numero_masque' => $this->masquer($pp->piece_identite_numero),
            'piece_expiration' => $expiration instanceof Carbon
                ? $expiration->format('d/m/Y')
                : ($expiration ? Carbon::parse((string) $expiration)->format('d/m/Y') : null),
            'npi_masque' => $this->masquer($pp->npi),
            'telephone' => (string) $pp->telephone,
            'compte_numero' => $compte->numero,
            'agence' => $compte->agence?->nom,
            'alerte_piece' => $alertePiece,
            'statut_conformite' => $client->statutConformiteAffichable() ? 'ok' : 'verification',
            'statut_npi' => $client->statut_verification_npi?->value,
        ]);
    }

    private function masquer(?string $valeur): ?string
    {
        $valeur = (string) $valeur;

        if ($valeur === '') {
            return null;
        }

        $longueur = mb_strlen($valeur);

        if ($longueur <= 4) {
            return str_repeat('•', $longueur);
        }

        return mb_substr($valeur, 0, 2).str_repeat('•', $longueur - 4).mb_substr($valeur, -2);
    }
}
