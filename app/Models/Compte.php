<?php

namespace App\Models;

use App\Casts\Chiffre;
use App\Casts\ChiffreIndexe;
use App\Enums\StatutCompte;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compte extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'comptes';

    protected $fillable = [
        'client_id', 'agence_id', 'numero', 'statut', 'derniere_operation_le',
        'gele_le', 'gele_par_agent_id', 'motif_gel', 'leve_le', 'leve_par_agent_id',
    ];

    protected function casts(): array
    {
        return [
            'numero' => ChiffreIndexe::class.':numero_idx,numero_compte',
            'statut' => StatutCompte::class,
            'derniere_operation_le' => 'datetime',
            'gele_le' => 'datetime',
            'leve_le' => 'datetime',
            'motif_gel' => Chiffre::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function geleParAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'gele_par_agent_id');
    }

    public function leveParAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'leve_par_agent_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }
}
