<?php

// app/Http/Controllers/Agent/Operations/OperationController.php

namespace App\Http\Controllers\Agent\Operations;

use App\Enums\CanalOperation;
use App\Enums\StatutCompte;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Operations\CreerOperationRequest;
use App\Models\Compte;
use App\Models\Operation;
use App\Services\Audit\Consignateur;
use App\Services\Contexte\ContexteReseau;
use App\Services\Detection\DetecteurFractionnement;
use App\Services\Operations\DetecteurPlafondInterAgences;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OperationController extends Controller
{
    public function creer(ContexteReseau $contexte): View
    {
        $comptes = Compte::with([
            'client.personnePhysique',
            'client.personneMorale',
            'agence',
        ])
            ->whereHas('client', fn ($q) => $q->where('reseau_id', $contexte->reseauId()))
            ->get();

        return view('agent.operations.creer', [
            'comptes' => $comptes,
            'urlLookupClient' => route('agent.clients.lookup'),
        ]);
    }

    public function stocker(
        CreerOperationRequest $request,
        DetecteurFractionnement $detecteur,
        DetecteurPlafondInterAgences $detecteurPlafond
    ): RedirectResponse {
        $donnees = $request->validated();
        $compte = Compte::with('client.personnePhysique', 'client.personneMorale')->findOrFail($donnees['compte_id']);
        $agent = auth('agent')->user();

        // 1. Vérification préalable : pièce d'identité expirée (personne physique)
        $client = $compte->client;

        if ($client->type->value === 'personne_physique') {
            $pp = $client->personnePhysique;
            $expiration = $pp?->piece_identite_expiration;

            if ($expiration !== null) {
                try {
                    $dateExpiration = $expiration instanceof Carbon
                        ? $expiration
                        : Carbon::parse((string) $expiration);
                } catch (\Throwable) {
                    $dateExpiration = null;
                }

                if ($dateExpiration !== null && $dateExpiration->isPast()) {
                    Consignateur::enregistrer(
                        'agent',
                        $agent->id,
                        'operation_refusee_piece_expiree',
                        'compte',
                        $compte->id,
                        ['date_expiration' => $dateExpiration->toDateString()]
                    );

                    return redirect()
                        ->route('agent.operations.creer')
                        ->withErrors([
                            'piece_expiree' => 'Pièce d\'identité expirée le '.$dateExpiration->format('d/m/Y').'. Le client doit renouveler sa pièce avant toute opération.',
                        ])
                        ->withInput();
                }
            }
        }

        // 2. Compte gelé — décision tracée d'un responsable, refus immédiat sans détail
        //    de motif transmis au caissier (Loi art. 89 à 91 : gel immédiat, sans informer
        //    le titulaire ; le caissier n'a pas à connaître la raison du gel).
        if ($compte->statut === StatutCompte::Gele) {
            Consignateur::enregistrer('agent', $agent->id, 'operation_refusee_compte_gele', 'compte', $compte->id);

            return redirect()->route('agent.operations.creer')
                ->with('statut', 'Opération en attente de validation par le service conformité. Référence : OP-'.Str::upper(Str::random(8)));
        }

        // 3. Contrôle de conformité (existant)
        if (! $agent->can('peutValiderOperation', $client)) {
            Consignateur::enregistrer('agent', $agent->id, 'tentative_operation_refusee', 'compte', $compte->id);

            return redirect()->route('agent.operations.creer')
                ->with('statut', 'Opération en attente de validation par le service conformité. Référence : OP-'.Str::upper(Str::random(8)));
        }

        // 4. Enregistrement de l'opération
        $operation = Operation::create([
            'compte_id' => $compte->id,
            'agence_id' => $agent->agence_id,
            'agent_id' => $agent->id,
            'type' => $donnees['type'],
            'montant' => $donnees['montant'],
            'mode_paiement' => $donnees['mode_paiement'],
            'devise_code' => 'XOF',
            'effectuee_le' => $donnees['effectuee_le'] ?? now(),
            'canal' => CanalOperation::Guichet,
        ]);

        // 5. Détection de fractionnement intra-agence (existant)
        $detecteur->analyserApresOperation($operation->fresh(['compte']));

        // 6. Contrôle du cumul inter-agences sur la journée
        //    Le caissier n'est JAMAIS notifié — seul le responsable d'agence
        //    de l'agence de saisie reçoit un e-mail + une alerte dans son espace.
        if ($donnees['mode_paiement'] === 'especes') {
            $detecteurPlafond->analyserCumulJournalier($operation->fresh(['compte.client.identite']));
        }

        $compte->update([
            'statut' => $compte->statut === StatutCompte::Dormant ? StatutCompte::Actif : $compte->statut,
            'derniere_operation_le' => $operation->effectuee_le,
        ]);

        Consignateur::enregistrer('agent', $agent->id, 'saisie_operation', 'operation', $operation->id);

        return redirect()->route('agent.operations.creer')
            ->with('statut', 'Opération enregistrée. Référence : OP-'.Str::upper(substr($operation->id, 0, 8)));
    }
}
