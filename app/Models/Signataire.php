<?php

namespace App\Models;

use App\Casts\Chiffre;
use App\Casts\ChiffreIndexe;
use App\Enums\RoleSignataire;
use App\Enums\StatutFiltrage;
use App\Enums\StatutPpe;
use App\Services\Empreinte\ServiceEmpreinte;
use App\Services\Securite\IndexAveugle;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signataire extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'signataires';

    protected $fillable = [
        'personne_morale_id', 'nom', 'role', 'pourcentage_detention',
        'statut_ppe', 'statut_filtrage', 'date_naissance', 'sexe', 'lieu_naissance',
        'piece_identite_type', 'piece_identite_numero', 'piece_identite_expiration',
        'validation_methode', 'nationalite', 'adresse', 'telephone', 'signature_path',
        'fonction',
    ];

    protected function casts(): array
    {
        return [
            'nom' => ChiffreIndexe::class.':nom_idx,nom',
            'role' => RoleSignataire::class,
            'statut_ppe' => StatutPpe::class,
            'statut_filtrage' => StatutFiltrage::class,
            'pourcentage_detention' => 'decimal:2',
            'date_naissance' => Chiffre::class,
            'lieu_naissance' => Chiffre::class,
            'piece_identite_numero' => Chiffre::class,
            'piece_identite_expiration' => 'date',
            'adresse' => Chiffre::class,
            'telephone' => ChiffreIndexe::class.':telephone_idx,telephone',
            'npi_verifie_le' => 'datetime',
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

    /**
     * Fiche complémentaire RLBC/FT propre à ce signataire contrôlé (§2.2) — jamais
     * chargée ni rendue sans Agent::estResponsableLbcft().
     */
    public function ficheRlbcft(): MorphOne
    {
        return $this->morphOne(FicheRlbcft::class, 'controlable');
    }

    /**
     * Écrit npi (jamais stocké en clair) uniquement sous forme d'index aveugle, même
     * convention que PersonnePhysique::definirNpi().
     */
    public function definirNpi(?string $npi): void
    {
        $this->npi_idx = $npi === null ? null : app(IndexAveugle::class)->calculer($npi, 'npi');
    }

    public function nomMasque(): string
    {
        $nom = (string) $this->nom;

        return $nom === '' ? '' : mb_substr($nom, 0, 1).str_repeat('*', max(mb_strlen($nom) - 1, 0));
    }
}
