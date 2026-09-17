<?php

namespace App\Models;

use App\Enums\StatutImportLot;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportLot extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'import_lots';

    protected $fillable = [
        'nom_fichier', 'agence_id', 'nombre_lignes', 'nombre_nouveaux',
        'nombre_a_completer', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutImportLot::class,
        ];
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(ImportLigne::class);
    }
}
