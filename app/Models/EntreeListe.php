<?php

// app/Models/EntreeListe.php

namespace App\Models;

use App\Casts\ChiffreIndexe;
use App\Enums\SourceListeType;
use App\Services\Empreinte\ServiceEmpreinte;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntreeListe extends Model
{
    use HasFactory;

    protected $table = 'entrees_liste';

    protected $fillable = [
        'source',
        'nom',
        'prenom',
        'npi',
        'pays',
        'telephone',
        'categorie',
        'version_liste',
        'importee_le',
    ];

    protected function casts(): array
    {
        return [
            'source' => SourceListeType::class,
            'nom' => ChiffreIndexe::class.':nom_idx,nom',
            'importee_le' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $entree) {
            // L'empreinte de filtrage est calculée sur nom + prénom concaténés :
            // c'est cette chaîne qui sert à la comparaison "similarité ≥ 70 %"
            // avec les clients. On garde `nom` comme source d'empreinte pour ne
            // pas dupliquer la logique de ServiceEmpreinte.
            if ($entree->isDirty('nom') || $entree->isDirty('prenom')) {
                $entree->nom = trim(
                    ($entree->nom ?? '').' '.($entree->prenom ?? '')
                );

                app(ServiceEmpreinte::class)->calculerPourEntreeListe($entree);
            }
        });
    }

    public function resultatsFiltrage(): HasMany
    {
        return $this->hasMany(ResultatFiltrage::class);
    }

    /**
     * Nom complet masqué pour l'affichage (protection des données des personnes
     * listées, non confirmées comme liées à un client).
     */
    public function nomMasque(): string
    {
        $nom = (string) $this->nom;

        return $nom === '' ? '' : mb_substr($nom, 0, 1).str_repeat('*', max(mb_strlen($nom) - 1, 0));
    }

    /**
     * Nom complet non masqué (usage interne uniquement, ex : export admin).
     */
    public function nomComplet(): string
    {
        return trim(($this->nom ?? '').' '.($this->prenom ?? ''));
    }
}
