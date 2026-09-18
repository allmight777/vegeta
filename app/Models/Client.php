<?php

namespace App\Models;

use App\Enums\NatureRelation;
use App\Enums\SourceCreation;
use App\Enums\StatutPpe;
use App\Enums\StatutVerificationNpi;
use App\Enums\TypeClient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'reseau_id', 'agence_creation_id', 'identite_id', 'type', 'nature_relation', 'statut_ppe',
        'score_completude_kyc', 'source_creation',
        'statut_verification_npi', 'npi_verifie_le', 'npi_tentatives',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypeClient::class,
            'nature_relation' => NatureRelation::class,
            'statut_ppe' => StatutPpe::class,
            'source_creation' => SourceCreation::class,
            'score_completude_kyc' => 'integer',
            'statut_verification_npi' => StatutVerificationNpi::class,
            'npi_verifie_le' => 'datetime',
            'npi_tentatives' => 'integer',
        ];
    }

    public function reseau(): BelongsTo
    {
        return $this->belongsTo(Reseau::class);
    }

    public function agenceCreation(): BelongsTo
    {
        return $this->belongsTo(Agence::class, 'agence_creation_id');
    }

    /**
     * Clients rattachés à une agence : créés là-bas, ou y ayant au moins un compte —
     * un client n'a pas d'`agence_id` propre (consolidation multi-agences, cf. migration
     * `add_agence_creation_id_to_clients_table`).
     */
    public function scopeDeLAgence(Builder $query, int $agenceId): Builder
    {
        return $query->where(function (Builder $q) use ($agenceId) {
            $q->where('agence_creation_id', $agenceId)
                ->orWhereHas('comptes', fn (Builder $c) => $c->where('agence_id', $agenceId));
        });
    }

    /**
     * La personne physique réelle derrière cette fiche : une identité peut porter
     * plusieurs fiches clients, donc plusieurs comptes, dans plusieurs agences.
     */
    public function identite(): BelongsTo
    {
        return $this->belongsTo(Identite::class);
    }

    /** Toutes les fiches de la même personne, celle-ci comprise. */
    public function clientIdsDeLIdentite(): array
    {
        return $this->identite?->clientIds() ?? [$this->id];
    }

    public function personnePhysique(): HasOne
    {
        return $this->hasOne(PersonnePhysique::class);
    }

    public function personneMorale(): HasOne
    {
        return $this->hasOne(PersonneMorale::class);
    }

    public function comptes(): HasMany
    {
        return $this->hasMany(Compte::class);
    }

    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class);
    }

    public function declarationsCentif(): HasMany
    {
        return $this->hasMany(DeclarationCentif::class);
    }

    public function resultatsFiltrage(): MorphMany
    {
        return $this->morphMany(ResultatFiltrage::class, 'filtrable');
    }

    /**
     * Fiche complémentaire RLBC/FT du client lui-même (distincte de celle de chacun de
     * ses signataires) — jamais chargée ni rendue sans Agent::estResponsableAgence().
     */
    public function ficheRlbcft(): MorphOne
    {
        return $this->morphOne(FicheRlbcft::class, 'controlable');
    }

    /**
     * Statut neutre affichable au guichet (Loi art. 63 : jamais "soupçon", "PPE",
     * "sanction" ni "gel" — problème 6). Ne reflète ni le détail ni la raison réelle.
     */
    public function statutConformiteAffichable(): bool
    {
        $aUneVerificationEnCours = $this->resultatsFiltrage()->where('statut', 'a_verifier')->exists()
            || $this->statut_ppe->value === 'ppe_a_verifier'
            || ($this->type->value === 'personne_morale' && $this->personneMorale?->signataires()->where('statut_filtrage', 'a_verifier')->exists());

        return ! $aUneVerificationEnCours;
    }

    /**
     * Nom affichable de la cible, quel que soit le type (physique/morale) — pour l'écran
     * de décision filtrage. Ne déchiffre que si l'appelant a le droit de voir l'identité.
     */
    public function nomAffichage(): string
    {
        return $this->type === TypeClient::PersonneMorale
            ? (string) $this->personneMorale?->raison_sociale
            : trim(($this->personnePhysique?->prenoms ?? '').' '.($this->personnePhysique?->nom ?? ''));
    }
}
