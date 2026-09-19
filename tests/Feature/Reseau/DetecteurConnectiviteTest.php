<?php

namespace Tests\Feature\Reseau;

use App\Services\Reseau\DetecteurConnectiviteHttp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DetecteurConnectiviteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ce test porte sur la détection HTTP elle-même : on annule le forçage
        // appliqué à toute la suite (phpunit.xml) pour exercer la vraie branche.
        config(['reseau.mode_connectivite' => 'auto']);
    }

    public function test_une_reponse_reussie_signale_la_connectivite_et_est_mise_en_cache(): void
    {
        Cache::flush();
        Http::fake(['*' => Http::response('', 204)]);

        $detecteur = app(DetecteurConnectiviteHttp::class);

        $this->assertTrue($detecteur->estEnLigne());

        // Second appel : ne doit pas déclencher une nouvelle requête (résultat en cache).
        Http::fake(['*' => Http::response('', 500)]);
        $this->assertTrue($detecteur->estEnLigne());
    }

    public function test_une_absence_de_reponse_signale_hors_ligne(): void
    {
        Cache::flush();
        Http::fake(['*' => fn () => throw new ConnectionException('Aucune route vers l\'hôte')]);

        $detecteur = app(DetecteurConnectiviteHttp::class);

        $this->assertFalse($detecteur->estEnLigne());
    }
}
