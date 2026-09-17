<?php

namespace App\Services\Explication;

use App\Models\Client;
use App\Models\EntreeListe;
use App\Models\Signataire;

/**
 * Gabarits déterministes : jamais un score brut sans phrase, jamais le nom complet
 * de la personne recherchée tant que la correspondance n'est pas confirmée (§6.2).
 */
class GenerateurExplication
{
    public function pourFiltrage(Client|Signataire $cible, EntreeListe $entree, float $score): string
    {
        $initiales = $this->initiales($this->nomCible($cible));
        $pourcentage = round($score * 100).' %';

        return "Le nom saisi ({$initiales}) ressemble fortement à {$entree->nomMasque()}, ".
            "inscrit sur la liste {$entree->source->libelle()} (score {$pourcentage}). ".
            'Vérifiez l\'identité avant de poursuivre.';
    }

    public function pourFractionnementGuichet(int $nbOperations, string $montantFormate, string $seuilFormate, int $fenetreHeures): string
    {
        return "{$nbOperations} opérations totalisant {$montantFormate} ont été effectuées sur le même compte ".
            "en moins de {$fenetreHeures} heures, chacune sous le seuil habituel. Cumulées, elles dépassent {$seuilFormate}.";
    }

    public function pourFractionnementMultiAgences(int $nbOperations, string $montantFormate, int $nbAgences, string $seuilFormate, int $fenetreJours): string
    {
        return "{$nbOperations} dépôts totalisant {$montantFormate} ont été effectués par le même client dans {$nbAgences} agences ".
            "différentes en {$fenetreJours} jours, chacun sous le seuil habituel. Cumulé, ce montant dépasse {$seuilFormate}.";
    }

    public function pourCompteDormant(int $moisInactivite, string $montantFormate): string
    {
        return "Ce compte n'avait enregistré aucune opération depuis {$moisInactivite} mois. ".
            "Une opération de {$montantFormate} vient de le réactiver — l'exécutant est tracé.";
    }

    private function nomCible(Client|Signataire $cible): string
    {
        return $cible instanceof Signataire ? (string) $cible->nom : $cible->nomAffichage();
    }

    private function initiales(string $nomComplet): string
    {
        $mots = array_filter(explode(' ', trim($nomComplet)));

        if ($mots === []) {
            return '?';
        }

        return collect($mots)->map(fn ($mot) => mb_strtoupper(mb_substr($mot, 0, 1)).'.')->implode(' ');
    }
}
