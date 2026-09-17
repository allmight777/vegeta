<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnes_morales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->unique('client_id');

            $table->text('raison_sociale');
            $table->string('raison_sociale_idx', 64)->index();
            $table->string('forme_juridique')->nullable();
            $table->date('date_creation')->nullable();
            $table->text('rccm')->nullable();
            $table->string('rccm_idx', 64)->nullable()->index();
            $table->text('ifu')->nullable();
            $table->string('ifu_idx', 64)->nullable()->index();

            $table->binary('empreinte_nom')->nullable();
            $table->json('champs_manquants');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnes_morales');
    }
};
