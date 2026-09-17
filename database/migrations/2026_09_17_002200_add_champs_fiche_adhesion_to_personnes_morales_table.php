<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnes_morales', function (Blueprint $table) {
            $table->text('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->text('email')->nullable();
            $table->string('email_idx', 64)->nullable()->index();

            $table->string('activite_1')->nullable();
            $table->string('activite_2')->nullable();
            $table->decimal('revenus_mensuels_estimes', 20, 2)->nullable();
            $table->text('beneficiaire_effectif_texte')->nullable();
            $table->uuid('beneficiaire_effectif_signataire_id')->nullable();
            $table->foreign('beneficiaire_effectif_signataire_id')->references('id')->on('signataires')->nullOnDelete();

            $table->decimal('droit_adhesion', 20, 2)->nullable();
            $table->decimal('part_sociale', 20, 2)->nullable();
            $table->decimal('depot_especes', 20, 2)->nullable();
            $table->decimal('total_versements_initiaux', 20, 2)->nullable();

            $table->string('signature_representants_path')->nullable();
            $table->string('signature_responsable_nom')->nullable();
            $table->string('signature_responsable_fonction')->nullable();
            $table->date('signature_responsable_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('personnes_morales', function (Blueprint $table) {
            $table->dropColumn([
                'adresse', 'telephone', 'email', 'email_idx',
                'activite_1', 'activite_2', 'revenus_mensuels_estimes',
                'beneficiaire_effectif_texte', 'beneficiaire_effectif_signataire_id',
                'droit_adhesion', 'part_sociale', 'depot_especes', 'total_versements_initiaux',
                'signature_representants_path', 'signature_responsable_nom',
                'signature_responsable_fonction', 'signature_responsable_date',
            ]);
        });
    }
};
