<?php

namespace App\Http\Middleware;

use App\Services\Audit\Consignateur;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint une route agent à une liste de rôles (ex. filtrage.decider, alertes.voir).
 * Usage : ->middleware('role.agent:responsable_agence')
 *
 * Toute tentative refusée est journalisée (problème 6, art. 63 : non-divulgation au
 * caissier) — même une tentative par URL directe doit laisser une trace pour la
 * conformité, jamais un message expliquant pourquoi au demandeur.
 */
class RoleAgentAutorise
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $agent = $request->user('agent');

        if ($agent === null || ! in_array($agent->role->value, $roles, true)) {
            Consignateur::enregistrer('agent', $agent?->id, 'tentative_acces_refusee', 'route', $request->path());
            abort(403);
        }

        return $next($request);
    }
}
