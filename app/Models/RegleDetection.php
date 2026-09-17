<?php

namespace App\Models;

use App\Enums\SourceValeur;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegleDetection extends Model
{
    use HasFactory;

    protected $table = 'regles_detection';

    protected $fillable = ['code', 'libelle', 'actif', 'parametres', 'source', 'reference_texte'];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'parametres' => 'array',
            'source' => SourceValeur::class,
        ];
    }

    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class);
    }
}
