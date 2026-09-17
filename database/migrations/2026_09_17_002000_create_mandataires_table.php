<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Mandataire désigné par une personne physique (procuration limitée), distinct du rôle
// RoleSignataire::Mandataire qui s'applique aux signataires d'une personne morale.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mandataires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('personne_physique_id');
            $table->foreign('personne_physique_id')->references('id')->on('personnes_physiques')->cascadeOnDelete();

            $table->text('nom');
            $table->string('nom_idx', 64)->index();
            $table->text('prenoms')->nullable();
            $table->string('lien_parente')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mandataires');
    }
};
