<?php

namespace App\Models;

use App\Enums\StatutImportLigne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLigne extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'import_lignes';

    protected $fillable = [
        'import_lot_id', 'client_id', 'donnees_brutes', 'champs_manquants', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'donnees_brutes' => 'array',
            'champs_manquants' => 'array',
            'statut' => StatutImportLigne::class,
        ];
    }

    public function importLot(): BelongsTo
    {
        return $this->belongsTo(ImportLot::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
