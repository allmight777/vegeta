<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_lignes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('import_lot_id');
            $table->foreign('import_lot_id')->references('id')->on('import_lots')->cascadeOnDelete();
            $table->uuid('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();

            $table->json('donnees_brutes');
            $table->json('champs_manquants');
            $table->string('statut');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_lignes');
    }
};
