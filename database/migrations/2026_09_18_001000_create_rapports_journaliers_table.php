<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports_journaliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('agence_id')->constrained('agences')->restrictOnDelete();
            $table->foreignId('genere_par_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('fichier_pdf_path');
            $table->text('destinataire_email')->nullable();
            $table->string('code_acces_hash')->nullable();
            $table->string('jeton_partage', 64)->nullable()->unique();
            $table->timestamp('expire_le')->nullable();
            $table->timestamp('envoye_le')->nullable();
            $table->timestamps();

            $table->index(['agence_id', 'date_debut', 'date_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports_journaliers');
    }
};
