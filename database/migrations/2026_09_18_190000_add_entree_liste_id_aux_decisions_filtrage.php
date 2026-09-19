<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nécessaire pour "3 cas similaires écartés" (12_PROMPT_IA_INTEGREE_PROFONDE §6) :
 * `cle_decision` entremêle identité de la cible ET nom aveugle de l'entrée de liste
 * dans un seul hash, donc impossible de retrouver "les autres décisions prises sur
 * cette même entrée de liste" sans stocker la référence séparément. Pas de contrainte
 * FK déclarée, cohérent avec `decisions_filtrage.identite_id` déjà en clé sans FK.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions_filtrage', function (Blueprint $table) {
            $table->uuid('entree_liste_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('decisions_filtrage', function (Blueprint $table) {
            $table->dropColumn('entree_liste_id');
        });
    }
};
