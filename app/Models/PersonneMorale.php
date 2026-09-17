<?php

namespace App\Models;

use App\Casts\Chiffre;
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
        'client_id', 'raison_sociale', 'forme_juridique', 'date_creation', 'adresse',
        'rccm', 'ifu', 'telephone', 'email',
        'activite_1', 'activite_2', 'revenus_mensuels_estimes',
        'beneficiaire_effectif_texte', 'beneficiaire_effectif_signataire_id',
        'droit_adhesion', 'part_sociale', 'depot_especes', 'total_versements_initiaux',
        'signature_representants_path', 'signature_responsable_nom',
        'signature_responsable_fonction', 'signature_responsable_date',
        'champs_manquants',
    ];

    protected function casts(): array
    {
        return [
            'raison_sociale' => ChiffreIndexe::class.':raison_sociale_idx,raison_sociale',
            'rccm' => ChiffreIndexe::class.':rccm_idx,rccm',
            'ifu' => ChiffreIndexe::class.':ifu_idx,ifu',
            'adresse' => Chiffre::class,
            'email' => ChiffreIndexe::class.':email_idx,email',
            'date_creation' => 'date',
            'revenus_mensuels_estimes' => 'decimal:2',
            'droit_adhesion' => 'decimal:2',
            'part_sociale' => 'decimal:2',
            'depot_especes' => 'decimal:2',
            'total_versements_initiaux' => 'decimal:2',
            'signature_responsable_date' => 'date',
            'champs_manquants' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $personne) {
            if ($personne->isDirty('raison_sociale')) {
                app(ServiceEmpreinte::class)->calculerPourPersonneMorale($personne);
            }

            if ($personne->isDirty(['droit_adhesion', 'part_sociale', 'depot_especes'])) {
                $personne->total_versements_initiaux = (float) $personne->droit_adhesion
                    + (float) $personne->part_sociale
                    + (float) $personne->depot_especes;
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

    public function beneficiaireEffectifSignataire(): BelongsTo
    {
        return $this->belongsTo(Signataire::class, 'beneficiaire_effectif_signataire_id');
    }

    public function beneficiaireEffectifConforme(): bool
    {
        $seuil = (float) config('champs_fiche_adhesion.seuil_beneficiaire_effectif_pourcentage', 25);

        return $this->signataires()
            ->where('role', RoleSignataire::BeneficiaireEffectif->value)
            ->where('pourcentage_detention', '>', $seuil)
            ->exists();
    }
}
