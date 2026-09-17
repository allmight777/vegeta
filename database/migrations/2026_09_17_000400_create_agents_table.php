<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->restrictOnDelete();
            $table->string('nom');
            $table->text('matricule');
            $table->string('matricule_idx', 64)->unique();
            $table->string('mot_de_passe');
            $table->string('role');
            $table->boolean('actif')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
