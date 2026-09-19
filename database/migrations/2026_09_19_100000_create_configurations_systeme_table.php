<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configurations_systeme', function (Blueprint $table) {
            $table->id();
            $table->string('nom_systeme');
            $table->string('sous_titre');
            $table->string('logo_principal_path')->nullable();
            $table->string('logo_connexion_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('couleur_primaire', 7);
            $table->string('couleur_secondaire', 7);
            $table->string('couleur_accent', 7);
            $table->string('couleur_sombre', 7);
            $table->string('couleur_espace_caissier', 7);
            $table->string('couleur_espace_responsable', 7);
            $table->string('couleur_espace_admin', 7);
            $table->string('couleur_page_connexion', 7);
            $table->unsignedBigInteger('modifie_par_admin_id')->nullable();
            $table->timestamp('modifie_le')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configurations_systeme');
    }
};
