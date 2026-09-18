<?php

namespace App\Services\Assistance;

use App\Contracts\DetecteurConnectivite;
use App\Contracts\MoteurRechercheWeb;

/**
 * Même priorité que SelecteurProviderIa (démonstration forcée > clés absentes > hors
 * connexion > réel) — 10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §4.2.
 */
class SelecteurMoteurRechercheWeb
{
    public function __construct(
        private readonly DetecteurConnectivite $connectivite,
        private readonly MoteurRechercheWebSimulateur $simulateur,
        private readonly MoteurRechercheWebApiExterne $apiExterne,
    ) {}

    public function choisir(): MoteurRechercheWeb
    {
        if (config('recherche_web.forcer_simulateur')) {
            return $this->simulateur;
        }

        if (blank(config('recherche_web.google_api_key')) || blank(config('recherche_web.google_engine_id'))) {
            return $this->simulateur;
        }

        if (! $this->connectivite->estEnLigne()) {
            return $this->simulateur;
        }

        return $this->apiExterne;
    }
}
