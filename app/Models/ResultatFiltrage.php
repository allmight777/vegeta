<?php

namespace App\Models;

use App\Casts\Chiffre;
use App\Enums\StatutFiltrage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ResultatFiltrage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'resultats_filtrage';

    protected $fillable = [
        'filtrable_type', 'filtrable_id', 'entree_liste_id', 'score_similarite',
        'statut', 'verifie_par_agent_id', 'verifie_le', 'motif_ecart',
    ];

    protected function casts(): array
    {
        return [
            'score_similarite' => 'decimal:4',
            'statut' => StatutFiltrage::class,
            'verifie_le' => 'datetime',
            'motif_ecart' => Chiffre::class,
        ];
    }

    public function filtrable(): MorphTo
    {
        return $this->morphTo();
    }

    public function entreeListe(): BelongsTo
    {
        return $this->belongsTo(EntreeListe::class);
    }

    public function verifiePar(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'verifie_par_agent_id');
    }
}
