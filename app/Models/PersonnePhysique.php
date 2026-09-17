<?php

namespace App\Models;

use App\Casts\Chiffre;
use App\Casts\ChiffreIndexe;
use App\Services\Empreinte\ServiceEmpreinte;
use App\Services\Securite\IndexAveugle;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonnePhysique extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'personnes_physiques';

    /** Champs modifiables depuis un mapping d'import ou l'écran de complétude agent. */
    public const CHAMPS_COMPLETABLES = [
        'nom' => true, 'prenoms' => true, 'date_naissance' => true, 'sexe' => true,
        'lieu_naissance' => true, 'piece_identite_type' => true,
        'piece_identite_numero' => true, 'piece_identite_expiration' => true,
        'validation_methode' => true, 'adresse' => true, 'telephone' => true, 'email' => true,
        'domicile' => true, 'lot' => true, 'maison' => true, 'quartier' => true,
        'indication_maison' => true, 'indication_travail' => true,
        'pere' => true, 'mere' => true, 'conjoint' => true, 'statut_matrimonial' => true,
        'nationalite' => true, 'employeur' => true, 'profession' => true,
        'ifu' => true, 'rccm' => true, 'activite_1' => true, 'activite_2' => true,
        'revenus_mensuels_estimes' => true,
        'droit_adhesion' => true, 'part_sociale' => true, 'depot_especes' => true,
        'signature_titulaire_path' => true, 'signature_responsable_nom' => true,
        'signature_responsable_fonction' => true, 'signature_responsable_date' => true,
    ];

    protected $fillable = [
        'client_id', 'nom', 'prenoms', 'date_naissance', 'sexe', 'lieu_naissance',
        'piece_identite_type', 'piece_identite_numero', 'piece_identite_expiration',
        'validation_methode', 'adresse', 'telephone', 'email', 'domicile', 'lot', 'maison',
        'quartier', 'indication_maison', 'indication_travail',
        'pere', 'mere', 'conjoint', 'statut_matrimonial', 'nationalite', 'employeur',
        'profession', 'ifu', 'rccm', 'activite_1', 'activite_2', 'revenus_mensuels_estimes',
        'droit_adhesion', 'part_sociale', 'depot_especes', 'total_versements_initiaux',
        'signature_titulaire_path', 'signature_responsable_nom',
        'signature_responsable_fonction', 'signature_responsable_date',
        'champs_manquants',
    ];

    protected function casts(): array
    {
        return [
            'nom' => ChiffreIndexe::class.':nom_idx,nom',
            'prenoms' => Chiffre::class,
            'date_naissance' => Chiffre::class,
            'lieu_naissance' => Chiffre::class,
            'piece_identite_numero' => Chiffre::class,
            'piece_identite_expiration' => 'date',
            'adresse' => Chiffre::class,
            'email' => ChiffreIndexe::class.':email_idx,email',
            'domicile' => Chiffre::class,
            'pere' => Chiffre::class,
            'mere' => Chiffre::class,
            'conjoint' => Chiffre::class,
            'revenus_mensuels_estimes' => 'decimal:2',
            'droit_adhesion' => 'decimal:2',
            'part_sociale' => 'decimal:2',
            'depot_especes' => 'decimal:2',
            'total_versements_initiaux' => 'decimal:2',
            'signature_responsable_date' => 'date',
            'npi_verifie_le' => 'datetime',
            'champs_manquants' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $personne) {
            if ($personne->isDirty(['nom', 'prenoms', 'date_naissance'])) {
                app(ServiceEmpreinte::class)->calculerPourPersonnePhysique($personne);
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

    public function mandataires(): HasMany
    {
        return $this->hasMany(Mandataire::class);
    }

    /**
     * Écrit npi (jamais stocké en clair) uniquement sous forme d'index aveugle,
     * utilisé pour le rapprochement de doublons à l'import — jamais réaffiché.
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
