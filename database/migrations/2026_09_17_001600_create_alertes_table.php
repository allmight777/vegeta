<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->restrictOnDelete();
            $table->foreignId('regle_detection_id')->nullable()->constrained('regles_detection')->nullOnDelete();
            $table->uuid('resultat_filtrage_id')->nullable();
            $table->foreign('resultat_filtrage_id')->references('id')->on('resultats_filtrage')->nullOnDelete();

            $table->string('gravite');
            $table->text('explication_texte');
            // Faits techniques uniquement (montants, dates, identifiants) : jamais de nom en clair.
            $table->json('faits');
            $table->string('statut')->default('nouvelle');
            $table->foreignId('traitee_par_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->timestamp('traitee_le')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'statut']);
            $table->index(['statut', 'gravite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes');
    }
};
