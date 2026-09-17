<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('statut_verification_npi')->nullable();
            $table->timestamp('npi_verifie_le')->nullable();
            $table->unsignedInteger('npi_tentatives')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['statut_verification_npi', 'npi_verifie_le', 'npi_tentatives']);
        });
    }
};
