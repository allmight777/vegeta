<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Documents (PDF/Word) déposés par lot pour extraction assistée (06_PROMPT §5). Le
// import_lot_id regroupe un batch d'upload — sans lien avec import_lots (import CSV
// core banking, domaine différent).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('import_lot_id')->index();

            $table->string('chemin_fichier');
            $table->string('nom_fichier_original');
            $table->string('type_mime');
            $table->string('type_client_devine')->nullable();
            $table->string('statut_extraction')->default('en_attente');
            $table->string('methode_extraction')->nullable();
            $table->json('donnees_extraites')->nullable();

            $table->uuid('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();

            $table->foreignId('traite_par_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->timestamp('traite_le')->nullable();
            $table->text('erreur_message')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_clients');
    }
};
