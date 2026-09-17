<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultats_filtrage', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Cible polymorphe : client ou signataire.
            $table->string('filtrable_type');
            $table->uuid('filtrable_id');

            $table->foreignId('entree_liste_id')->constrained('entrees_liste')->restrictOnDelete();
            $table->decimal('score_similarite', 5, 4);
            $table->string('statut')->default('a_verifier');
            $table->foreignId('verifie_par_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->timestamp('verifie_le')->nullable();
            $table->text('motif_ecart')->nullable();

            $table->timestamps();

            $table->unique(['filtrable_type', 'filtrable_id', 'entree_liste_id']);
            $table->index(['statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultats_filtrage');
    }
};
