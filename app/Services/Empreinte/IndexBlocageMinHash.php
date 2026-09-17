<?php

namespace App\Services\Empreinte;

use App\Services\Securite\GestionnaireCles;
use App\Support\Bitset;

/**
 * Blocage MinHash (20 bandes × 3 hachages, §6.1) : évite de comparer un nouveau client
 * à toute la liste de sanctions un par un lors d'un import de masse.
 *
 * Composant implémenté et testé mais non branché dans le chemin chaud du filtrage/
 * détection de ce MVP (voir docs/DECISIONS.md) : au volume de démonstration (quelques
 * centaines à quelques milliers d'enregistrements), la comparaison Dice directe reste
 * largement sous le temps cible. Utile tel quel pour un import CSV volumineux.
 */
class IndexBlocageMinHash
{
    private const NB_BANDES = 20;

    private const HACHAGES_PAR_BANDE = 3;

    public function __construct(private readonly GestionnaireCles $cles) {}

    /**
     * @return array<int, ?string> signature par bande (null si vecteur vide)
     */
    public function signature(Bitset $vecteur): array
    {
        $positions = $vecteur->positionsActives();
        $cle = $this->cles->cle('blocage');
        $signature = [];

        for ($bande = 0; $bande < self::NB_BANDES; $bande++) {
            $min = null;

            for ($hachage = 0; $hachage < self::HACHAGES_PAR_BANDE; $hachage++) {
                foreach ($positions as $position) {
                    $valeur = unpack('N', hash_hmac('sha256', $bande.'|'.$hachage.'|'.$position, $cle, true))[1];

                    if ($min === null || $valeur < $min) {
                        $min = $valeur;
                    }
                }
            }

            $signature[$bande] = $min === null ? null : substr(hash('sha256', (string) $min), 0, 16);
        }

        return $signature;
    }

    /**
     * @param  array<int, ?string>  $signatureCible
     * @param  array<string, array<int, ?string>>  $signaturesCandidats  clé => signature
     * @return array<string> clés des candidats partageant au moins une bande
     */
    public function candidatsPartageantUneBande(array $signatureCible, array $signaturesCandidats): array
    {
        $resultat = [];

        foreach ($signaturesCandidats as $cle => $signature) {
            for ($bande = 0; $bande < self::NB_BANDES; $bande++) {
                if ($signatureCible[$bande] !== null && $signatureCible[$bande] === $signature[$bande]) {
                    $resultat[] = $cle;
                    break;
                }
            }
        }

        return $resultat;
    }
}
