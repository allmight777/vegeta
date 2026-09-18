<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrégat entretenu à chaque opération : le contrôle du plafond quotidien doit rester en
 * temps réel sur un poste modeste, donc on lit une ligne au lieu de rebalayer l'historique.
 * Traduction de la Loi art. 17 i) : les opérations en espèces multiples d'une même
 * personne dans la journée sont considérées comme une opération unique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cumuls_journaliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('identite_id');
            $table->foreign('identite_id')->references('id')->on('identites')->cascadeOnDelete();

            $table->date('jour');
            $table->string('mode_paiement');

            $table->decimal('total_depots', 20, 2)->default(0);
            $table->decimal('total_retraits', 20, 2)->default(0);
            $table->unsignedInteger('nb_operations')->default(0);
            $table->unsignedInteger('nb_comptes')->default(0);
            $table->unsignedInteger('nb_agences')->default(0);

            $table->timestamps();

            $table->unique(['identite_id', 'jour', 'mode_paiement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cumuls_journaliers');
    }
};
