<?php

namespace App\Providers;

use App\Contracts\ConnecteurSystemeExistant;
use App\Contracts\DetecteurConnectivite;
use App\Contracts\VerificateurNpi;
use App\Models\Client;
use App\Models\ConfigurationSysteme;
use App\Models\Signataire;
use App\Policies\ConfigurationSystemePolicy;
use App\Services\Coherence\AnalyseurCoherenceProfil;
use App\Services\Coherence\Indicateurs\CompteDePassage;
use App\Services\Coherence\Indicateurs\EcartFluxRevenus;
use App\Services\Coherence\Indicateurs\IncoherenceActiviteCanal;
use App\Services\Configuration\IdentiteSysteme;
use App\Services\Kyc\ConnecteurApiCoreBanking;
use App\Services\Kyc\ConnecteurImportLocal;
use App\Services\Kyc\VerificateurNpiApiReel;
use App\Services\Kyc\VerificateurNpiSimulateur;
use App\Services\Reseau\DetecteurConnectiviteHttp;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DetecteurConnectivite::class, DetecteurConnectiviteHttp::class);

        // Vigilance constante : la liste des indicateurs est déclarée ici et
        // nulle part ailleurs. En ajouter un, c'est écrire une classe et
        // ajouter une ligne — aucune modification du moteur.
        $this->app->singleton(AnalyseurCoherenceProfil::class, fn ($app) => new AnalyseurCoherenceProfil(
            $app->make(EcartFluxRevenus::class),
            $app->make(CompteDePassage::class),
            $app->make(IncoherenceActiviteCanal::class),
        ));

        $this->app->bind(VerificateurNpi::class, fn ($app) => config('kyc.verificateur_npi') === 'api_reelle'
            ? $app->make(VerificateurNpiApiReel::class)
            : $app->make(VerificateurNpiSimulateur::class));

        $this->app->bind(ConnecteurSystemeExistant::class, fn ($app) => config('kyc.connecteur_systeme_existant') === 'api_core_banking'
            ? $app->make(ConnecteurApiCoreBanking::class)
            : $app->make(ConnecteurImportLocal::class));

        // Contracts\ExtracteurDocument n'est plus lié statiquement : le choix de
        // l'implémentation (texte natif / OCR local / IA en ligne) dépend du fichier et
        // de la connectivité — voir Services\Kyc\SelecteurExtracteurDocument.
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

        Gate::policy(ConfigurationSysteme::class, ConfigurationSystemePolicy::class);

        // Identité visuelle (nom, logos, couleurs) injectée une fois dans chaque vue ;
        // lecture mémoïsée + cache, avec repli sur les valeurs d'origine.
        View::composer('*', fn ($vue) => $vue->with('identite', IdentiteSysteme::courante()));

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
