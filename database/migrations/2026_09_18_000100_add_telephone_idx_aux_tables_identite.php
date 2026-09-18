<?php

use App\Services\Securite\Chiffrement;
use App\Services\Securite\IndexAveugle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `telephone` était jusqu'ici stocké en clair sur personnes_physiques/personnes_morales/
 * signataires (oubli du chantier fiche d'adhésion — CLAUDE.md §5 exige le chiffrement de
 * tout champ d'identité). Ajoute l'index aveugle nécessaire au rapprochement par numéro
 * (recherche exacte uniquement, jamais de LIKE sur une colonne chiffrée) et rechiffre les
 * valeurs déjà présentes. Voir docs/DECISIONS.md §16.
 */
return new class extends Migration
{
    private const TABLES = ['personnes_physiques', 'personnes_morales', 'signataires'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('telephone_idx')->nullable()->index();
            });
        }

        $chiffrement = app(Chiffrement::class);
        $indexAveugle = app(IndexAveugle::class);

        foreach (self::TABLES as $table) {
            DB::table($table)->whereNotNull('telephone')->where('telephone', '!=', '')->orderBy('id')->get()->each(function ($ligne) use ($table, $chiffrement, $indexAveugle) {
                // Idempotent : une valeur déjà rechiffrée par ce script porte le préfixe "v1:".
                if (str_starts_with((string) $ligne->telephone, 'v1:')) {
                    return;
                }

                DB::table($table)->where('id', $ligne->id)->update([
                    'telephone' => $chiffrement->chiffrer($ligne->telephone),
                    'telephone_idx' => $indexAveugle->calculer($ligne->telephone, 'telephone'),
                ]);
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('telephone_idx');
            });
        }
    }
};
