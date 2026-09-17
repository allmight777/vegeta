<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Escalade humaine de l'assistant IA (08_PROMPT_ASSISTANT_IA_CONFORMITE §5) : jamais une
// transmission automatique silencieuse, toujours un geste explicite de l'agent puis du
// responsable. Une fois traitée, la paire question/réponse alimente
// Services\Assistance\BaseConnaissances pour le simulateur.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escalades_assistant_ia', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->string('role_agent');
            $table->text('question');
            $table->string('contexte_ecran');
            $table->text('reponse_ia')->nullable();
            $table->string('statut')->default('en_attente');
            $table->text('reponse_responsable')->nullable();
            $table->foreignId('traitee_par_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->timestamp('traitee_le')->nullable();

            $table->timestamps();

            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalades_assistant_ia');
    }
};
