<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Volontairement 4 règles pour ce MVP resserré (voir 05_PROMPT_MVP_RECENTRE.md §11).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regles_detection', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->boolean('actif')->default(true);
            $table->json('parametres');
            $table->string('source');
            $table->string('reference_texte')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regles_detection');
    }
};
