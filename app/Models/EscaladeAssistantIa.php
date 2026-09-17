<?php

namespace App\Models;

use App\Enums\StatutEscalade;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscaladeAssistantIa extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'escalades_assistant_ia';

    protected $fillable = [
        'agent_id', 'role_agent', 'question', 'contexte_ecran', 'reponse_ia',
        'statut', 'reponse_responsable', 'traitee_par_agent_id', 'traitee_le',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutEscalade::class,
            'traitee_le' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function traiteePar(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'traitee_par_agent_id');
    }
}
