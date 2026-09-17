<?php

namespace App\Services\Assistance;

use App\Contracts\DetecteurConnectivite;
use App\Contracts\ProviderIa;

/**
 * Centralise la bascule réel/simulateur (08_PROMPT §4.4) pour que les contrôleurs n'aient
 * jamais à en connaître le détail. Ordre de priorité : démonstration forcée > absence de
 * clé API > absence de connexion (Contracts\DetecteurConnectivite, 07_PROMPT §2) > réel.
 */
class SelecteurProviderIa
{
    public function __construct(
        private readonly DetecteurConnectivite $connectivite,
        private readonly ProviderIaSimulateur $simulateur,
        private readonly ProviderIaApiExterne $apiExterne,
    ) {}

    public function choisir(): ProviderIa
    {
        if (config('assistance.forcer_simulateur')) {
            return $this->simulateur;
        }

        if (blank(config('assistance.api_cle'))) {
            return $this->simulateur;
        }

        if (! $this->connectivite->estEnLigne()) {
            return $this->simulateur;
        }

        return $this->apiExterne;
    }

    public function modeActifLibelle(): string
    {
        return $this->choisir() instanceof ProviderIaApiExterne
            ? 'Assistant en ligne'
            : 'Assistant hors connexion — base locale';
    }
}
