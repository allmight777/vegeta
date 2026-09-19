<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers_analyse_soupcon', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('suggestion_soupcon_id')->constrained('suggestions_soupcon')->restrictOnDelete();
            $table->foreignUuid('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('controleur_id')->constrained('agents')->restrictOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('agents')->nullOnDelete();

            // 1. Informations sur le client (nom, compte, date d'ouverture : lus sur le client, pas dupliqués)
            $table->string('type_client');
            $table->string('niveau_risque');

            // 2. Description de l'opération suspecte
            $table->json('dates_operations')->nullable();
            $table->json('montants_concernes')->nullable();
            $table->string('canal')->nullable();
            $table->text('resume_faits')->nullable(); // chiffré (cast Chiffre)

            // 3. Analyse du caractère suspect
            $table->json('indicateurs');
            $table->string('indicateur_autre_texte')->nullable();

            // 4. Décision et suites à donner (contrôleur permanent)
            $table->text('analyse_controleur')->nullable(); // chiffré (cast Chiffre)
            $table->string('avis_technique_controleur')->nullable();
            $table->timestamp('transmis_le')->nullable();

            // Validation (responsable d'agence)
            $table->string('avis_technique_responsable')->nullable();
            $table->timestamp('decide_le')->nullable();

            $table->string('statut')->default('en_cours');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['statut', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers_analyse_soupcon');
    }
};
