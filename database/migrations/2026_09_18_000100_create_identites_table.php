<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Une identité = une personne physique réelle, qui peut porter plusieurs fiches clients
 * (donc plusieurs comptes) dans une ou plusieurs agences. Elle absorbe la différence
 * entre une caisse à base centralisée (1 client = 1 identité) et des agences à bases
 * séparées (N clients rapprochés = 1 identité) : les règles de cumul, elles, ne changent
 * jamais. Aucun nom ni NPI en clair ici : index aveugle et empreinte uniquement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('reseau_id')->constrained('reseaux')->restrictOnDelete();

            $table->string('npi_idx', 64)->nullable();
            $table->binary('empreinte_combinee')->nullable();

            $table->decimal('plafond_quotidien_especes', 20, 2)->nullable();
            $table->string('source_plafond')->default('demo');
            $table->string('base_calcul_plafond')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['reseau_id', 'npi_idx']);
            $table->index(['reseau_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identites');
    }
};
