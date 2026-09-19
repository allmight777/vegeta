<?php

// database/migrations/2026_09_18_000001_ajouter_colonnes_entrees_liste.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entrees_liste', function (Blueprint $table) {
            // Colonnes séparées (l'ancienne colonne `nom` reste pour compat ;
            // `nom` reste alimentée par concaténation pour l'empreinte de filtrage).
            $table->string('prenom')->nullable()->after('nom');
            $table->string('npi')->nullable()->after('prenom');
            $table->string('pays')->nullable()->after('npi');
            $table->string('telephone')->nullable()->after('pays');

            // Index pour accélérer la recherche par NPI/pays
            $table->index('npi');
            $table->index('pays');
        });
    }

    public function down(): void
    {
        Schema::table('entrees_liste', function (Blueprint $table) {
            $table->dropIndex(['npi']);
            $table->dropIndex(['pays']);
            $table->dropColumn(['prenom', 'npi', 'pays', 'telephone']);
        });
    }
};
