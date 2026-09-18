<?php

namespace App\Models;

use App\Casts\ChiffreIndexe;
use App\Enums\RoleAgent;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;

class Agent extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $table = 'agents';

    protected $fillable = ['agence_id', 'nom', 'matricule', 'email', 'mot_de_passe', 'role', 'actif', 'civilite'];

    protected $hidden = ['mot_de_passe', 'remember_token'];

    protected function casts(): array
    {
        return [
            'matricule' => ChiffreIndexe::class.':matricule_idx,matricule',
            'email' => ChiffreIndexe::class.':email_idx,email',
            'role' => RoleAgent::class,
            'actif' => 'boolean',
        ];
    }

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function estCaissier(): bool
    {
        return $this->role === RoleAgent::Caissier;
    }

    public function estResponsableAgence(): bool
    {
        return $this->role === RoleAgent::ResponsableAgence;
    }

    /** @deprecated Utiliser estCaissier(). */
    public function estGuichet(): bool
    {
        return $this->role === RoleAgent::Guichet;
    }

    /** @deprecated Utiliser estResponsableAgence(). */
    public function estResponsableLbcft(): bool
    {
        return $this->role === RoleAgent::ResponsableLbcft;
    }

    public function motDePasse()
    {
        return $this->hasOne(UserPassword::class, 'agent_id')->latestOfMany();
    }
}
