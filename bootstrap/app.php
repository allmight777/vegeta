<?php

use App\Http\Middleware\RoleAgentAutorise;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            foreach (glob(__DIR__.'/../routes/admin/*.php') as $fichier) {
                Route::middleware(['web', 'auth:admin'])
                    ->prefix('admin')
                    ->name('admin.')
                    ->group($fichier);
            }

            foreach (glob(__DIR__.'/../routes/agent/*.php') as $fichier) {
                // Le contrôleur permanent (rôle sans agence) n'entre jamais dans l'espace agence.
                Route::middleware(['web', 'auth:agent', 'role.agent:caissier,responsable_agence,guichet,responsable_lbcft,direction'])
                    ->prefix('espace')
                    ->name('agent.')
                    ->group($fichier);
            }

            foreach (glob(__DIR__.'/../routes/controleur/*.php') as $fichier) {
                Route::middleware(['web', 'auth:agent', 'role.agent:controleur_permanent'])
                    ->prefix('espace/controleur')
                    ->name('controleur.')
                    ->group($fichier);
            }

            foreach (glob(__DIR__.'/../routes/responsable/*.php') as $fichier) {
                Route::middleware(['web', 'auth:agent', 'role.agent:responsable_agence'])
                    ->prefix('espace/responsable')
                    ->name('responsable.')
                    ->group($fichier);
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn ($request) => $request->is('admin', 'admin/*')
            ? '/admin/connexion'
            : '/connexion');

        $middleware->alias([
            'role.agent' => RoleAgentAutorise::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
