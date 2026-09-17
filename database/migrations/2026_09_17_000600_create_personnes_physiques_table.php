<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnes_physiques', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->unique('client_id');

            $table->text('nom');
            $table->text('prenoms');
            $table->string('nom_idx', 64)->index();
            $table->text('date_naissance')->nullable();
            $table->text('lieu_naissance')->nullable();
            // Index aveugle seul (pas de valeur en clair stockée) : le NPI ne sert qu'au
            // rapprochement de doublons, jamais réaffiché par cette couche de conformité.
            $table->string('npi_idx', 64)->nullable()->index();
            $table->text('piece_identite_numero')->nullable();
            $table->date('piece_identite_expiration')->nullable();
            $table->text('adresse')->nullable();
            $table->string('profession')->nullable();
            $table->decimal('revenus_mensuels_estimes', 20, 2)->nullable();

            $table->binary('empreinte_nom')->nullable();
            $table->binary('empreinte_combinee')->nullable();
            $table->json('champs_manquants');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnes_physiques');
    }
};
