<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Une ligne par client OU par signataire contrôlé (polymorphe) — strictement invisible
// au rôle guichet (Loi art. 63), cf. app/Policies et Agent::estResponsableLbcft().
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiches_rlbcft', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('controlable_type');
            $table->uuid('controlable_id');

            $table->boolean('ppe_national')->nullable();
            $table->boolean('ppe_etranger')->nullable();
            $table->boolean('sanction_financiere_internationale')->nullable();
            $table->boolean('financement_terrorisme')->nullable();
            $table->string('visa_rlbcft_nom')->nullable();
            $table->date('visa_rlbcft_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['controlable_type', 'controlable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiches_rlbcft');
    }
};
