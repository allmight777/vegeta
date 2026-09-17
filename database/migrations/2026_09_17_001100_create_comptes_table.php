<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->restrictOnDelete();
            $table->foreignId('agence_id')->constrained('agences')->restrictOnDelete();

            $table->text('numero');
            $table->string('numero_idx', 64)->unique();
            $table->string('statut')->default('actif');
            $table->timestamp('derniere_operation_le')->nullable();

            $table->timestamps();

            $table->index(['client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
