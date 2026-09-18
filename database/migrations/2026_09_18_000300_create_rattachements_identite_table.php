<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trace de chaque rattachement d'une fiche client à une identité : par quelle méthode
 * (NPI vérifié, empreinte, décision humaine), avec quel score. Le responsable LBC/FT
 * doit toujours pouvoir répondre à « pourquoi ces deux comptes sont-ils la même
 * personne ? » — jamais une boîte noire (CLAUDE.md §3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rattachements_identite', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('identite_id');
            $table->foreign('identite_id')->references('id')->on('identites')->restrictOnDelete();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->restrictOnDelete();

            $table->string('methode');
            $table->decimal('score', 6, 4)->nullable();
            $table->foreignId('decide_par_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->text('motif')->nullable();

            $table->timestamps();

            $table->index(['identite_id']);
            $table->index(['client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rattachements_identite');
    }
};
