<?php

namespace App\Models;

use App\Enums\SourceValeur;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Personne physique réelle derrière une ou plusieurs fiches clients (§ identité).
 * Ne porte aucune donnée nominative : seulement un index aveugle du NPI et l'empreinte.
 */
class Identite extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'identites';

    protected $fillable = [
        'reseau_id', 'npi_idx', 'empreinte_combinee',
        'plafond_quotidien_especes', 'source_plafond', 'base_calcul_plafond',
    ];

    protected function casts(): array
    {
        return [
            'plafond_quotidien_especes' => 'decimal:2',
            'source_plafond' => SourceValeur::class,
        ];
    }

    public function reseau(): BelongsTo
    {
        return $this->belongsTo(Reseau::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function rattachements(): HasMany
    {
        return $this->hasMany(RattachementIdentite::class);
    }

    public function cumuls(): HasMany
    {
        return $this->hasMany(CumulJournalier::class);
    }

    /** @return array<int, string> identifiants de toutes les fiches clients de la personne */
    public function clientIds(): array
    {
        return $this->clients()->pluck('id')->all();
    }

    public function comptes()
    {
        return Compte::whereIn('client_id', $this->clientIds());
    }

    /** Une identité rapprochée par empreinte seule reste signalée comme telle à l'écran. */
    public function rapprocheeParNpi(): bool
    {
        return $this->npi_idx !== null;
    }
}
