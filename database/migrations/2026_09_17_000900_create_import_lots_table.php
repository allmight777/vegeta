<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_lots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom_fichier');
            $table->foreignId('agence_id')->constrained('agences')->restrictOnDelete();
            $table->unsignedInteger('nombre_lignes')->default(0);
            $table->unsignedInteger('nombre_nouveaux')->default(0);
            $table->unsignedInteger('nombre_a_completer')->default(0);
            $table->string('statut')->default('en_cours');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_lots');
    }
};
