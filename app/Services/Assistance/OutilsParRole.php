<?php

namespace App\Services\Assistance;

use App\Contracts\OutilAssistantIa;
use App\Models\Admin;
use App\Models\Agent;
use App\Services\Assistance\Outils\OutilBaseConnaissancesProduit;
use App\Services\Assistance\Outils\OutilCompterAlertesDuJour;
use App\Services\Assistance\Outils\OutilCompterProfilsIncomplets;
use App\Services\Assistance\Outils\OutilConsulterParametreReglementaire;
use App\Services\Assistance\Outils\OutilRechercheDocumentaire;
use App\Services\Assistance\Outils\OutilRechercherClientExistant;
use App\Services\Assistance\Outils\OutilRechercheWeb;
use App\Services\Assistance\Outils\OutilStatistiquesAgregeesAgence;

/**
 * Construit le jeu d'outils disponibles selon le rôle — c'est ce jeu d'outils, pas le
 * prompt système, qui définit ce que l'IA peut réellement faire pour chaque profil
 * (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §2.1). Jamais d'accès SQL libre : chaque
 * entrée est une classe PHP écrite à la main.
 */
class OutilsParRole
{
    public function __construct(
        private readonly OutilRechercheDocumentaire $rechercheDocumentaire,
        private readonly OutilBaseConnaissancesProduit $baseConnaissances,
        private readonly OutilRechercheWeb $rechercheWeb,
        private readonly OutilCompterProfilsIncomplets $profilsIncomplets,
        private readonly OutilCompterAlertesDuJour $compterAlertes,
        private readonly OutilConsulterParametreReglementaire $consulterParametre,
        private readonly OutilRechercherClientExistant $rechercherClient,
        private readonly OutilStatistiquesAgregeesAgence $statistiques,
    ) {}

    /**
     * @return array<int, OutilAssistantIa>
     */
    public function pour(Agent|Admin $utilisateur): array
    {
        $communs = [
            $this->rechercheDocumentaire,
            $this->baseConnaissances,
            $this->rechercheWeb,
            $this->profilsIncomplets,
        ];

        if ($utilisateur instanceof Agent && $utilisateur->estCaissier()) {
            return $communs;
        }

        // Responsable d'agence et administrateur.
        return [
            ...$communs,
            $this->compterAlertes,
            $this->consulterParametre,
            $this->rechercherClient,
            $this->statistiques,
        ];
    }
}
