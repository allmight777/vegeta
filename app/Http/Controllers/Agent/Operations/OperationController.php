<?php

namespace App\Http\Controllers\Agent\Operations;

use App\Enums\CanalOperation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Operations\CreerOperationRequest;
use App\Models\Compte;
use App\Models\Operation;
use App\Services\Audit\Consignateur;
use App\Services\Contexte\ContexteReseau;
use App\Services\Detection\DetecteurFractionnement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OperationController extends Controller
{
    public function creer(ContexteReseau $contexte): View
    {
        $comptes = Compte::with('client.personnePhysique', 'client.personneMorale')
            ->whereHas('client', fn ($q) => $q->where('reseau_id', $contexte->reseauId()))
            ->get();

        return view('agent.operations.creer', ['comptes' => $comptes]);
    }

    public function stocker(CreerOperationRequest $request, DetecteurFractionnement $detecteur): RedirectResponse
    {
        $donnees = $request->validated();
        $compte = Compte::with('client.personnePhysique', 'client.personneMorale')->findOrFail($donnees['compte_id']);
        $agent = auth('agent')->user();

        // Message guichet neutre dans les deux cas : jamais le motif réel (art. 63).
        if (! $agent->can('peutValiderOperation', $compte->client)) {
            Consignateur::enregistrer('agent', $agent->id, 'tentative_operation_refusee', 'compte', $compte->id);

            return redirect()->route('agent.operations.creer')
                ->with('statut', 'Opération en attente de validation par le service conformité. Référence : OP-'.Str::upper(Str::random(8)));
        }

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

        $detecteur->analyserApresOperation($operation->fresh(['compte']));

        $compte->update([
            'statut' => 'actif',
            'derniere_operation_le' => $operation->effectuee_le,
        ]);

        Consignateur::enregistrer('agent', $agent->id, 'saisie_operation', 'operation', $operation->id);

        return redirect()->route('agent.operations.creer')
            ->with('statut', 'Opération enregistrée. Référence : OP-'.Str::upper(substr($operation->id, 0, 8)));
    }
}
