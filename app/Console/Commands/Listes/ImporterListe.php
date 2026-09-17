<?php

namespace App\Console\Commands\Listes;

use App\Models\EntreeListe;
use App\Services\Securite\IndexAveugle;
use Illuminate\Console\Command;
use League\Csv\Reader;

/**
 * Import d'une liste de sanctions/PPE. Le format ONU réel (XML) n'est pas encore
 * fourni dans docs/sources/ pour cette instance ; seul le format CSV générique
 * (colonnes nom, categorie) est implémenté dans ce MVP — voir docs/DECISIONS.md.
 */
class ImporterListe extends Command
{
    protected $signature = 'listes:importer {source : onu|ppe_benin|ppe_cedeao|demo} {--fichier= : chemin vers un CSV nom,categorie}';

    protected $description = 'Importe une liste de sanctions ou PPE depuis un fichier CSV local (jamais d\'appel réseau).';

    public function handle(IndexAveugle $indexAveugle): int
    {
        $source = $this->argument('source');
        $fichier = $this->option('fichier');

        if (! in_array($source, ['onu', 'ppe_benin', 'ppe_cedeao', 'demo'], true)) {
            $this->error('Source inconnue. Valeurs attendues : onu, ppe_benin, ppe_cedeao, demo.');

            return self::FAILURE;
        }

        if ($fichier === null || ! is_file($fichier)) {
            $this->error('Fichier introuvable. Utilisez --fichier=chemin/vers/liste.csv (colonnes: nom,categorie).');

            return self::FAILURE;
        }

        $reader = Reader::createFromPath($fichier);
        $reader->setHeaderOffset(0);
        $version = 'IMPORT-'.now()->format('YmdHis');

        $ajouts = 0;
        foreach ($reader->getRecords() as $enregistrement) {
            $nom = trim($enregistrement['nom'] ?? '');
            if ($nom === '') {
                continue;
            }

            $idx = $indexAveugle->calculer($nom, 'nom');
            if (EntreeListe::where('nom_idx', $idx)->where('source', $source)->exists()) {
                continue;
            }

            EntreeListe::create([
                'source' => $source,
                'nom' => $nom,
                'categorie' => $enregistrement['categorie'] ?? null,
                'version_liste' => $version,
                'importee_le' => now(),
            ]);
            $ajouts++;
        }

        $this->info("Import terminé : {$ajouts} nouvelle(s) entrée(s) — version {$version}.");

        return self::SUCCESS;
    }
}
