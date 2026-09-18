<?php

namespace App\Models;

use App\Enums\StatutFusionIdentite;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FusionIdentiteEnAttente extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'fusions_identite_en_attente';

    protected $fillable = [
        'identite_source_id', 'identite_cible_id', 'score',
        'statut', 'decide_par_agent_id', 'motif', 'decide_le',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutFusionIdentite::class,
            'score' => 'decimal:4',
            'decide_le' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Identite::class, 'identite_source_id');
    }

    public function cible(): BelongsTo
    {
        return $this->belongsTo(Identite::class, 'identite_cible_id');
    }
}
