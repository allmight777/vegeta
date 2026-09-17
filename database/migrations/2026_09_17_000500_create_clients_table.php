<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('reseau_id')->constrained('reseaux')->restrictOnDelete();
            $table->string('type');
            $table->string('nature_relation');
            $table->string('statut_ppe')->default('non_ppe');
            $table->unsignedTinyInteger('score_completude_kyc')->default(0);
            $table->string('source_creation');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reseau_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
