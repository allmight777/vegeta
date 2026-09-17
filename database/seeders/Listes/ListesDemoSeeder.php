<?php

namespace Database\Seeders\Listes;

use App\Models\EntreeListe;
use App\Services\Securite\IndexAveugle;
use Illuminate\Database\Seeder;

/**
 * Entrées de liste fictives (source=demo/ppe_benin/ppe_cedeao). Aucun fichier ONU réel
 * n'est présent dans docs/sources/ pour cette instance : on ne prétend jamais avoir
 * importé la vraie liste consolidée — voir `listes:importer --source=onu` pour un
 * import réel à partir d'un fichier XML local le jour où l'équipe le fournit.
 *
 * Une entrée reprend volontairement le nom d'un client de démonstration
 * (AHOUANDJINOU Rachidatou, voir DemoCoreBankingSeeder) pour rendre le scénario C
 * rejouable sans configuration manuelle.
 */
class ListesDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('cif_demo.actif')) {
            return;
        }

        $entrees = [
            ['source' => 'demo', 'nom' => 'AHOUANDJINOU Rachidatou', 'categorie' => 'Extrait fictif format ONU — démonstration', 'version_liste' => 'DEMO-2026-1'],
            ['source' => 'demo', 'nom' => 'SOSSOU Moctar', 'categorie' => 'Extrait fictif format ONU — démonstration', 'version_liste' => 'DEMO-2026-1'],
            ['source' => 'ppe_benin', 'nom' => 'ALIDOU Bertin', 'categorie' => 'PPE nationale — ministre (fictif)', 'version_liste' => 'PPE-BENIN-DEMO-1'],
            ['source' => 'ppe_benin', 'nom' => 'KOUASSI Delphine', 'categorie' => 'Conjoint de PPE nationale (fictif)', 'version_liste' => 'PPE-BENIN-DEMO-1'],
            ['source' => 'ppe_cedeao', 'nom' => 'TRAORE Salimata', 'categorie' => 'PPE étrangère CEDEAO (fictif)', 'version_liste' => 'PPE-CEDEAO-DEMO-1'],
        ];

        foreach ($entrees as $entree) {
            $idx = app(IndexAveugle::class)->calculer($entree['nom'], 'nom');

            if (EntreeListe::where('nom_idx', $idx)->where('source', $entree['source'])->exists()) {
                continue;
            }

            EntreeListe::create([...$entree, 'importee_le' => now()]);
        }
    }
}
