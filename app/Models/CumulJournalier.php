<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CumulJournalier extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'cumuls_journaliers';

    protected $fillable = [
        'identite_id', 'jour', 'mode_paiement',
        'total_depots', 'total_retraits', 'nb_operations', 'nb_comptes', 'nb_agences',
    ];

    protected function casts(): array
    {
        return [
            'jour' => 'date',
            'total_depots' => 'decimal:2',
            'total_retraits' => 'decimal:2',
            'nb_operations' => 'integer',
            'nb_comptes' => 'integer',
            'nb_agences' => 'integer',
        ];
    }

    public function identite(): BelongsTo
    {
        return $this->belongsTo(Identite::class);
    }

    /** Ce que la règle du plafond regarde : les entrées d'espèces de la journée. */
    public function totalRetenu(): float
    {
        return (float) $this->total_depots;
    }
}
