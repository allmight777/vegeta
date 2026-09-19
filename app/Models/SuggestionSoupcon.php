<?php

namespace App\Models;

use App\Enums\IndicateurSoupcon;
use App\Enums\StatutSuggestionSoupcon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Suggestion, jamais accusation (18_PROMPT §1) : alimente uniquement le tableau de bord du
 * contrôleur permanent. Jamais visible d'un caissier, jamais une alerte ailleurs.
 */
class SuggestionSoupcon extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'suggestions_soupcon';

    protected $fillable = ['client_id', 'reseau_id', 'score', 'indicateurs_detectes', 'genere_le', 'statut', 'controleur_id'];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'indicateurs_detectes' => 'array',
            'genere_le' => 'datetime',
            'statut' => StatutSuggestionSoupcon::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function controleur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'controleur_id');
    }

    public function dossier(): HasOne
    {
        return $this->hasOne(DossierAnalyseSoupcon::class, 'suggestion_soupcon_id');
    }

    /** @return array<int, IndicateurSoupcon> */
    public function indicateurs(): array
    {
        return array_values(array_filter(array_map(
            fn ($cle) => IndicateurSoupcon::tryFrom((string) $cle),
            $this->indicateurs_detectes ?? [],
        )));
    }

    public function scopeDuReseau($requete, int $reseauId)
    {
        return $requete->where('reseau_id', $reseauId);
    }
}
