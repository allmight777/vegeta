<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrees_liste', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->text('nom');
            $table->string('nom_idx', 64)->index();
            $table->binary('empreinte_nom')->nullable();
            $table->string('categorie')->nullable();
            $table->string('version_liste')->nullable();
            $table->timestamp('importee_le')->nullable();
            $table->timestamps();

            $table->index(['source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrees_liste');
    }
};
