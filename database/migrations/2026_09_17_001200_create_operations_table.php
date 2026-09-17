<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes')->restrictOnDelete();
            $table->foreignId('agence_id')->constrained('agences')->restrictOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();

            $table->string('type');
            $table->decimal('montant', 20, 2);
            $table->string('devise_code', 3)->default('XOF');
            $table->timestamp('effectuee_le');
            $table->string('canal');

            $table->timestamps();

            $table->index(['compte_id', 'effectuee_le']);
            $table->index(['agence_id', 'effectuee_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
