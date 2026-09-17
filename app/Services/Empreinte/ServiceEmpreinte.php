<?php

namespace App\Services\Empreinte;

use App\Models\EntreeListe;
use App\Models\PersonneMorale;
use App\Models\PersonnePhysique;
use App\Models\Signataire;
use App\Support\Bitset;

/**
 * Façade du domaine empreinte : maintient empreinte_nom / empreinte_combinee sur les
 * entités qui en portent, et compare deux vecteurs stockés en base.
 */
class ServiceEmpreinte
{
    public const TAILLE_BITS_NOM = 1000;

    public const TAILLE_BITS_COMBINEE = 1500;

    public function __construct(
        private readonly GenerateurEmpreinte $generateur,
        private readonly ComparateurEmpreinte $comparateur,
    ) {}

    public function calculerPourPersonnePhysique(PersonnePhysique $personne): void
    {
        $nomComplet = trim(($personne->prenoms ?? '').' '.($personne->nom ?? ''));
        $vecteurNom = $this->generateur->encoderNom($nomComplet);
        $personne->empreinte_nom = $vecteurNom->versOctets();

        $dateNormalisee = $this->generateur->normaliserDate($personne->date_naissance);
        $personne->empreinte_combinee = $dateNormalisee === null
            ? Bitset::concatener($vecteurNom, Bitset::vide(500))->versOctets()
            : Bitset::concatener($vecteurNom, $this->generateur->encoderDate($dateNormalisee))->versOctets();
    }

    public function calculerPourPersonneMorale(PersonneMorale $personne): void
    {
        $personne->empreinte_nom = $this->generateur->encoderNom((string) $personne->raison_sociale)->versOctets();
    }

    public function calculerPourSignataire(Signataire $signataire): void
    {
        $signataire->empreinte_nom = $this->generateur->encoderNom((string) $signataire->nom)->versOctets();
    }

    public function calculerPourEntreeListe(EntreeListe $entree): void
    {
        $entree->empreinte_nom = $this->generateur->encoderNom((string) $entree->nom)->versOctets();
    }

    public function similariteNom(?string $octetsA, ?string $octetsB): ?float
    {
        if ($octetsA === null || $octetsB === null) {
            return null;
        }

        return $this->comparateur->dice(
            Bitset::depuisOctets($octetsA, self::TAILLE_BITS_NOM),
            Bitset::depuisOctets($octetsB, self::TAILLE_BITS_NOM),
        );
    }

    public function similariteCombinee(?string $octetsA, ?string $octetsB): ?float
    {
        if ($octetsA === null || $octetsB === null) {
            return null;
        }

        return $this->comparateur->dice(
            Bitset::depuisOctets($octetsA, self::TAILLE_BITS_COMBINEE),
            Bitset::depuisOctets($octetsB, self::TAILLE_BITS_COMBINEE),
        );
    }
}
