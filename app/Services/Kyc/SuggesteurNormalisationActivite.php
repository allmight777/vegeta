<?php

namespace App\Services\Kyc;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Copilote de saisie — normalisation des champs d'activité (16_PROMPT §2.3). Simple
 * comptage des orthographes déjà saisies dans le réseau, aucune IA : « commercante »
 * → « Commerçante » si au moins N dossiers l'utilisent déjà. Des données propres en
 * entrée rendent la détection en aval plus fiable.
 *
 * Ne renvoie qu'une orthographe et un nombre de dossiers — jamais un dossier.
 */
class SuggesteurNormalisationActivite
{
    /** Champ du formulaire → colonnes de personnes_physiques qui alimentent la norme. */
    private const COLONNES = [
        'profession' => ['profession'],
        'activite_1' => ['activite_1', 'activite_2'],
        'activite_2' => ['activite_1', 'activite_2'],
        'employeur' => ['employeur'],
        'nationalite' => ['nationalite'],
    ];

    /** Nombre maximal de propositions affichées pendant la frappe. */
    public const LIMITE_PROPOSITIONS = 5;

    /**
     * @return array{valeur: string, nombre: int}|null
     */
    public function suggerer(string $champ, string $valeur, int $reseauId): ?array
    {
        $colonnes = self::COLONNES[$champ] ?? null;
        $valeur = trim($valeur);

        if ($colonnes === null || $valeur === '') {
            return null;
        }

        $cle = $this->normaliser($valeur);
        $groupes = $this->groupes($colonnes, $reseauId);

        $meilleur = null;

        foreach ($groupes as $cleGroupe => $variantes) {
            if (! $this->proche($cle, (string) $cleGroupe)) {
                continue;
            }

            arsort($variantes);
            $orthographe = (string) array_key_first($variantes);
            $nombre = (int) $variantes[$orthographe];

            if ($meilleur === null || $nombre > $meilleur['nombre']) {
                $meilleur = ['valeur' => $orthographe, 'nombre' => $nombre];
            }
        }

        if ($meilleur === null
            || $meilleur['valeur'] === $valeur
            || $meilleur['nombre'] < (int) config('kyc.copilote.normalisation_min_dossiers')) {
            return null;
        }

        return $meilleur;
    }

    /**
     * Propositions pendant la frappe (17_PROMPT §1) : orthographes déjà saisies dans le réseau
     * dont un mot commence par ce qui est tapé. Comparaison faite en PHP sur des textes
     * normalisés des deux côtés (Str::ascii + minuscules) : « etu » propose « Étudiant »,
     * « commercant » propose « Commerçante », sur SQLite comme sur PostgreSQL ou MySQL (la casse et
     * les accents ne dépendent pas de la collation du moteur). Les plus utilisées d'abord.
     *
     * @return array<int, array{valeur: string, nombre: int}> vide si rien ne correspond
     */
    public function proposer(string $champ, string $saisie, int $reseauId): array
    {
        $colonnes = self::COLONNES[$champ] ?? null;
        $prefixe = $this->normaliser($saisie);

        if ($colonnes === null || mb_strlen($prefixe) < 2) {
            return [];
        }

        $motif = '/(?:^|[\s\'-])'.preg_quote($prefixe, '/').'/';
        $propositions = [];

        foreach ($this->groupes($colonnes, $reseauId) as $cleGroupe => $variantes) {
            if (preg_match($motif, (string) $cleGroupe) !== 1) {
                continue;
            }

            arsort($variantes);
            $orthographe = (string) array_key_first($variantes);

            // Ce qui est déjà exactement saisi n'est pas une proposition utile.
            if ($orthographe === trim($saisie)) {
                continue;
            }

            $propositions[] = ['valeur' => $orthographe, 'nombre' => array_sum($variantes)];
        }

        usort($propositions, fn (array $a, array $b) => [$b['nombre'], $a['valeur']] <=> [$a['nombre'], $b['valeur']]);

        return array_slice($propositions, 0, self::LIMITE_PROPOSITIONS);
    }

    /**
     * @param  array<int, string>  $colonnes
     * @return array<string, array<string, int>> clé normalisée => [orthographe exacte => nombre de dossiers]
     */
    private function groupes(array $colonnes, int $reseauId): array
    {
        $groupes = [];

        foreach ($colonnes as $colonne) {
            $lignes = DB::table('personnes_physiques')
                ->join('clients', 'clients.id', '=', 'personnes_physiques.client_id')
                ->where('clients.reseau_id', $reseauId)
                ->whereNull('clients.deleted_at')
                ->whereNull('personnes_physiques.deleted_at')
                ->whereNotNull("personnes_physiques.$colonne")
                ->where("personnes_physiques.$colonne", '!=', '')
                ->selectRaw("personnes_physiques.$colonne as valeur, count(*) as nombre")
                ->groupBy("personnes_physiques.$colonne")
                ->get();

            foreach ($lignes as $ligne) {
                $cle = $this->normaliser((string) $ligne->valeur);
                $groupes[$cle][(string) $ligne->valeur] = ($groupes[$cle][(string) $ligne->valeur] ?? 0) + (int) $ligne->nombre;
            }
        }

        return $groupes;
    }

    private function normaliser(string $texte): string
    {
        return trim(preg_replace('/\s+/', ' ', Str::of($texte)->ascii()->lower()->toString()) ?? '');
    }

    /** Même mot à la casse/accents près, ou faute de frappe d'une lettre (deux si le mot est long). */
    private function proche(string $a, string $b): bool
    {
        if ($a === $b) {
            return true;
        }

        if (min(strlen($a), strlen($b)) < 5) {
            return false;
        }

        return levenshtein($a, $b) <= (strlen($a) >= 9 ? 2 : 1);
    }
}
