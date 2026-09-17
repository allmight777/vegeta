<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Démontre le multi-guichets (problème 3 : fractionnement multi-agences).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseau_id')->constrained('reseaux')->restrictOnDelete();
            $table->string('nom');
            $table->string('code');
            $table->timestamps();

            $table->unique(['reseau_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};
