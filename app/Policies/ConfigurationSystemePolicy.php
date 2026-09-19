<?php

namespace App\Policies;

use App\Models\Admin;

class ConfigurationSystemePolicy
{
    /**
     * Seul un administrateur (guard admin) peut consulter ou modifier l'identité du système.
     * Un agent (caissier, responsable d'agence) est refusé par le type même de l'acteur.
     */
    public function gerer(mixed $acteur): bool
    {
        return $acteur instanceof Admin && $acteur->actif !== false;
    }
}
