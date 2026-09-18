<?php

namespace Tests\Feature\Authentification;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 11_PROMPT_PAGE_419 : `resources/views/errors/419.blade.php` habille la session expirée
 * par défaut de Laravel et redirige vers la bonne page de connexion selon l'espace
 * d'origine. CSRF étant désactivé automatiquement par le framework pendant les tests
 * (documentation officielle Laravel), on ne peut pas déclencher un vrai 419 par une
 * requête HTTP ici — on rend directement la vue, comme Laravel le ferait pour ce code
 * d'erreur.
 */
class Page419Test extends TestCase
{
    use RefreshDatabase;

    public function test_la_vue_419_affiche_le_message_de_session_expiree_et_le_bouton_de_reconnexion(): void
    {
        $rendu = view('errors.419')->render();

        $this->assertStringContainsString('Session expirée', $rendu);
        $this->assertStringContainsString('Se reconnecter', $rendu);
    }

    public function test_une_origine_admin_redirige_vers_la_connexion_admin(): void
    {
        request()->headers->set('referer', url('/admin/agents'));

        $rendu = view('errors.419')->render();

        $this->assertStringContainsString(route('admin.connexion.creer'), $rendu);
    }

    public function test_une_origine_agent_redirige_vers_la_connexion_agent(): void
    {
        // Partagée par les rôles caissier et responsable d'agence : une seule page de
        // connexion existe pour ces deux rôles (09_PROMPT_TROIS_PROFILS §6).
        request()->headers->set('referer', url('/espace/clients'));

        $rendu = view('errors.419')->render();

        $this->assertStringContainsString(route('agent.connexion.creer'), $rendu);
    }

    public function test_une_origine_responsable_redirige_aussi_vers_la_connexion_agent(): void
    {
        request()->headers->set('referer', url('/espace/responsable'));

        $rendu = view('errors.419')->render();

        $this->assertStringContainsString(route('agent.connexion.creer'), $rendu);
    }

    public function test_une_origine_indeterminee_retombe_sur_la_connexion_admin(): void
    {
        request()->headers->remove('referer');

        $rendu = view('errors.419')->render();

        $this->assertStringContainsString(route('admin.connexion.creer'), $rendu);
    }
}
