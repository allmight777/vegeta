<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Bibliothèque documentaire de l'assistant IA (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §1) —
// alimentée uniquement par upload direct de l'administrateur, jamais par un connecteur externe.
// `reseau_id`/`agence_id` nullable : un document sans les deux est valable pour toutes les
// agences.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_ia', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('titre');
            $table->string('nom_fichier_original');
            $table->string('chemin_fichier');
            $table->string('type_mime');
            $table->unsignedBigInteger('taille_octets');
            $table->text('contenu_extrait')->nullable();
            $table->string('statut_extraction')->default('en_attente');
            $table->string('methode_extraction')->nullable();
            $table->text('erreur_message')->nullable();

            $table->boolean('visible_caissier')->default(false);
            $table->boolean('visible_responsable_agence')->default(true);
            $table->boolean('visible_administrateur')->default(true);

            $table->foreignId('reseau_id')->nullable()->constrained('reseaux')->nullOnDelete();
            $table->foreignId('agence_id')->nullable()->constrained('agences')->nullOnDelete();

            $table->foreignId('televerse_par_admin_id')->nullable()->constrained('admins')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_ia');
    }
};
