<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnes_physiques', function (Blueprint $table) {
            $table->string('sexe')->nullable()->after('lieu_naissance');
            $table->string('piece_identite_type')->nullable()->after('sexe');
            // Index aveugle seul (même convention que npi_idx) : jamais de NPI en clair.
            $table->string('validation_methode')->nullable()->after('npi_idx');

            $table->string('telephone')->nullable()->after('revenus_mensuels_estimes');
            $table->text('email')->nullable();
            $table->string('email_idx', 64)->nullable()->index();
            $table->text('domicile')->nullable();
            $table->string('lot')->nullable();
            $table->string('maison')->nullable();
            $table->string('quartier')->nullable();
            $table->string('indication_maison')->nullable();
            $table->string('indication_travail')->nullable();

            $table->text('pere')->nullable();
            $table->text('mere')->nullable();
            $table->text('conjoint')->nullable();
            $table->string('statut_matrimonial')->nullable();
            $table->string('nationalite')->nullable();
            $table->string('employeur')->nullable();

            $table->text('ifu')->nullable();
            $table->text('rccm')->nullable();
            $table->string('activite_1')->nullable();
            $table->string('activite_2')->nullable();

            $table->decimal('droit_adhesion', 20, 2)->nullable();
            $table->decimal('part_sociale', 20, 2)->nullable();
            $table->decimal('depot_especes', 20, 2)->nullable();
            $table->decimal('total_versements_initiaux', 20, 2)->nullable();

            $table->string('signature_titulaire_path')->nullable();
            $table->string('signature_responsable_nom')->nullable();
            $table->string('signature_responsable_fonction')->nullable();
            $table->date('signature_responsable_date')->nullable();

            $table->timestamp('npi_verifie_le')->nullable();
            $table->string('npi_verification_source')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('personnes_physiques', function (Blueprint $table) {
            $table->dropColumn([
                'sexe', 'piece_identite_type', 'validation_methode',
                'telephone', 'email', 'email_idx', 'domicile', 'lot', 'maison', 'quartier',
                'indication_maison', 'indication_travail',
                'pere', 'mere', 'conjoint', 'statut_matrimonial', 'nationalite', 'employeur',
                'ifu', 'rccm', 'activite_1', 'activite_2',
                'droit_adhesion', 'part_sociale', 'depot_especes', 'total_versements_initiaux',
                'signature_titulaire_path', 'signature_responsable_nom',
                'signature_responsable_fonction', 'signature_responsable_date',
                'npi_verifie_le', 'npi_verification_source',
            ]);
        });
    }
};
