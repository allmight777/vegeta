<?php

namespace App\Services\Assistance;

use App\Contracts\MoteurRechercheWeb;

/**
 * Résultats fictifs illustratifs (`source = demo`), utilisé tant que les clés de
 * recherche web ne sont pas configurées ou hors connexion
 * (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §4.2) — l'assistant reste fonctionnel, il
 * indique juste qu'il travaille en mode démonstration.
 */
class MoteurRechercheWebSimulateur implements MoteurRechercheWeb
{
    public function rechercher(string $requete): array
    {
        return [
            [
                'titre' => "Résultat de démonstration pour « {$requete} »",
                'url' => 'https://exemple.demo/resultat-1',
                'extrait' => 'Recherche web en mode démonstration — aucune clé de recherche configurée ou connexion indisponible. '
                    .'Ceci est un résultat fictif, à ne jamais citer comme une source réelle.',
            ],
        ];
    }
}
