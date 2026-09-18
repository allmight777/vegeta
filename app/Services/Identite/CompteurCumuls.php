<?php

namespace App\Services\Identite;

use App\Enums\TypeOperation;
use App\Models\CumulJournalier;
use App\Models\Operation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Entretient le cumul du jour par identité et par mode de paiement, à chaque opération.
 * Sans cet agrégat, vérifier le plafond obligerait à rebalayer toutes les opérations de
 * tous les comptes de la personne : intenable en temps réel au guichet.
 */
class CompteurCumuls
{
    public function enregistrer(Operation $operation): ?CumulJournalier
    {
        $identite = $operation->compte->client->identite;

        if ($identite === null) {
            return null;
        }

        $jour = $operation->effectuee_le->toDateString();
        $mode = $operation->mode_paiement->value;

        return DB::transaction(function () use ($identite, $jour, $mode) {
            // whereDate() et non where() : la colonne est castée en date, donc stockée
            // avec une heure à zéro — une comparaison stricte ne retrouverait jamais
            // la ligne du jour et tenterait de la réinsérer.
            $cumul = CumulJournalier::where('identite_id', $identite->id)
                ->where('mode_paiement', $mode)
                ->whereDate('jour', $jour)
                ->first();

            $operationsDuJour = $this->operationsDuJour($identite->clientIds(), $jour, $mode);

            $valeurs = [
                'total_depots' => (float) $operationsDuJour->where('type', TypeOperation::Depot)->sum('montant'),
                'total_retraits' => (float) $operationsDuJour->where('type', TypeOperation::Retrait)->sum('montant'),
                'nb_operations' => $operationsDuJour->count(),
                'nb_comptes' => $operationsDuJour->pluck('compte_id')->unique()->count(),
                'nb_agences' => $operationsDuJour->pluck('agence_id')->unique()->count(),
            ];

            if ($cumul === null) {
                return CumulJournalier::create($valeurs + [
                    'identite_id' => $identite->id,
                    'jour' => $jour,
                    'mode_paiement' => $mode,
                ]);
            }

            $cumul->update($valeurs);

            return $cumul->fresh();
        });
    }

    /**
     * Recalcul complet plutôt qu'incrément : une opération saisie avec une date passée,
     * ou rejouée par un scénario de démonstration, ne fausse jamais le compteur.
     *
     * @param  array<int, string>  $clientIds
     * @return Collection<int, Operation>
     */
    private function operationsDuJour(array $clientIds, string $jour, string $mode): Collection
    {
        return Operation::whereHas('compte', fn ($q) => $q->whereIn('client_id', $clientIds))
            ->where('mode_paiement', $mode)
            ->whereDate('effectuee_le', $jour)
            ->get();
    }
}
