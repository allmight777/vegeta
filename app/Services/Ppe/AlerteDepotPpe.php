<?php

namespace App\Services\Ppe;

use App\Enums\GraviteAlerte;
use App\Enums\TypeAlerte;
use App\Mail\AlerteConformiteMail;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Operation;
use App\Services\Audit\Consignateur;
use Illuminate\Support\Facades\Mail;

/**
 * Tout dépôt sur le compte d'une PPE, quel qu'en soit le montant (même 1 XOF), alerte le
 * responsable de l'agence de saisie : alerte au tableau de bord + e-mail (Loi art. 29 :
 * surveillance renforcée). Le caissier n'en est jamais informé (Loi art. 63).
 */
class AlerteDepotPpe
{
    public function analyser(Operation $operation): ?Alerte
    {
        $client = $operation->compte?->client;

        if ($operation->type->value !== 'depot' || $client === null || ! $client->estPpe()) {
            return null;
        }

        $agence = $operation->agence;
        $mode = $operation->mode_paiement->value;
        $montant = number_format((float) $operation->montant, 0, ',', ' ');
        // Aucune donnée d'identité dans l'explication ni dans les faits.
        $explication = "Dépôt de {$montant} XOF ({$mode}) sur le compte d'une personne politiquement exposée. Surveillance renforcée requise.";

        $alerte = Alerte::create([
            'type' => TypeAlerte::DepotPpe,
            'gravite' => GraviteAlerte::Attention,
            'agence_id' => $operation->agence_id,
            'client_id' => $client->id,
            'explication_texte' => $explication,
            'faits' => [
                'operation_id' => $operation->id,
                'montant' => (float) $operation->montant,
                'mode_paiement' => $mode,
            ],
        ]);

        $responsables = Agent::where('agence_id', $operation->agence_id)
            ->where('role', 'responsable_agence')
            ->where('actif', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        foreach ($responsables as $responsable) {
            // Un SMTP indisponible ne doit jamais bloquer l'enregistrement de l'opération.
            try {
                Mail::to($responsable->email)->queue(new AlerteConformiteMail(
                    gravite: $alerte->gravite->value,
                    typeLibelle: $alerte->type->libelle(),
                    agenceNom: $agence?->nom ?? '—',
                    explication: $explication,
                    lienDashboard: route('responsable.tableau-de-bord.index'),
                ));
            } catch (\Throwable $e) {
                logger()->warning('Échec envoi alerte dépôt PPE', [
                    'alerte_id' => $alerte->id,
                    'responsable_id' => $responsable->id,
                    'erreur' => $e->getMessage(),
                ]);
            }
        }

        Consignateur::enregistrer('systeme', null, 'alerte_depot_ppe', 'alerte', $alerte->id, ['operation_id' => $operation->id]);

        return $alerte;
    }
}
