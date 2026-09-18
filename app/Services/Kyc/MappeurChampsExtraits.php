<?php

namespace App\Services\Kyc;

use Illuminate\Support\Str;

/**
 * Fait correspondre le texte extrait d'un document au référentiel
 * config/champs_fiche_adhesion.php (06_PROMPT_FORMULAIRE_CLIENT_ENRICHI §5.2.3).
 * Travaille ligne par ligne (jamais une regex multiligne sur tout le texte, trop
 * permissive) : pour chaque libellé, cherche la première ligne non déjà réservée qui en
 * commence par le texte, prend le reste de cette ligne comme valeur, ou à défaut la
 * ligne suivante non vide. Une ligne déjà réservée par un libellé ne peut plus être
 * réutilisée par un autre — sans quoi un libellé court ("Nom") happerait la ligne d'un
 * libellé plus long qui le commence ("Nom du responsable"). Les libellés sont donc
 * essayés du plus long au plus court. Confiance fixe pour un match explicite — pas une
 * estimation statistique, ce mappeur reste un heuristique de démonstration à base de
 * texte, pas un modèle entraîné.
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
     * @param  float  $plafondConfiance  jamais dépassé même sur un match explicite —
     *                                   l'OCR sur écriture manuscrite a un taux d'erreur
     *                                   mesurable, jamais présenté comme équivalent au
     *                                   texte natif (07_PROMPT_MODE_DEGRADE_NPI_OCR §4.2).
     * @return array<string, array{valeur: string, confiance: float}>
     */
    public function mapper(string $texte, string $typeClientDevine, float $plafondConfiance = 1.0): array
    {
        if (! in_array($typeClientDevine, ['personne_physique', 'personne_morale'], true)) {
            return [];
        }

        $definitions = $this->referentiel->champsPlats($typeClientDevine);

        // Le plus long libellé d'abord : "Nom du responsable" doit réserver sa ligne
        // avant que "Nom" ne cherche la sienne, sinon "Nom" matcherait cette même
        // ligne et lui donnerait "du responsable : ..." comme valeur.
        uasort($definitions, fn (array $a, array $b) => mb_strlen((string) $b['libelle']) <=> mb_strlen((string) $a['libelle']));

        // Les candidats valeur sont aussi rejetés s'ils ressemblent à un titre de
        // section ("Activité économique", "Identification"...), pas seulement au
        // libellé d'un autre champ — un titre de groupe suivant un libellé isolé est
        // le cas exact qui a motivé ce repli.
        $tousLesLibelles = [
            ...array_map(fn (array $d) => (string) $d['libelle'], $definitions),
            ...array_map(fn (array $g) => (string) $g['libelle'], $this->referentiel->groupes($typeClientDevine)),
        ];

        $lignes = array_values(array_filter(
            array_map('trim', preg_split('/\R/u', $texte) ?: []),
            fn (string $ligne) => $ligne !== '',
        ));
        $ligneReservee = array_fill(0, count($lignes), false);

        $resultat = [];
        $confiance = min(self::CONFIANCE_MATCH_EXPLICITE, $plafondConfiance);

        foreach ($definitions as $code => $definition) {
            $valeur = $this->trouverValeur((string) $definition['libelle'], $lignes, $ligneReservee, $tousLesLibelles);

            if ($valeur !== null) {
                $resultat[$code] = ['valeur' => $valeur, 'confiance' => $confiance];
            }
        }

        return $resultat;
    }

    /**
     * @param  array<int, string>  $lignes
     * @param  array<int, bool>  $ligneReservee  passé par référence : la ligne trouvée
     *                                           pour ce libellé (et, le cas échéant, la
     *                                           ligne suivante utilisée comme valeur) est
     *                                           marquée réservée même si aucune valeur
     *                                           n'en est retenue — un libellé ne cherche
     *                                           jamais deux fois.
     * @param  array<int, string>  $tousLesLibelles
     */
    private function trouverValeur(string $libelle, array $lignes, array &$ligneReservee, array $tousLesLibelles): ?string
    {
        foreach ($lignes as $index => $ligne) {
            if ($ligneReservee[$index]) {
                continue;
            }

            $reste = $this->resteApresLibelle($ligne, $libelle);

            if ($reste === null) {
                continue;
            }

            $ligneReservee[$index] = true;
            $reste = trim(ltrim($reste, " \t:："));

            if ($reste !== '' && ! $this->ressembleAUnTitreOuUnAutreLibelle($reste, $tousLesLibelles)) {
                return $reste;
            }

            // Libellé seul (ou suivi uniquement de bruit, ex. la fin d'un titre de
            // section) : on tente la ligne suivante non réservée, sauf si elle
            // ressemble elle-même à un titre ou au libellé d'un autre champ — dans ce
            // cas, ce champ n'a simplement pas de valeur sur ce document.
            $indexSuivant = $index + 1;

            if (
                isset($lignes[$indexSuivant])
                && ! $ligneReservee[$indexSuivant]
                && ! $this->ressembleAUnTitreOuUnAutreLibelle($lignes[$indexSuivant], $tousLesLibelles)
            ) {
                $ligneReservee[$indexSuivant] = true;

                return $lignes[$indexSuivant];
            }

            return null;
        }

        return null;
    }

    /**
     * Compare libellé et début de ligne en tolérant l'accentuation (l'OCR perd parfois
     * un accent) — retourne le texte de la ligne après le libellé (non normalisé,
     * jamais tronqué de son contenu réel), ou null si la ligne ne commence pas par ce
     * libellé.
     */
    private function resteApresLibelle(string $ligne, string $libelle): ?string
    {
        $ligneNormalisee = Str::ascii(mb_strtolower($ligne));
        $libelleNormalise = Str::ascii(mb_strtolower($libelle));

        if (! str_starts_with($ligneNormalisee, $libelleNormalise)) {
            return null;
        }

        return mb_substr($ligne, mb_strlen($libelle));
    }

    /**
     * Un candidat valeur n'est jamais retenu s'il ressemble à la suite d'un titre de
     * section (ex. « / CONTACT », reste de « ADRESSE / CONTACT ») ou au libellé d'un
     * autre champ (ex. une ligne « Domicile » rencontrée en cherchant la valeur
     * d'« Adresse »). Ne rejette jamais sur la seule casse : un nom de famille est
     * couramment saisi tout en majuscules sur ces fiches ("KPADONOU" reste une valeur
     * valide), et une date peut légitimement contenir "/" — seul un candidat qui
     * *commence* par "/" est un résidu de titre, pas une valeur.
     *
     * @param  array<int, string>  $tousLesLibelles
     */
    private function ressembleAUnTitreOuUnAutreLibelle(string $candidat, array $tousLesLibelles): bool
    {
        if (str_starts_with($candidat, '/')) {
            return true;
        }

        $candidatNormalise = Str::ascii(mb_strtolower($candidat));

        foreach ($tousLesLibelles as $libelle) {
            if (str_starts_with($candidatNormalise, Str::ascii(mb_strtolower($libelle)))) {
                return true;
            }
        }

        return false;
    }
}
