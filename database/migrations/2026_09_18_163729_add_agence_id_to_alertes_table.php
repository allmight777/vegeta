<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alertes', function (Blueprint $table) {
            // Colonne nullable : les alertes existantes (filtrage_sanction,
            // plafond_quotidien_depasse) ne sont rattachées à aucune agence,
            // c'est normal. Seules les nouvelles alertes multi-agences la
            // renseigneront.
            $table->foreignId('agence_id')
                ->nullable()
                ->after('type')
                ->constrained('agences')
                ->nullOnDelete();

            $table->index('agence_id');
        });
    }

    public function down(): void
    {
        Schema::table('alertes', function (Blueprint $table) {
            $table->dropForeign(['agence_id']);
            $table->dropIndex(['agence_id']);
            $table->dropColumn('agence_id');
        });
    }
};
