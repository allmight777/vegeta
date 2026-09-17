<?php

namespace App\Models;

use App\Casts\ChiffreIndexe;
use App\Enums\StatutCompte;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compte extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'comptes';

    protected $fillable = ['client_id', 'agence_id', 'numero', 'statut', 'derniere_operation_le'];

    protected function casts(): array
    {
        return [
            'numero' => ChiffreIndexe::class.':numero_idx,numero_compte',
            'statut' => StatutCompte::class,
            'derniere_operation_le' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }
}
