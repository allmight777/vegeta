<?php

namespace App\Models;

use App\Casts\ChiffreIndexe;
use App\Enums\RoleSignataire;
use App\Services\Empreinte\ServiceEmpreinte;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonneMorale extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'personnes_morales';

    protected $fillable = [
        'client_id', 'raison_sociale', 'forme_juridique', 'date_creation',
        'rccm', 'ifu', 'champs_manquants',
    ];

    protected function casts(): array
    {
        return [
            'raison_sociale' => ChiffreIndexe::class.':raison_sociale_idx,raison_sociale',
            'rccm' => ChiffreIndexe::class.':rccm_idx,rccm',
            'ifu' => ChiffreIndexe::class.':ifu_idx,ifu',
            'date_creation' => 'date',
            'champs_manquants' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $personne) {
            if ($personne->isDirty('raison_sociale')) {
                app(ServiceEmpreinte::class)->calculerPourPersonneMorale($personne);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function signataires(): HasMany
    {
        return $this->hasMany(Signataire::class);
    }

    public function beneficiaireEffectifConforme(): bool
    {
        $seuil = (float) config('champs_kyc_obligatoires.seuil_beneficiaire_effectif_pourcentage', 25);

        return $this->signataires()
            ->where('role', RoleSignataire::BeneficiaireEffectif->value)
            ->where('pourcentage_detention', '>', $seuil)
            ->exists();
    }
}
