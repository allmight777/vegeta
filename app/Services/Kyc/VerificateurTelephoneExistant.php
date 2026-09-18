<?php

namespace App\Services\Kyc;

use App\Data\ResultatVerificationTelephone;
use App\Models\PersonneMorale;
use App\Models\PersonnePhysique;
use App\Services\Empreinte\ComparateurEmpreinte;
use App\Services\Empreinte\GenerateurEmpreinte;
use App\Services\Securite\IndexAveugle;

/**
 * Recherche, au sein du réseau de l'agent connecté, un client déjà enregistré sous ce
 * même numéro de téléphone (recherche exacte par index aveugle uniquement — jamais de
 * LIKE sur une colonne chiffrée). Sert la consolidation d'un même client multi-agences
 * et la détection d'une usurpation (numéro réel, nom déclaré différent) — jamais une
 * vérification externe : ce MVP ne recherche que dans les fiches déjà tenues par le
 * réseau, jamais dans un système tiers (CLAUDE.md §2.1, docs/DECISIONS.md §16).
 *
 * Ne renvoie jamais le nom trouvé au client HTTP, seulement un avertissement textuel
 * (même minimisation que NpiVerificationController) : le guichet voit qu'un doublon
 * probable existe, jamais l'identité complète de l'autre fiche depuis ce simple contrôle.
 */
class VerificateurTelephoneExistant
{
    private const SEUIL_RESSEMBLANCE_NOM = 0.7;

    public function __construct(
        private readonly IndexAveugle $indexAveugle,
        private readonly GenerateurEmpreinte $generateur,
        private readonly ComparateurEmpreinte $comparateur,
    ) {}

    public function verifier(string $telephone, ?int $reseauId, ?string $nomSaisi, ?string $clientIdActuel = null): ResultatVerificationTelephone
    {
        if (blank($telephone) || $reseauId === null) {
            return new ResultatVerificationTelephone(dejaEnregistre: false, avertissementNom: null);
        }

        $idx = $this->indexAveugle->calculer($telephone, 'telephone');

        $nomExistant = $this->rechercherNomPersonnePhysique($idx, $reseauId, $clientIdActuel)
            ?? $this->rechercherRaisonSocialePersonneMorale($idx, $reseauId, $clientIdActuel);

        if ($nomExistant === null) {
            return new ResultatVerificationTelephone(dejaEnregistre: false, avertissementNom: null);
        }

        if (blank($nomSaisi)) {
            return new ResultatVerificationTelephone(
                dejaEnregistre: true,
                avertissementNom: 'Ce numéro de téléphone est déjà associé à un client existant de ce réseau — vérifiez qu\'il ne s\'agit pas d\'un doublon avant de continuer.',
            );
        }

        $score = $this->comparateur->dice(
            $this->generateur->encoderNom($nomSaisi),
            $this->generateur->encoderNom($nomExistant),
        );

        $avertissement = $score < self::SEUIL_RESSEMBLANCE_NOM
            ? 'Ce numéro de téléphone est déjà associé à un client existant sous un nom sensiblement différent — vérifiez qu\'il ne s\'agit pas d\'un doublon ou d\'une usurpation.'
            : 'Ce numéro de téléphone est déjà associé à un client existant de ce réseau (nom proche) — vérifiez qu\'il ne s\'agit pas d\'un doublon avant de créer une nouvelle fiche.';

        return new ResultatVerificationTelephone(dejaEnregistre: true, avertissementNom: $avertissement);
    }

    private function rechercherNomPersonnePhysique(string $idx, int $reseauId, ?string $clientIdActuel): ?string
    {
        $personne = PersonnePhysique::query()
            ->where('telephone_idx', $idx)
            ->whereHas('client', function ($requete) use ($reseauId, $clientIdActuel) {
                $requete->where('reseau_id', $reseauId);
                if ($clientIdActuel !== null) {
                    $requete->where('id', '!=', $clientIdActuel);
                }
            })
            ->first();

        return $personne === null ? null : trim($personne->prenoms.' '.$personne->nom);
    }

    private function rechercherRaisonSocialePersonneMorale(string $idx, int $reseauId, ?string $clientIdActuel): ?string
    {
        $personneMorale = PersonneMorale::query()
            ->where('telephone_idx', $idx)
            ->whereHas('client', function ($requete) use ($reseauId, $clientIdActuel) {
                $requete->where('reseau_id', $reseauId);
                if ($clientIdActuel !== null) {
                    $requete->where('id', '!=', $clientIdActuel);
                }
            })
            ->first();

        return $personneMorale?->raison_sociale;
    }
}
