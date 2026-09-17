<?php

namespace App\Models;

use App\Casts\Chiffre;
use App\Casts\ChiffreIndexe;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Mandataire désigné par une personne physique — distinct de RoleSignataire::Mandataire
 * (signataire d'une personne morale), cf. docs/DECISIONS.md.
 */
class Mandataire extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'mandataires';

    protected $fillable = ['personne_physique_id', 'nom', 'prenoms', 'lien_parente'];

    protected function casts(): array
    {
        return [
            'nom' => ChiffreIndexe::class.':nom_idx,nom',
            'prenoms' => Chiffre::class,
        ];
    }

    public function personnePhysique(): BelongsTo
    {
        return $this->belongsTo(PersonnePhysique::class);
    }
}
