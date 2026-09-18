<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les clients sont consolidés réseau entier (`reseau_id`), pas d'`agence_id` sur `clients` —
 * un même client peut avoir des comptes dans plusieurs agences (CLAUDE.md §1, consolidation
 * multi-agences). Pour permettre au tableau de bord du responsable d'agence de se limiter à
 * son agence (09_PROMPT_TROIS_PROFILS §4) sans casser cette consolidation, on trace
 * uniquement l'agence où la fiche a été *créée* — un signal complémentaire à `comptes.agence_id`,
 * pas un remplacement. Décision documentée dans docs/DECISIONS.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('agence_creation_id')->nullable()->after('reseau_id')
                ->constrained('agences')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agence_creation_id');
        });
    }
};
