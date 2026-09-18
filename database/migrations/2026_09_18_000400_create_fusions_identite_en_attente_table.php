<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zone grise du rapprochement : score trop haut pour être ignoré, trop bas pour fusionner
 * sans risque (cas « AGBO Paul » / « AGBO Pauline »). On crée deux identités distinctes et
 * on propose la fusion au responsable LBC/FT : l'outil recommande, l'humain décide.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fusions_identite_en_attente', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('identite_source_id');
            $table->foreign('identite_source_id')->references('id')->on('identites')->cascadeOnDelete();
            $table->uuid('identite_cible_id');
            $table->foreign('identite_cible_id')->references('id')->on('identites')->cascadeOnDelete();

            $table->decimal('score', 6, 4);
            $table->string('statut')->default('en_attente');
            $table->foreignId('decide_par_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->text('motif')->nullable();
            $table->timestamp('decide_le')->nullable();

            $table->timestamps();

            $table->index(['statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fusions_identite_en_attente');
    }
};
