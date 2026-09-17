<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reseau extends Model
{
    use HasFactory;

    protected $table = 'reseaux';

    protected $fillable = ['nom', 'code'];

    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
