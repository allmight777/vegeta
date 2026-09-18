<?php

namespace App\Services\Kyc;

use App\Data\ResultatSimulationDepot;
use App\Models\CompteMobileMonnaieSimule;
use App\Services\Securite\IndexAveugle;

/**
 * Simule l'écran de confirmation affiché par un vrai transfert mobile money (MTN/Moov/
 * Celtiis) : à partir d'un numéro, retrouve le "titulaire" dans l'annuaire synthétique
 * (jamais un vrai appel USSD/API opérateur — voir docs/DECISIONS.md). Recherche exacte
 * par index aveugle uniquement, comme toute recherche par téléphone dans ce dépôt.
 */
class SimulateurDepotMobileMonnaie
{
    public function __construct(private readonly IndexAveugle $indexAveugle) {}

    public function simuler(string $telephone): ResultatSimulationDepot
    {
        if (blank($telephone)) {
            return new ResultatSimulationDepot(trouve: false);
        }

        $compte = CompteMobileMonnaieSimule::query()
            ->where('telephone_idx', $this->indexAveugle->calculer($telephone, 'telephone'))
            ->first();

        if ($compte === null) {
            return new ResultatSimulationDepot(trouve: false);
        }

        return new ResultatSimulationDepot(
            trouve: true,
            operateur: $compte->operateur,
            nomTitulaire: $compte->nom_titulaire,
        );
    }
}
