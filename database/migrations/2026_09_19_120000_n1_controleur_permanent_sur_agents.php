<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Le contrôleur permanent supervise un réseau, pas une agence (18_PROMPT §2) : agence_id
        // devient nullable (contrainte applicative : obligatoire pour tous les autres rôles) et
        // reseau_id porte son périmètre.
        Schema::table('agents', function (Blueprint $table) {
            $table->foreignId('reseau_id')->nullable()->constrained('reseaux')->restrictOnDelete();
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->unsignedBigInteger('agence_id')->nullable()->change();
        });

        Schema::table('configurations_systeme', function (Blueprint $table) {
            // Accent propre à l'espace contrôleur, réglable depuis Admin > Configuration.
            $table->string('couleur_espace_controleur', 7)->default('#7C3AED');
        });
    }

    public function down(): void
    {
        Schema::table('configurations_systeme', function (Blueprint $table) {
            $table->dropColumn('couleur_espace_controleur');
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reseau_id');
        });
    }
};
