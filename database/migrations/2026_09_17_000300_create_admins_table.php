<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// reseau_id null = admin plateforme (aucun accès à la clientèle) ; sinon admin réseau.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseau_id')->nullable()->constrained('reseaux')->restrictOnDelete();
            $table->string('nom');
            $table->text('email');
            $table->string('email_idx', 64)->unique();
            $table->string('mot_de_passe');
            $table->boolean('actif')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
