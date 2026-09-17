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
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonnePhysique extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'personnes_physiques';

    /** Champs modifiables depuis un mapping d'import ou l'écran de complétude agent. */
    public const CHAMPS_COMPLETABLES = [
        'nom' => true, 'prenoms' => true, 'date_naissance' => true, 'lieu_naissance' => true,
        'piece_identite_numero' => true, 'piece_identite_expiration' => true, 'adresse' => true,
        'profession' => true, 'revenus_mensuels_estimes' => true,
    ];

    protected $fillable = [
        'client_id', 'nom', 'prenoms', 'date_naissance', 'lieu_naissance',
        'piece_identite_numero', 'piece_identite_expiration', 'adresse',
        'profession', 'revenus_mensuels_estimes', 'champs_manquants',
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
            'revenus_mensuels_estimes' => 'decimal:2',
            'champs_manquants' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $personne) {
            if ($personne->isDirty(['nom', 'prenoms', 'date_naissance'])) {
                app(ServiceEmpreinte::class)->calculerPourPersonnePhysique($personne);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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
