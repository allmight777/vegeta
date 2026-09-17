<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Signataire;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'client' => Client::class,
            'signataire' => Signataire::class,
        ]);

        // 5 tentatives / minute par e-mail ou matricule + IP (CLAUDE.md §7 sécurité).
        RateLimiter::for('connexion', function ($request) {
            $identifiant = (string) ($request->input('email') ?? $request->input('matricule') ?? '');

            return Limit::perMinute(5)->by($identifiant.'|'.$request->ip());
        });

        if (! $this->app->isProduction()) {
            Model::preventLazyLoading();
            Model::preventSilentlyDiscardingAttributes();
        }
    }
}
