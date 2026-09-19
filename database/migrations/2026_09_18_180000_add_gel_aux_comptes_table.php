<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->timestamp('gele_le')->nullable();
            $table->foreignId('gele_par_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->text('motif_gel')->nullable();
            $table->timestamp('leve_le')->nullable();
            $table->foreignId('leve_par_agent_id')->nullable()->constrained('agents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gele_par_agent_id');
            $table->dropConstrainedForeignId('leve_par_agent_id');
            $table->dropColumn(['gele_le', 'motif_gel', 'leve_le']);
        });
    }
};
