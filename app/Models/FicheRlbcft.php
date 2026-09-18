<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fiche complémentaire de validation RLBC/FT — une ligne par client OU par signataire
 * contrôlé (polymorphe). Strictement invisible au rôle caissier (Loi art. 63) : jamais
 * chargée ni rendue sans Agent::estResponsableAgence().
 */
class FicheRlbcft extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'fiches_rlbcft';

    protected $fillable = [
        'controlable_type', 'controlable_id',
        'ppe_national', 'ppe_etranger', 'sanction_financiere_internationale',
        'financement_terrorisme', 'visa_rlbcft_nom', 'visa_rlbcft_date',
    ];

    protected function casts(): array
    {
        return [
            'ppe_national' => 'boolean',
            'ppe_etranger' => 'boolean',
            'sanction_financiere_internationale' => 'boolean',
            'financement_terrorisme' => 'boolean',
            'visa_rlbcft_date' => 'date',
        ];
    }

    public function controlable(): MorphTo
    {
        return $this->morphTo();
    }
}
