<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PPE déclarée à l'adhésion (Loi art. 29 : pièces justificatives, origine des fonds), et envoi
// sécurisé de la liste des PPE d'une agence (lien + code d'accès, comme les rapports journaliers).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('ppe_declare')->default(false);
        });

        Schema::create('documents_ppe', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->string('chemin_fichier');
            $table->string('nom_fichier_original');
            $table->string('type_mime');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('partages_liste_ppe', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('agence_id')->constrained('agences')->restrictOnDelete();
            $table->foreignId('genere_par_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->string('fichier_pdf_path');
            $table->text('destinataire_email')->nullable();
            $table->string('code_acces_hash')->nullable();
            $table->string('jeton_partage', 64)->nullable()->unique();
            $table->timestamp('expire_le')->nullable();
            $table->timestamp('envoye_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partages_liste_ppe');
        Schema::dropIfExists('documents_ppe');
        Schema::table('clients', fn (Blueprint $table) => $table->dropColumn('ppe_declare'));
    }
};
