<?php

namespace App\Services\Reseau;

use App\Contracts\DetecteurConnectivite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Requête légère et rapide (timeout court) vers une URL de test configurable, avec mise
 * en cache pour ne pas tester la connexion à chaque clic (driver `database`, déjà en
 * place — CLAUDE.md §4).
 */
class DetecteurConnectiviteHttp implements DetecteurConnectivite
{
    private const CLE_CACHE = 'connectivite:etat';

    public function estEnLigne(): bool
    {
        // Forçage explicite : la démonstration ne doit jamais dépendre du wifi de la
        // salle, et un test qui décrit le mode dégradé doit pouvoir le provoquer.
        $mode = (string) config('reseau.mode_connectivite', 'auto');

        if ($mode === 'en_ligne') {
            return true;
        }

        if ($mode === 'hors_ligne') {
            return false;
        }

        return Cache::remember(self::CLE_CACHE, (int) config('reseau.duree_cache_secondes', 10), function () {
            try {
                return Http::timeout(2)->get((string) config('reseau.url_test_connectivite'))->successful();
            } catch (Throwable) {
                return false;
            }
        });
    }
}
