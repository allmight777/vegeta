<?php

namespace App\Services\Kyc;

/**
 * Fait correspondre le texte extrait d'un document au référentiel
 * config/champs_fiche_adhesion.php, par recherche du libellé exact suivi de « : » et de
 * la valeur adjacente (06_PROMPT_FORMULAIRE_CLIENT_ENRICHI §5.2.3). Confiance fixe pour
 * un match par séparateur explicite — pas une estimation statistique, ce mappeur reste
 * un heuristique de démonstration à base de regex, pas un modèle entraîné.
 */
class MappeurChampsExtraits
{
    private const CONFIANCE_MATCH_EXPLICITE = 0.9;

    public function __construct(private readonly ReferentielFicheAdhesion $referentiel) {}

    public function typeClientDevine(string $texte): string
    {
        $normalise = mb_strtolower($texte);

        if (str_contains($normalise, 'personne morale')) {
            return 'personne_morale';
        }

        if (str_contains($normalise, 'personne physique')) {
            return 'personne_physique';
        }

        return 'indetermine';
    }

    /**
     * @param  float  $plafondConfiance  jamais dépassé même sur un match par séparateur
     *                                   explicite — l'OCR sur écriture manuscrite a un taux
     *                                   d'erreur mesurable, jamais présenté comme équivalent
     *                                   au texte natif (07_PROMPT_MODE_DEGRADE_NPI_OCR §4.2).
     * @return array<string, array{valeur: string, confiance: float}>
     */
    public function mapper(string $texte, string $typeClientDevine, float $plafondConfiance = 1.0): array
    {
        if (! in_array($typeClientDevine, ['personne_physique', 'personne_morale'], true)) {
            return [];
        }

        $resultat = [];
        $confiance = min(self::CONFIANCE_MATCH_EXPLICITE, $plafondConfiance);

        foreach ($this->referentiel->champsPlats($typeClientDevine) as $code => $definition) {
            $libelle = preg_quote((string) $definition['libelle'], '/');

            if (preg_match('/'.$libelle.'\s*[:：]\s*([^\n\r]+)/iu', $texte, $correspondance)) {
                $valeur = trim($correspondance[1]);

                if ($valeur !== '') {
                    $resultat[$code] = ['valeur' => $valeur, 'confiance' => $confiance];
                }
            }
        }

        return $resultat;
    }
}
