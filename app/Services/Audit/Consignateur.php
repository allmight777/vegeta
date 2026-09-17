<?php

namespace App\Services\Audit;

use App\Models\JournalAudit;
use Illuminate\Support\Facades\DB;

class Consignateur
{
    public static function enregistrer(
        string $acteurType,
        ?int $acteurId,
        string $action,
        ?string $cibleType = null,
        ?string $cibleId = null,
    ): JournalAudit {
        return DB::transaction(function () use ($acteurType, $acteurId, $action, $cibleType, $cibleId) {
            $precedent = JournalAudit::query()->orderByDesc('id')->lockForUpdate()->first();
            $hashPrecedent = $precedent?->hash_courant;

            $donnees = [
                'acteur_type' => $acteurType,
                'acteur_id' => $acteurId,
                'action' => $action,
                'cible_type' => $cibleType,
                'cible_id' => $cibleId,
                'hash_precedent' => $hashPrecedent,
            ];

            $hashCourant = hash('sha256', ($hashPrecedent ?? '').json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return JournalAudit::create($donnees + ['hash_courant' => $hashCourant]);
        });
    }

    /**
     * Recalcule toute la chaîne et renvoie l'id de la première rupture (null si intègre).
     */
    public static function premiereRupture(): ?int
    {
        $hashPrecedent = null;

        foreach (JournalAudit::query()->orderBy('id')->cursor() as $ligne) {
            if ($ligne->hash_precedent !== $hashPrecedent) {
                return $ligne->id;
            }

            $donnees = [
                'acteur_type' => $ligne->acteur_type,
                'acteur_id' => $ligne->acteur_id,
                'action' => $ligne->action,
                'cible_type' => $ligne->cible_type,
                'cible_id' => $ligne->cible_id,
                'hash_precedent' => $ligne->hash_precedent,
            ];

            $attendu = hash('sha256', ($hashPrecedent ?? '').json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            if ($attendu !== $ligne->hash_courant) {
                return $ligne->id;
            }

            $hashPrecedent = $ligne->hash_courant;
        }

        return null;
    }
}
