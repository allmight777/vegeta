<?php

namespace App\Models;

use App\Casts\Chiffre;
use App\Enums\MotifDecisionFiltrage;
use App\Enums\StatutFiltrage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DecisionFiltrage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'decisions_filtrage';

    protected $fillable = [
        'cle_decision', 'identite_id', 'portee', 'source_liste', 'version_liste',
        'statut', 'motif_code', 'motif_detail',
        'decide_par_agent_id', 'decide_le', 'expire_le',
        'applications', 'derniere_application_le',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutFiltrage::class,
            'motif_code' => MotifDecisionFiltrage::class,
            'motif_detail' => Chiffre::class,
            'decide_le' => 'datetime',
            'expire_le' => 'datetime',
            'derniere_application_le' => 'datetime',
            'applications' => 'integer',
        ];
    }

    public function decidePar(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'decide_par_agent_id');
    }

    public function identite(): BelongsTo
    {
        return $this->belongsTo(Identite::class);
    }

    /**
     * Une décision ne vaut que pour la version de liste sur laquelle elle a été prise :
     * si l'entrée a été modifiée depuis (nouvelle version), la correspondance doit être
     * réexaminée par un humain.
     */
    public function estValidePour(?string $versionListe): bool
    {
        return $this->expire_le->isFuture() && $this->version_liste === $versionListe;
    }

    public function joursRestants(): int
    {
        return max(0, (int) now()->diffInDays($this->expire_le, false));
    }
}