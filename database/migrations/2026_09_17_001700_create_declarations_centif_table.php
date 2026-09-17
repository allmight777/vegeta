<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('declarations_centif', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->restrictOnDelete();

            $table->decimal('montant_cumule', 20, 2);
            $table->string('periode');
            $table->string('statut')->default('a_preparer');
            $table->string('fichier_pdf_path')->nullable();
            $table->timestamp('generee_le')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declarations_centif');
    }
};
