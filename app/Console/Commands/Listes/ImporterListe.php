<?php

namespace App\Console\Commands\Listes;

use App\Models\EntreeListe;
use App\Services\Filtrage\RefiltrageParc;
use App\Services\Securite\IndexAveugle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use League\Csv\Reader;
use Throwable;

/**
 * Import d'une liste de sanctions/PPE. Le format ONU réel (XML) n'est pas encore
 * fourni dans docs/sources/ pour cette instance ; seul le format CSV générique
 * (colonnes nom, categorie) est implémenté dans ce MVP — voir docs/DECISIONS.md.
 */
class ImporterListe extends Command
{
    protected $signature = 'listes:importer
        {source : onu|ppe_benin|ppe_cedeao|demo}
        {--fichier= : chemin vers un CSV nom,categorie}
        {--publiee-le= : date de publication officielle de la liste (mesure du délai réglementaire)}
        {--sans-refiltrage : n\'enchaîne pas le recontrôle du parc (déconseillé)}';

    protected $description = 'Importe une liste de sanctions ou PPE depuis un fichier CSV local (jamais d\'appel réseau).';

    public function handle(IndexAveugle $indexAveugle, RefiltrageParc $refiltrage): int
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

        // Une liste importée mais jamais confrontée au parc existant ne protège de
        // rien : un membre inscrit hier ne repassera plus jamais par le filtrage.
        // L'obligation d'agir « sans délai » (24 h) impose donc d'enchaîner ici.
        if ($this->option('sans-refiltrage')) {
            $this->warn('Refiltrage du parc ignoré (--sans-refiltrage). Obligation « sans délai » non satisfaite : lancer php artisan listes:refiltrer dès que possible.');

            return self::SUCCESS;
        }

        if ($ajouts === 0) {
            $this->line('Aucune nouvelle entrée : refiltrage du parc inutile.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->line('Recontrôle du parc existant contre la liste mise à jour…');

        $publieeLe = null;

        if ($valeur = $this->option('publiee-le')) {
            try {
                $publieeLe = Carbon::parse($valeur);
            } catch (Throwable) {
                $this->warn('Date de publication illisible : le délai réglementaire ne sera pas mesuré.');
            }
        }

        $rapport = $refiltrage->refiltrerTout($publieeLe);

        $this->info($rapport->resume());

        if ($rapport->correspondancesTrouvees() > 0) {
            $this->warn('Des correspondances ont été relevées sur le parc existant : file de filtrage du responsable conformité à traiter.');
        }

        return self::SUCCESS;
    }
}
