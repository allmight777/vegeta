<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents_ia', function (Blueprint $table) {
            // Compteur simple (16_PROMPT §4.2) : nombre de fois où le document a servi à répondre.
            $table->unsignedInteger('nombre_utilisations')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('documents_ia', function (Blueprint $table) {
            $table->dropColumn('nombre_utilisations');
        });
    }
};
