<?php

namespace App\Services\Kyc;

use App\Contracts\ConnecteurSystemeExistant;
use App\Models\PersonnePhysique;
use App\Services\Empreinte\GenerateurEmpreinte;
use App\Services\Empreinte\ServiceEmpreinte;
use App\Services\Securite\IndexAveugle;
use App\Support\Bitset;

/**
 * Cherche un autre client déjà présent (typiquement issu d'un import CSV core banking,
 * cf. Services\Import\ImportateurCsv) qui correspondrait au client à compléter, par NPI
 * (index aveugle, jamais par valeur en clair) ou par nom + date de naissance via
 * l'empreinte — jamais par comparaison de texte en clair (06_PROMPT §7.2).
 *
 * Adaptation assumée par rapport au prompt : ce MVP fusionne déjà les lignes CSV
 * rapprochées dans clients/personnes_physiques (ImportateurCsv::rapprocherOuCreer), donc
 * la donnée « système existant » la plus fiable est une autre PersonnePhysique déjà
 * persistée, pas une relecture de import_lignes.donnees_brutes (qui garde les en-têtes
 * CSV bruts et variables d'un fichier à l'autre) — voir docs/DECISIONS.md.
 */
class ConnecteurImportLocal implements ConnecteurSystemeExistant
{
    private const SEUIL_RAPPROCHEMENT = 0.85;

    public function __construct(
        private readonly ServiceEmpreinte $empreinte,
        private readonly GenerateurEmpreinte $generateur,
        private readonly IndexAveugle $indexAveugle,
    ) {}

    public function rechercherParCritere(string $type, array $criteres): ?array
    {
        if ($type !== 'personne_physique') {
            return null;
        }

        $exclureClientId = $criteres['exclure_client_id'] ?? null;
        $candidat = $this->parNpi($criteres['npi'] ?? null, $exclureClientId)
            ?? $this->parNomEtDate($criteres, $exclureClientId);

        if ($candidat === null) {
            return null;
        }

        return collect(PersonnePhysique::CHAMPS_COMPLETABLES)
            ->keys()
            ->mapWithKeys(fn (string $code) => [$code => $candidat->{$code}])
            ->filter(fn ($valeur) => filled($valeur))
            ->all();
    }

    private function parNpi(?string $npi, ?string $exclureClientId): ?PersonnePhysique
    {
        if (blank($npi)) {
            return null;
        }

        return PersonnePhysique::where('npi_idx', $this->indexAveugle->calculer($npi, 'npi'))
            ->when($exclureClientId, fn ($q) => $q->where('client_id', '!=', $exclureClientId))
            ->first();
    }

    private function parNomEtDate(array $criteres, ?string $exclureClientId): ?PersonnePhysique
    {
        if (blank($criteres['nom'] ?? null)) {
            return null;
        }

        $nomComplet = trim(($criteres['prenoms'] ?? '').' '.($criteres['nom'] ?? ''));
        $vecteurNom = $this->generateur->encoderNom($nomComplet);
        $dateNormalisee = $this->generateur->normaliserDate($criteres['date_naissance'] ?? null);
        $vecteurCible = $dateNormalisee !== null
            ? Bitset::concatener($vecteurNom, $this->generateur->encoderDate($dateNormalisee))
            : Bitset::concatener($vecteurNom, Bitset::vide(500));

        $meilleur = null;
        $meilleurScore = 0.0;

        foreach (PersonnePhysique::when($exclureClientId, fn ($q) => $q->where('client_id', '!=', $exclureClientId))->get() as $existante) {
            $score = $this->empreinte->similariteCombinee($vecteurCible->versOctets(), $existante->empreinte_combinee);
            if ($score !== null && $score > $meilleurScore) {
                $meilleurScore = $score;
                $meilleur = $existante;
            }
        }

        return $meilleurScore >= self::SEUIL_RAPPROCHEMENT ? $meilleur : null;
    }
}
