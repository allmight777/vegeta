<?php

namespace App\Services\Kyc;

use App\Models\PersonnePhysique;
use App\Services\Empreinte\GenerateurEmpreinte;
use App\Services\Empreinte\ServiceEmpreinte;

/**
 * Copilote de saisie — alerte doublon (16_PROMPT §2.1). Pendant la saisie d'un nom,
 * compare son empreinte à celles des dossiers du réseau (jamais d'un autre réseau : une
 * empreinte n'est comparable qu'à clé identique) et signale qu'un dossier très proche
 * existe déjà.
 *
 * Minimisation : ne renvoie JAMAIS le nom ni l'identifiant du dossier trouvé, seulement
 * le nom de son agence de création. Le guichet apprend qu'un doublon probable existe, pas
 * de qui il s'agit. Fonctionne sans IA ni connexion (empreinte locale).
 */
class DetecteurDoublonEmpreinte
{
    public function __construct(
        private readonly GenerateurEmpreinte $generateur,
        private readonly ServiceEmpreinte $empreinte,
    ) {}

    /**
     * @return array{agence: ?string}|null null si aucun dossier proche
     */
    public function chercher(?string $prenoms, ?string $nom, ?string $dateNaissance, int $reseauId, ?string $clientIdActuel = null): ?array
    {
        $nom = trim((string) $nom);

        if (mb_strlen($nom) < 2) {
            return null;
        }

        $vecteur = $this->generateur->encoderNom(trim(trim((string) $prenoms).' '.$nom))->versOctets();
        $dateSaisie = $this->generateur->normaliserDate($dateNaissance);
        $seuil = (float) config('kyc.copilote.doublon_seuil_nom');

        $candidats = PersonnePhysique::query()
            ->whereNotNull('empreinte_nom')
            ->whereHas('client', function ($requete) use ($reseauId, $clientIdActuel) {
                $requete->where('reseau_id', $reseauId);

                if ($clientIdActuel !== null) {
                    $requete->where('id', '!=', $clientIdActuel);
                }
            })
            ->with('client.agenceCreation')
            ->get();

        $meilleurScore = 0.0;
        $meilleur = null;

        foreach ($candidats as $candidat) {
            // Deux dates de naissance connues et différentes : personnes distinctes.
            $dateCandidat = $this->generateur->normaliserDate($candidat->date_naissance);

            if ($dateSaisie !== null && $dateCandidat !== null && $dateSaisie !== $dateCandidat) {
                continue;
            }

            $score = $this->empreinte->similariteNom($vecteur, $candidat->empreinte_nom);

            if ($score !== null && $score >= $seuil && $score > $meilleurScore) {
                $meilleurScore = $score;
                $meilleur = $candidat;
            }
        }

        return $meilleur === null ? null : ['agence' => $meilleur->client->agenceCreation?->nom];
    }
}
