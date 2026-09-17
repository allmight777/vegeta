<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Un réseau (SFD) — table de référence, peu de lignes, id auto-incrémenté.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseaux', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseaux');
    }
};
