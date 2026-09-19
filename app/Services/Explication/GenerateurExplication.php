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

    /**
     * Pourquoi le système a suspecté ce profil (18_PROMPT §5.3) : reformulation en langage clair des
     * indicateurs déjà détectés, à partir de caractéristiques DÉRIVÉES seulement — jamais un nom, un
     * NPI, une adresse ni un texte rédigé. Une suggestion, pas une accusation.
     *
     * @param  array{score: float, indicateurs: array<int, string>, age: ?int, ratio_depot_revenu: ?float, nombre_agences: int, nombre_operations: int, type_client: string, niveau_risque: string, montant_total: float}  $d
     */
    public function pourSuggestionSoupcon(array $d): string
    {
        $indicateurs = $d['indicateurs'] === [] ? 'aucun indicateur précis' : implode(' ; ', array_map('mb_strtolower', $d['indicateurs']));
        $constats = [];

        if ($d['nombre_operations'] > 0) {
            $constats[] = $d['nombre_operations'].' opération'.($d['nombre_operations'] > 1 ? 's' : '').' totalisant '
                .number_format($d['montant_total'], 0, ',', ' ').' XOF récemment, réparties dans '
                .$d['nombre_agences'].' agence'.($d['nombre_agences'] > 1 ? 's' : '');
        }
        if ($d['ratio_depot_revenu'] !== null) {
            $constats[] = 'un dépôt initial déclaré égal à '.$d['ratio_depot_revenu'].' fois le revenu mensuel estimé';
        }
        if ($d['age'] !== null) {
            $constats[] = 'un client de '.$d['age'].' ans';
        }

        return "Le système a relevé {$indicateurs} (score ".round($d['score'])." sur 100, niveau de risque {$d['niveau_risque']}). "
            .($constats === [] ? '' : 'Faits observés : '.implode(', ', $constats).'. ')
            .'Il s\'agit d\'une suggestion à vérifier, pas d\'une accusation : à vous d\'établir si ces éléments sont justifiés.';
    }

    /**
     * Résumé d'un dossier pour le responsable d'agence (18_PROMPT §6.3) : extraction des champs
     * STRUCTURÉS de la fiche (indicateurs, canal, montants, avis technique) — jamais le résumé des
     * faits ni l'analyse rédigés, qui peuvent contenir de l'identité. L'analyse complète reste à lire
     * dans la fiche.
     *
     * @param  array{reference: string, type_client: string, niveau_risque: string, indicateurs: array<int, string>, canal: ?string, nombre_operations: int, montant_total: float, avis_controleur: ?string}  $d
     */
    public function pourResumeDossierSoupcon(array $d): string
    {
        $indicateurs = $d['indicateurs'] === [] ? 'aucun indicateur coché' : implode(' ; ', array_map('mb_strtolower', $d['indicateurs']));

        return "Dossier {$d['reference']} : client de type {$d['type_client']}, niveau de risque {$d['niveau_risque']}. "
            ."Indicateurs retenus par le contrôleur : {$indicateurs}. "
            .($d['nombre_operations'] > 0
                ? $d['nombre_operations'].' opération'.($d['nombre_operations'] > 1 ? 's' : '').' concernée'.($d['nombre_operations'] > 1 ? 's' : '')
                    .' pour '.number_format($d['montant_total'], 0, ',', ' ').' XOF'.($d['canal'] ? ", canal {$d['canal']}" : '').'. '
                : '')
            .($d['avis_controleur'] ? "Avis technique du contrôleur : {$d['avis_controleur']}. " : '')
            .'Le détail de son analyse est dans la fiche ci-dessous.';
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
