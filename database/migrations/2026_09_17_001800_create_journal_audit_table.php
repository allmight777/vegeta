<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Chaînage SHA-256 façon chaîne de blocs simplifiée : append-only, jamais modifiée ni supprimée.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_audit', function (Blueprint $table) {
            $table->id();
            $table->string('acteur_type');
            $table->unsignedBigInteger('acteur_id')->nullable();
            $table->string('action');
            $table->string('cible_type')->nullable();
            $table->string('cible_id')->nullable();
            $table->char('hash_precedent', 64)->nullable();
            $table->char('hash_courant', 64)->unique();
            $table->timestamp('cree_le')->useCurrent();

            $table->index(['cible_type', 'cible_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_audit');
    }
};
