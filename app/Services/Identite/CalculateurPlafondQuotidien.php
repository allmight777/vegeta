<?php

namespace App\Services\Identite;

use App\Models\Client;
use App\Models\Identite;
use App\Services\Audit\Consignateur;

/**
 * Le plafond quotidien d'espèces est déduit du profil déclaré au KYC : un revendeur de
 * marché n'a pas le même profil qu'un salarié. Un plafond calculé à partir de l'activité
 * est exactement ce qu'attend l'Instruction BCEAO 001-03-2025 art. 6 (profilage des
 * clients). Valeurs dans config/identite.php, source affichée, modifiable par l'admin.
 */
class CalculateurPlafondQuotidien
{
    /**
     * @return array{montant: float, base: string}
     */
    public function calculer(Client $client): array
    {
        $config = config('identite.plafond_quotidien');
        $revenus = (float) ($client->personnePhysique?->revenus_mensuels_estimes ?? 0);

        if ($revenus <= 0) {
            return [
                'montant' => (float) $config['defaut_sans_revenus'],
                'base' => 'aucun revenu déclaré au KYC — plafond prudentiel par défaut',
            ];
        }

        $montant = $revenus * (float) $config['coefficient_revenus'];
        $montant = max((float) $config['plancher'], min($montant, (float) $config['plafond_max']));

        return [
            'montant' => round($montant, 2),
            'base' => 'revenus mensuels déclarés × '.$config['coefficient_revenus'],
        ];
    }

    /**
     * Recalcule le plafond de l'identité à partir de la fiche la mieux renseignée :
     * une identité qui regroupe plusieurs fiches retient la plus favorable au client,
     * sinon compléter un dossier pourrait abaisser son plafond sans raison.
     */
    public function appliquer(Identite $identite): void
    {
        $meilleur = ['montant' => 0.0, 'base' => 'aucune fiche exploitable'];

        foreach ($identite->clients()->with('personnePhysique')->get() as $client) {
            $calcul = $this->calculer($client);
            if ($calcul['montant'] > $meilleur['montant']) {
                $meilleur = $calcul;
            }
        }

        if ($meilleur['montant'] <= 0) {
            return;
        }

        $ancien = (float) $identite->plafond_quotidien_especes;

        $identite->update([
            'plafond_quotidien_especes' => $meilleur['montant'],
            'source_plafond' => config('identite.plafond_quotidien.source'),
            'base_calcul_plafond' => $meilleur['base'],
        ]);

        if ($ancien !== $meilleur['montant']) {
            Consignateur::enregistrer('systeme', null, 'plafond_quotidien_recalcule', 'identite', $identite->id);
        }
    }
}
