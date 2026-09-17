<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agence extends Model
{
    use HasFactory;

    protected $table = 'agences';

    protected $fillable = ['reseau_id', 'nom', 'code'];

    public function reseau(): BelongsTo
    {
        return $this->belongsTo(Reseau::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function comptes(): HasMany
    {
        return $this->hasMany(Compte::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }
}
