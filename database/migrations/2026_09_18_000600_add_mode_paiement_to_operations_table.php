<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le plafond quotidien, le fractionnement et le seuil de déclaration ne visent que les
 * espèces : sans cette colonne, un virement de salaire déclencherait une alerte.
 * Valeur par défaut « especes » pour ne pas invalider les opérations déjà saisies.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->string('mode_paiement')->default('especes')->after('montant');
            $table->index(['mode_paiement', 'effectuee_le']);
        });
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropIndex(['mode_paiement', 'effectuee_le']);
            $table->dropColumn('mode_paiement');
        });
    }
};
