<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// File de rattrapage NPI (07_PROMPT_MODE_DEGRADE_NPI_OCR §2.2). npi_chiffre est une
// exception ciblée et temporaire à la règle "index aveugle seul" : un rattrapage différé
// réel doit resoumettre le NPI à VerificateurNpi::verifier(), ce qu'un hash à sens unique
// ne permet pas. Vidé dès que la ligne quitte l'état "en_attente" (voir docs/DECISIONS.md).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifications_npi_en_attente', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->uuid('signataire_id')->nullable();
            $table->foreign('signataire_id')->references('id')->on('signataires')->cascadeOnDelete();

            $table->string('npi_idx', 64)->index();
            $table->text('npi_chiffre')->nullable();

            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('derniere_tentative_le')->nullable();
            $table->unsignedInteger('tentatives')->default(0);
            $table->string('statut')->default('en_attente');

            $table->timestamps();

            $table->index(['statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifications_npi_en_attente');
    }
};
