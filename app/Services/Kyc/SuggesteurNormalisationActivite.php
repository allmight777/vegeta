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
    ];

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
        $groupes = []; // clé normalisée => [orthographe exacte => nombre de dossiers]

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
                $groupes[$this->normaliser((string) $ligne->valeur)][(string) $ligne->valeur] =
                    ($groupes[$this->normaliser((string) $ligne->valeur)][(string) $ligne->valeur] ?? 0) + (int) $ligne->nombre;
            }
        }

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
