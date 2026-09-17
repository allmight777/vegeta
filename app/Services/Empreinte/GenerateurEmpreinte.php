<?php

namespace App\Services\Empreinte;

use App\Services\Securite\GestionnaireCles;
use App\Support\Bitset;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * Normalisation → bigrammes → HMAC-SHA256 → filtre de Bloom. Paramètres calibrés
 * (voir 05_PROMPT_MVP_RECENTRE.md §6.1) : ne pas modifier sans nouvelle mesure.
 */
class GenerateurEmpreinte
{
    private const TAILLE_BITS_NOM = 1000;

    private const NB_HACHAGES_NOM = 10;

    private const TAILLE_BITS_DATE = 500;

    private const NB_HACHAGES_DATE = 10;

    private const TAILLE_QGRAMME = 2;

    public function __construct(private readonly GestionnaireCles $cles) {}

    public function normaliser(string $valeur): string
    {
        $ascii = Str::of($valeur)->ascii()->upper()->toString();
        $nettoye = preg_replace('/[^A-Z0-9 ]/', ' ', $ascii) ?? '';

        return trim(preg_replace('/\s+/', ' ', $nettoye) ?? '');
    }

    /**
     * Attend un format non ambigu (ISO 8601 "AAAA-MM-JJ", tel que soumis par un champ
     * <input type="date">) : Carbon::parse interprète "12/05/1990" à l'anglo-saxonne.
     */
    public function normaliserDate(?string $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Ymd');
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Bigrammes par mot (marqueurs _mot_), unis sans tenir compte de l'ordre des mots :
     * neutralise l'inversion nom/prénom (CLAUDE.md §1).
     *
     * @return array<string>
     */
    public function qGrammes(string $normalise): array
    {
        $mots = array_values(array_filter(explode(' ', $normalise), fn ($mot) => $mot !== ''));
        $grammes = [];

        foreach ($mots as $mot) {
            $chaine = '_'.$mot.'_';
            $longueur = mb_strlen($chaine);

            for ($i = 0; $i < $longueur - self::TAILLE_QGRAMME + 1; $i++) {
                $grammes[] = mb_substr($chaine, $i, self::TAILLE_QGRAMME);
            }
        }

        return array_values(array_unique($grammes));
    }

    public function encoderNom(string $nomComplet): Bitset
    {
        return $this->encoder($nomComplet, self::TAILLE_BITS_NOM, self::NB_HACHAGES_NOM, 'nom_complet');
    }

    public function encoderDate(string $dateNormalisee): Bitset
    {
        return $this->encoder($dateNormalisee, self::TAILLE_BITS_DATE, self::NB_HACHAGES_DATE, 'date_naissance');
    }

    private function encoder(string $valeur, int $tailleBits, int $nbHachages, string $champ): Bitset
    {
        $vecteur = Bitset::vide($tailleBits);
        $cle = $this->cles->cle('empreinte');

        foreach ($this->qGrammes($this->normaliser($valeur)) as $gramme) {
            $condense = hash_hmac('sha256', $champ.'|'.$gramme, $cle, true);
            $h1 = unpack('N', substr($condense, 0, 4))[1];
            $h2 = unpack('N', substr($condense, 4, 4))[1] | 1;

            for ($i = 0; $i < $nbHachages; $i++) {
                $position = ($h1 + $i * $h2) % $tailleBits;
                $vecteur->activer($position);
            }
        }

        return $vecteur;
    }
}
