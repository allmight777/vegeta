<?php

namespace App\Models;

use App\Casts\ChiffreIndexe;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\Access\Authorizable;

class Admin extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $table = 'admins';

    protected $fillable = ['reseau_id', 'nom', 'email', 'mot_de_passe', 'actif'];

    protected $hidden = ['mot_de_passe', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email' => ChiffreIndexe::class.':email_idx,email',
            'actif' => 'boolean',
        ];
    }

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function reseau(): BelongsTo
    {
        return $this->belongsTo(Reseau::class);
    }

    /**
     * Admin plateforme (CIF) : aucun accès aux données de clientèle d'un réseau.
     */
    public function estAdminPlateforme(): bool
    {
        return $this->reseau_id === null;
    }
}
