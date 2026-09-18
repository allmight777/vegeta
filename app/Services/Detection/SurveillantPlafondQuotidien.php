<?php

namespace App\Services\Detection;

use App\Enums\GraviteAlerte;
use App\Enums\ModePaiement;
use App\Enums\StatutAlerte;
use App\Enums\TypeAlerte;
use App\Models\Alerte;
use App\Models\CumulJournalier;
use App\Models\Identite;
use App\Models\Operation;
use App\Models\RegleDetection;
use App\Services\Audit\Consignateur;
use App\Services\Explication\GenerateurExplication;

/**
 * Règle PLAFOND_QUOTIDIEN_IDENTITE : cumul des espèces de la journée sur TOUS les comptes
 * de la personne, dans toutes les agences, comparé au plafond déduit de son activité.
 *
 * Fondement : Loi uniforme art. 17 i) — les opérations en espèces multiples d'une même
 * personne dans la journée sont considérées comme une opération unique. C'est donc bien
 * la personne, et non le compte, qui est l'unité de contrôle.
 */
class SurveillantPlafondQuotidien
{
    public function __construct(private readonly GenerateurExplication $explication) {}

    public function verifier(Operation $operation, ?CumulJournalier $cumul): void
    {
        if ($cumul === null || $operation->mode_paiement !== ModePaiement::Especes) {
            return;
        }

        $regle = RegleDetection::where('code', 'PLAFOND_QUOTIDIEN_IDENTITE')->where('actif', true)->first();
        if ($regle === null) {
            return;
        }

        $identite = $cumul->identite;
        $plafond = (float) $identite->plafond_quotidien_especes;
        if ($plafond <= 0) {
            return;
        }

        $total = $cumul->totalRetenu();
        $ratioApproche = (float) ($regle->parametres['ratio_alerte_approche'] ?? 0.8);

        if ($total >= $plafond) {
            $this->lever($operation, $identite, $cumul, $regle->id, TypeAlerte::PlafondQuotidienDepasse, GraviteAlerte::Critique, $plafond, true);

            return;
        }

        if ($total >= $plafond * $ratioApproche) {
            $this->lever($operation, $identite, $cumul, $regle->id, TypeAlerte::PlafondQuotidienApproche, GraviteAlerte::Attention, $plafond, false);
        }
    }

    private function lever(
        Operation $operation,
        Identite $identite,
        CumulJournalier $cumul,
        int $regleId,
        TypeAlerte $type,
        GraviteAlerte $gravite,
        float $plafond,
        bool $depasse,
    ): void {
        // Une seule alerte par identité, par jour et par type : le responsable traite un
        // dossier, pas une notification par opération.
        $dejaLevee = Alerte::whereIn('client_id', $identite->clientIds())
            ->where('type', $type)
            ->whereDate('created_at', $cumul->jour)
            ->exists();

        if ($dejaLevee) {
            return;
        }

        Alerte::create([
            'type' => $type,
            'client_id' => $operation->compte->client_id,
            'regle_detection_id' => $regleId,
            'gravite' => $gravite,
            'explication_texte' => $this->explication->pourPlafondQuotidien(
                $cumul->nb_operations,
                $cumul->nb_comptes,
                $cumul->nb_agences,
                $this->formaterMontant($cumul->totalRetenu(), $operation->devise_code),
                $this->formaterMontant($plafond, $operation->devise_code),
                (string) $identite->base_calcul_plafond,
                $depasse,
            ),
            'faits' => [
                'identite_id' => $identite->id,
                'jour' => $cumul->jour->toDateString(),
                'total_especes' => $cumul->totalRetenu(),
                'plafond' => $plafond,
                'nb_operations' => $cumul->nb_operations,
                'nb_comptes' => $cumul->nb_comptes,
                'nb_agences' => $cumul->nb_agences,
                'rapprochement' => $identite->rapprocheeParNpi() ? 'npi' : 'empreinte',
            ],
            'statut' => StatutAlerte::Nouvelle,
        ]);

        Consignateur::enregistrer('systeme', null, 'detection_'.$type->value, 'identite', $identite->id);
    }

    private function formaterMontant(float $montant, string $devise): string
    {
        return number_format($montant, 0, ',', ' ').' '.$devise;
    }
}
