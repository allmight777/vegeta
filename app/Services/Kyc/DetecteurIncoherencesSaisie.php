<?php

namespace App\Services\Kyc;

use Illuminate\Support\Str;

/**
 * Copilote de saisie — détection d'incohérences (12_PROMPT_IA_INTEGREE_PROFONDE §4,
 * point 1). Jeu de règles PHP déterministes : c'est le chemin qui doit fonctionner
 * hors connexion, sans IA, toujours. Une éventuelle couche IA viendrait s'ajouter en
 * amélioration, jamais en remplacement — ce service reste la garantie de service
 * minimum.
 *
 * Ne reçoit et ne traite que des caractéristiques dérivées, jamais une valeur
 * d'identité (CLAUDE.md §5, prompt §4 « contraintes techniques ») : c'est à l'appelant
 * (le contrôleur, via la requête validée) de n'avoir jamais transmis autre chose.
 *
 * Chaque avertissement est un avis, jamais un blocage (CLAUDE.md §3, dernière ligne :
 * « l'outil recommande, l'humain décide »).
 */
class DetecteurIncoherencesSaisie
{
    /**
     * @return array{avertissements: array<int, array{champ: string, message: string}>, source: string}
     */
    public function verifier(
        ?int $ageCalcule,
        ?string $profession,
        ?float $ratioDepotRevenu,
        bool $pieceExpiree,
    ): array {
        $avertissements = [];

        if ($pieceExpiree) {
            $avertissements[] = [
                'champ' => 'piece_identite_expiration',
                'message' => 'La pièce d\'identité déclarée est déjà expirée — le client devra la '.
                    'renouveler avant toute opération.',
            ];
        }

        if ($ageCalcule !== null && $profession !== null && $profession !== '') {
            $professionNormalisee = Str::of($profession)->ascii()->lower()->toString();
            $ageMinRetraite = (int) config('kyc.copilote.age_min_retraite');

            if (str_contains($professionNormalisee, 'retrait') && $ageCalcule < $ageMinRetraite) {
                $avertissements[] = [
                    'champ' => 'profession',
                    'message' => "Âge déclaré ({$ageCalcule} ans) inhabituel pour la profession « ".
                        'retraité(e) » — vérifiez la date de naissance saisie.',
                ];
            }
        }

        $ratioAlerte = (float) config('kyc.copilote.ratio_depot_revenu_alerte');

        if ($ratioDepotRevenu !== null && $ratioDepotRevenu >= $ratioAlerte) {
            $avertissements[] = [
                'champ' => 'revenus_mensuels_estimes',
                'message' => 'Le revenu déclaré semble faible par rapport au dépôt initial — '.
                    'vérifiez l\'origine des fonds auprès du client (Loi art. 17 i).',
            ];
        }

        return ['avertissements' => $avertissements, 'source' => 'regles_php'];
    }
}
