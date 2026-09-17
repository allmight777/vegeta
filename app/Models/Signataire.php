<?php

namespace App\Models;

use App\Casts\ChiffreIndexe;
use App\Enums\RoleSignataire;
use App\Enums\StatutFiltrage;
use App\Enums\StatutPpe;
use App\Services\Empreinte\ServiceEmpreinte;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signataire extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'signataires';

    protected $fillable = [
        'personne_morale_id', 'nom', 'role', 'pourcentage_detention',
        'statut_ppe', 'statut_filtrage',
    ];

    protected function casts(): array
    {
        return [
            'nom' => ChiffreIndexe::class.':nom_idx,nom',
            'role' => RoleSignataire::class,
            'statut_ppe' => StatutPpe::class,
            'statut_filtrage' => StatutFiltrage::class,
            'pourcentage_detention' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $signataire) {
            if ($signataire->isDirty('nom')) {
                app(ServiceEmpreinte::class)->calculerPourSignataire($signataire);
            }
        });
    }

    public function personneMorale(): BelongsTo
    {
        return $this->belongsTo(PersonneMorale::class);
    }

    public function resultatsFiltrage(): MorphMany
    {
        return $this->morphMany(ResultatFiltrage::class, 'filtrable');
    }

    public function nomMasque(): string
    {
        $nom = (string) $this->nom;

        return $nom === '' ? '' : mb_substr($nom, 0, 1).str_repeat('*', max(mb_strlen($nom) - 1, 0));
    }
}
