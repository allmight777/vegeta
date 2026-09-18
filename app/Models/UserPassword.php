<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPassword extends Model
{
    protected $table = 'users_password';

    protected $fillable = ['agent_id', 'mot_de_passe_chiffre'];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
