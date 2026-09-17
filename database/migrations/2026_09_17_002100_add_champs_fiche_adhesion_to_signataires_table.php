<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signataires', function (Blueprint $table) {
            $table->text('date_naissance')->nullable();
            $table->string('sexe')->nullable();
            $table->text('lieu_naissance')->nullable();
            $table->string('piece_identite_type')->nullable();
            $table->text('piece_identite_numero')->nullable();
            $table->date('piece_identite_expiration')->nullable();
            $table->string('validation_methode')->nullable();
            $table->string('nationalite')->nullable();
            $table->text('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('fonction')->nullable();

            // Index aveugle seul, même convention que personnes_physiques.npi_idx.
            $table->string('npi_idx', 64)->nullable()->index();
            $table->timestamp('npi_verifie_le')->nullable();
            $table->string('npi_verification_source')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('signataires', function (Blueprint $table) {
            $table->dropColumn([
                'date_naissance', 'sexe', 'lieu_naissance', 'piece_identite_type',
                'piece_identite_numero', 'piece_identite_expiration', 'validation_methode',
                'nationalite', 'adresse', 'telephone', 'signature_path', 'fonction',
                'npi_idx', 'npi_verifie_le', 'npi_verification_source',
            ]);
        });
    }
};
