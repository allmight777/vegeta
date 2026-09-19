<?php

namespace App\Services\Listes;

use App\Enums\SourceListeType;
use App\Models\EntreeListe;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportateurListes
{
    /**
     * Importe un fichier Excel/CSV contenant des entrées de liste (sanctions/PPE).
     * Colonnes attendues : nom, prenom, npi, pays, telephone, categorie.
     * La première ligne est considérée comme l'en-tête.
     */
    public function importer(
        UploadedFile $fichier,
        SourceListeType $source,
        string $version,
        ?string $categorie = null
    ): int {
        // Excel::toArray attend une instance d'une classe qui implémente
        // Maatwebsite\Excel\Concerns\Import — pas une classe anonyme vide.
        // On utilise la classe NoopImport fournie avec ce service.
        $lignes = Excel::toArray(new NoopImport, $fichier)[0] ?? [];

        if (empty($lignes)) {
            return 0;
        }

        // Première ligne = en-tête
        $entetes = array_map(
            fn ($v) => mb_strtolower(trim((string) $v)),
            array_shift($lignes)
        );

        // Index des colonnes attendues (permet l'ordre libre des colonnes)
        $idx = array_flip($entetes);

        $compteur = 0;

        DB::transaction(function () use ($lignes, $idx, $source, $version, $categorie, &$compteur) {
            foreach ($lignes as $ligne) {
                $nom = trim((string) ($this->valeurBrute($ligne, $idx, 'nom') ?? ''));
                $prenom = trim((string) ($this->valeurBrute($ligne, $idx, 'prenom') ?? ''));

                // Ligne sans nom et sans prénom : ignorée
                if ($nom === '' && $prenom === '') {
                    continue;
                }

                EntreeListe::create([
                    'source' => $source,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'npi' => $this->valeur($ligne, $idx, 'npi'),
                    'pays' => $this->valeur($ligne, $idx, 'pays'),
                    'telephone' => $this->valeur($ligne, $idx, 'telephone'),
                    'categorie' => $categorie
                        ?? $this->valeur($ligne, $idx, 'categorie')
                        ?? 'PPE',
                    'version_liste' => $version,
                    'importee_le' => now(),
                ]);

                $compteur++;
            }
        });

        return $compteur;
    }

    /**
     * Retourne la valeur brute d'une cellule (non nettoyée), utilisée pour
     * nom et prénom afin de préserver les espaces internes significatifs.
     */
    private function valeurBrute(array $ligne, array $idx, string $cle): ?string
    {
        $position = $idx[$cle] ?? null;

        if ($position === null) {
            return null;
        }

        $valeur = $ligne[$position] ?? null;

        return $valeur === null ? null : (string) $valeur;
    }

    /**
     * Retourne une valeur nettoyée (trim + null si vide).
     */
    private function valeur(array $ligne, array $idx, string $cle): ?string
    {
        $valeur = $this->valeurBrute($ligne, $idx, $cle);

        if ($valeur === null) {
            return null;
        }

        $valeur = trim($valeur);

        return $valeur === '' ? null : $valeur;
    }
}
