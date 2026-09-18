<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration additive (le socle "agents" déjà fusionné n'est jamais modifié) : ajoute
 * l'e-mail du responsable d'agence, nécessaire pour l'alerte de conformité envoyée hors
 * de l'application (docs/DECISIONS.md). Chiffré + index aveugle, comme "matricule".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->text('email')->nullable()->after('nom');
            $table->string('email_idx', 64)->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn(['email', 'email_idx']);
        });
    }
};
