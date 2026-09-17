<?php

namespace Tests\Feature\Assistance;

use App\Contracts\DetecteurConnectivite;
use App\Services\Assistance\ProviderIaApiExterne;
use App\Services\Assistance\ProviderIaSimulateur;
use App\Services\Assistance\SelecteurProviderIa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelecteurProviderIaTest extends TestCase
{
    use RefreshDatabase;

    private function bindConnectivite(bool $enLigne): void
    {
        $this->app->bind(DetecteurConnectivite::class, fn () => new class($enLigne) implements DetecteurConnectivite
        {
            public function __construct(private readonly bool $enLigne) {}

            public function estEnLigne(): bool
            {
                return $this->enLigne;
            }
        });
    }

    public function test_sans_cle_api_le_simulateur_est_choisi(): void
    {
        $this->bindConnectivite(true);
        config(['assistance.api_cle' => null, 'assistance.forcer_simulateur' => false]);

        $this->assertInstanceOf(ProviderIaSimulateur::class, app(SelecteurProviderIa::class)->choisir());
    }

    public function test_sans_connectivite_le_simulateur_est_choisi_meme_avec_une_cle_api(): void
    {
        $this->bindConnectivite(false);
        config(['assistance.api_cle' => 'une-cle-de-test', 'assistance.forcer_simulateur' => false]);

        $this->assertInstanceOf(ProviderIaSimulateur::class, app(SelecteurProviderIa::class)->choisir());
    }

    public function test_le_mode_demonstration_force_le_simulateur_meme_en_ligne_avec_une_cle(): void
    {
        $this->bindConnectivite(true);
        config(['assistance.api_cle' => 'une-cle-de-test', 'assistance.forcer_simulateur' => true]);

        $this->assertInstanceOf(ProviderIaSimulateur::class, app(SelecteurProviderIa::class)->choisir());
    }

    public function test_avec_cle_connectivite_et_sans_mode_demonstration_le_fournisseur_reel_est_choisi(): void
    {
        $this->bindConnectivite(true);
        config(['assistance.api_cle' => 'une-cle-de-test', 'assistance.forcer_simulateur' => false]);

        $this->assertInstanceOf(ProviderIaApiExterne::class, app(SelecteurProviderIa::class)->choisir());
    }
}
