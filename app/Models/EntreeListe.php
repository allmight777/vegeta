<?php

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

    protected $fillable = ['source', 'nom', 'categorie', 'version_liste', 'importee_le'];

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
            if ($entree->isDirty('nom')) {
                app(ServiceEmpreinte::class)->calculerPourEntreeListe($entree);
            }
        });
    }

    public function resultatsFiltrage(): HasMany
    {
        return $this->hasMany(ResultatFiltrage::class);
    }

    public function nomMasque(): string
    {
        $nom = (string) $this->nom;

        return $nom === '' ? '' : mb_substr($nom, 0, 1).str_repeat('*', max(mb_strlen($nom) - 1, 0));
    }
}
