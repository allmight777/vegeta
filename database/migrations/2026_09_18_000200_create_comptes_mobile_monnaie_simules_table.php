<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Annuaire numéro → titulaire entièrement synthétique (source = demo), généré par
 * database/seeders/Demo/AnnuaireMobileMonnaieSimuleSeeder.php. Remplace un vrai appel
 * USSD/API opérateur (impossible depuis une appli web sans matériel/contrat réel, et
 * interdit par le règlement du concours — aucune donnée personnelle réelle, voir
 * docs/DECISIONS.md). Table de référentiel : id auto-incrémenté, comme regles_detection.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comptes_mobile_monnaie_simules', function (Blueprint $table) {
            $table->id();
            $table->text('telephone');
            $table->string('telephone_idx', 64)->unique();
            $table->string('operateur');
            $table->text('nom_titulaire');
            $table->string('source')->default('demo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes_mobile_monnaie_simules');
    }
};
