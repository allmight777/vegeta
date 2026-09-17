<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Chaque signataire/mandataire/bénéficiaire effectif est filtré individuellement (problème 4).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signataires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('personne_morale_id');
            $table->foreign('personne_morale_id')->references('id')->on('personnes_morales')->cascadeOnDelete();

            $table->text('nom');
            $table->string('nom_idx', 64)->index();
            $table->string('role');
            $table->decimal('pourcentage_detention', 5, 2)->nullable();
            $table->string('statut_ppe')->default('non_ppe');
            $table->string('statut_filtrage')->default('a_verifier');
            $table->binary('empreinte_nom')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['personne_morale_id', 'statut_filtrage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signataires');
    }
};
