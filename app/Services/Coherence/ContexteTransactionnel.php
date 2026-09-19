<?php

namespace App\Services\Coherence;

use App\Enums\TypeOperation;
use App\Models\Client;
use App\Models\Operation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Agrégats transactionnels d'un client, calculés UNE SEULE FOIS puis partagés
 * entre tous les indicateurs.
 *
 * Sans ça, chaque indicateur relirait la table des opérations : sur une
 * campagne nocturne portant sur tout le parc, c'est le même piège que celui
 * corrigé dans MoteurFiltrage (rechargement de la table de listes à chaque
 * dossier).
 *
 * Ne manipule que des montants, des dates et des canaux — jamais une valeur
 * d'identité déchiffrée (CLAUDE.md §5).
 */
class ContexteTransactionnel
{
    /** @var Collection<int, Operation> */
    public readonly Collection $operations;

    public readonly float $revenusMensuelsDeclares;

    public readonly ?string $activiteDeclaree;

    private function __construct(Collection $operations, float $revenus, ?string $activite)
    {
        $this->operations = $operations;
        $this->revenusMensuelsDeclares = $revenus;
        $this->activiteDeclaree = $activite;
    }

    public static function pour(Client $client, int $fenetreJoursMax = 90): self
    {
        $client->loadMissing(['personnePhysique', 'comptes']);

        $operations = Operation::query()
            ->whereIn('compte_id', $client->comptes->pluck('id'))
            ->where('effectuee_le', '>=', now()->subDays($fenetreJoursMax))
            ->orderBy('effectuee_le')
            ->get();

        $personne = $client->personnePhysique;

        // profession, puis activités secondaires : on prend la première renseignée.
        $activite = collect([
            $personne?->profession,
            $personne?->activite_1,
            $personne?->activite_2,
        ])->first(fn ($valeur) => filled($valeur));

        return new self(
            $operations,
            (float) ($personne?->revenus_mensuels_estimes ?? 0),
            $activite === null ? null : Str::of((string) $activite)->ascii()->lower()->toString(),
        );
    }

    /**
     * @return Collection<int, Operation>
     */
    public function depuis(int $jours): Collection
    {
        $depuis = now()->subDays($jours);

        return $this->operations->filter(fn (Operation $o) => $o->effectuee_le >= $depuis)->values();
    }

    public function cumulDepots(int $jours): float
    {
        return (float) $this->depuis($jours)
            ->where('type', TypeOperation::Depot)
            ->sum(fn (Operation $o) => (float) $o->montant);
    }

    public function nombreOperations(int $jours): int
    {
        return $this->depuis($jours)->count();
    }

    /**
     * Répartition des opérations par mode de paiement, en parts de 0 à 1.
     *
     * @return array<string, float>
     */
    public function repartitionModesPaiement(int $jours): array
    {
        $operations = $this->depuis($jours);
        $total = $operations->count();

        if ($total === 0) {
            return [];
        }

        return $operations
            ->groupBy(fn (Operation $o) => $o->mode_paiement->value)
            ->map(fn (Collection $groupe) => round($groupe->count() / $total, 4))
            ->sortDesc()
            ->all();
    }

    /**
     * Paires dépôt → retrait rapprochées : un dépôt suivi d'un retrait de
     * l'essentiel du montant dans le délai imparti.
     *
     * @return array<int, array{depose_le: string, montant_depose: float, montant_retire: float, part_retiree: float, delai_heures: float}>
     */
    public function allersRetoursRapides(int $jours, int $delaiMaxHeures, float $partMinimum): array
    {
        $operations = $this->depuis($jours);
        $depots = $operations->where('type', TypeOperation::Depot)->values();
        $retraits = $operations->where('type', TypeOperation::Retrait)->values();

        $paires = [];
        $retraitsConsommes = [];

        foreach ($depots as $depot) {
            $montantDepose = (float) $depot->montant;

            if ($montantDepose <= 0.0) {
                continue;
            }

            foreach ($retraits as $index => $retrait) {
                if (isset($retraitsConsommes[$index]) || $retrait->effectuee_le < $depot->effectuee_le) {
                    continue;
                }

                $delai = $depot->effectuee_le->floatDiffInHours($retrait->effectuee_le);

                if ($delai > $delaiMaxHeures) {
                    break; // les retraits sont triés : les suivants sont encore plus tard
                }

                $part = (float) $retrait->montant / $montantDepose;

                if ($part < $partMinimum) {
                    continue;
                }

                $retraitsConsommes[$index] = true;
                $paires[] = [
                    'depose_le' => $depot->effectuee_le->toDateTimeString(),
                    'montant_depose' => $montantDepose,
                    'montant_retire' => (float) $retrait->montant,
                    'part_retiree' => round($part, 4),
                    'delai_heures' => round($delai, 1),
                ];

                break;
            }
        }

        return $paires;
    }
}
