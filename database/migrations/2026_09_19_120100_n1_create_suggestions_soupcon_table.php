<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestions_soupcon', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('reseau_id')->constrained('reseaux')->restrictOnDelete();
            $table->decimal('score', 5, 2);
            // Clés = cases à cocher de la fiche officielle (jamais de texte libre à ce stade).
            $table->json('indicateurs_detectes');
            $table->timestamp('genere_le');
            $table->string('statut')->default('nouvelle');
            $table->foreignId('controleur_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reseau_id', 'statut', 'score']);
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestions_soupcon');
    }
};
