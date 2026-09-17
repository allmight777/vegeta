<?php

namespace App\Services\Contexte;

use Illuminate\Support\Facades\Auth;

/**
 * Résout le réseau de l'utilisateur courant : agent → réseau de son agence,
 * admin réseau → son réseau, admin plateforme → aucun (accès clientèle interdit).
 *
 * Simplification assumée pour ce MVP (voir docs/DECISIONS.md) : accesseur explicite
 * appelé dans chaque requête plutôt que le scope global + singleton du socle complet.
 */
class ContexteReseau
{
    public function reseauId(): ?int
    {
        if (Auth::guard('agent')->check()) {
            return Auth::guard('agent')->user()->agence->reseau_id;
        }

        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->user()->reseau_id;
        }

        return null;
    }
}
