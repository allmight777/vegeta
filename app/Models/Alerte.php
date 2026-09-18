<?php

namespace App\Models;

use App\Enums\GraviteAlerte;
use App\Enums\StatutAlerte;
use App\Enums\TypeAlerte;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alerte extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'alertes';

    protected $fillable = [
        'type',
        'agence_id',
        'client_id',
        'regle_detection_id',
        'resultat_filtrage_id',
        'gravite',
        'explication_texte',
        'faits',
        'statut',
        'traitee_par_agent_id',
        'traitee_le',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypeAlerte::class,
            'gravite' => GraviteAlerte::class,
            'faits' => 'array',
            'statut' => StatutAlerte::class,
            'traitee_le' => 'datetime',
        ];
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function regleDetection(): BelongsTo
    {
        return $this->belongsTo(RegleDetection::class);
    }

    public function resultatFiltrage(): BelongsTo
    {
        return $this->belongsTo(ResultatFiltrage::class);
    }

    public function traiteePar(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'traitee_par_agent_id');
    }
}
