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

    public function pourFractionnementMultiAgences(int $nbOperations, string $montantFormate, int $nbAgences, string $seuilFormate, int $fenetreJours, string $rapprochement = 'npi'): string
    {
        $critere = $rapprochement === 'npi'
            ? 'rapprochés par NPI vérifié'
            : 'rapprochés par empreinte (nom et date de naissance)';

        return "{$nbOperations} dépôts en espèces totalisant {$montantFormate} ont été effectués par la même personne dans ".
            "{$nbAgences} agences différentes en {$fenetreJours} jours, {$critere}, chacun sous le seuil unitaire. ".
            "Cumulé, ce montant dépasse {$seuilFormate}.";
    }

    /**
     * Le responsable doit comprendre sans cliquer : combien d'opérations, sur combien
     * de comptes, dans combien d'agences, contre quel plafond et sur quelle base.
     */
    public function pourPlafondQuotidien(
        int $nbOperations,
        int $nbComptes,
        int $nbAgences,
        string $cumulFormate,
        string $plafondFormate,
        string $baseCalcul,
        bool $depasse,
    ): string {
        $comptes = $nbComptes > 1 ? "{$nbComptes} comptes" : 'un seul compte';
        $agences = $nbAgences > 1 ? " dans {$nbAgences} agences différentes" : '';
        $verdict = $depasse
            ? "Ce cumul dépasse le plafond quotidien de {$plafondFormate}"
            : "Ce cumul approche le plafond quotidien de {$plafondFormate}";

        return "{$nbOperations} opérations en espèces aujourd'hui sur {$comptes}{$agences}, ".
            "totalisant {$cumulFormate} pour la même personne. {$verdict} ".
            "(calculé sur : {$baseCalcul}).";
    }

    public function pourCompteDormant(int $moisInactivite, string $montantFormate): string
    {
        return "Ce compte n'avait enregistré aucune opération depuis {$moisInactivite} mois. ".
            "Une opération de {$montantFormate} vient de le réactiver — l'exécutant est tracé.";
    }

    /**
     * Jamais le nom saisi ni le nom simulé trouvé (même principe que pourFiltrage) : la
     * fiche complète reste accessible au responsable depuis le tableau de bord, mais
     * cette phrase d'audit n'en a pas besoin.
     */
    public function pourIncoherenceDepotSimule(string $operateurLibelle, float $score): string
    {
        $pourcentage = round($score * 100).' %';

        return 'Le nom saisi par le caissier ne correspond pas au titulaire connu du numéro de téléphone '.
            "déclaré (opérateur {$operateurLibelle}, similarité {$pourcentage} — simulation de dépôt). ".
            "Vérifiez l'identité avant de poursuivre.";
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
