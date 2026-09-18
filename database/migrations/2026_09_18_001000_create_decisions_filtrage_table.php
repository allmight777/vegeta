<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mémoire des décisions du responsable : une correspondance déjà tranchée ne doit pas
 * revenir tous les jours, sinon le responsable finit par tout écarter en bloc et
 * l'outil devient décoratif.
 *
 * La décision est indexée par `cle_decision` — une empreinte du FAIT constaté (identité
 * ou nom aveugle de la cible + nom aveugle de l'entrée de liste), jamais par un
 * identifiant de ligne. Deux conséquences voulues :
 *  - la décision suit la personne, pas la fiche : tranché une fois pour un membre,
 *    valable pour tous ses dossiers, dans toutes les agences ;
 *  - si le nom du client est corrigé, la clé change et la décision cesse de
 *    s'appliquer. L'invalidation est structurelle, pas déclarative.
 *
 * Aucun nom en clair ici : uniquement des index aveugles et un motif codé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisions_filtrage', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('cle_decision', 64)->unique();

            $table->uuid('identite_id')->nullable()->index();
            $table->string('portee');              // identite | fiche
            $table->string('source_liste');
            $table->string('version_liste')->nullable();

            $table->string('statut');              // ecarte | confirme
            $table->string('motif_code');
            $table->text('motif_detail')->nullable();

            $table->foreignId('decide_par_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            // dateTime, pas timestamp : sous MariaDB (sql_mode strict), seule la
            // première colonne timestamp NOT NULL sans défaut explicite reçoit un
            // défaut implicite (CURRENT_TIMESTAMP) — la suivante ("expire_le") reçoit
            // un défaut '0000-00-00 00:00:00' que le mode strict rejette à la
            // création (erreur 1067). dateTime n'a pas cette contrainte historique et
            // se comporte à l'identique côté Eloquent/Carbon.
            $table->dateTime('decide_le');
            // Une décision ne vaut jamais à vie : sans péremption, un écart unique
            // rendrait un client invisible pour toujours.
            $table->dateTime('expire_le');

            $table->unsignedInteger('applications')->default(0);
            $table->timestamp('derniere_application_le')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions_filtrage');
    }
};