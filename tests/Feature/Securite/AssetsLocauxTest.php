<?php

namespace Tests\Feature\Securite;

use Tests\TestCase;

/**
 * CLAUDE.md §2 (« pas de CDN, tous les assets compilés localement ») et démonstration hors
 * connexion : polices et icônes sont servies depuis public/vendor, jamais depuis un CDN.
 * Un CDN réintroduit dans une vue casserait l'interface (Times New Roman + carrés vides)
 * si le wifi de la salle est coupé.
 */
class AssetsLocauxTest extends TestCase
{
    private const HOTES_INTERDITS = ['fonts.googleapis.com', 'fonts.gstatic.com', 'cdnjs.cloudflare.com', 'cdn.jsdelivr.net', 'unpkg.com', 'ajax.googleapis.com', 'use.fontawesome.com', 'kit.fontawesome.com'];

    public function test_aucune_vue_ne_charge_de_ressource_depuis_un_cdn(): void
    {
        $fautifs = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $fichier) {
            if (! $fichier->isFile()) {
                continue;
            }

            $contenu = file_get_contents($fichier->getPathname());

            foreach (self::HOTES_INTERDITS as $hote) {
                if (str_contains($contenu, $hote)) {
                    $fautifs[] = str_replace(base_path().'/', '', $fichier->getPathname())." → $hote";
                }
            }
        }

        $this->assertSame([], $fautifs, "Ressources CDN trouvées :\n".implode("\n", $fautifs));
    }

    public function test_les_fichiers_de_polices_et_d_icones_locaux_sont_presents(): void
    {
        foreach ([
            'vendor/fontawesome/css/all.min.css',
            'vendor/fontawesome/webfonts/fa-solid-900.woff2',
            'vendor/fontawesome/webfonts/fa-regular-400.woff2',
            'vendor/plus-jakarta-sans/font.css',
            'vendor/plus-jakarta-sans/plus-jakarta-sans-latin.woff2',
            'vendor/plus-jakarta-sans/plus-jakarta-sans-latin-ext.woff2',
        ] as $chemin) {
            $this->assertFileExists(public_path($chemin));
            $this->assertGreaterThan(200, filesize(public_path($chemin)), "$chemin est vide ou tronqué");
        }

        $this->assertStringContainsString('woff2', (string) file_get_contents(public_path('vendor/fontawesome/css/all.min.css')));
    }

    public function test_les_pages_pointent_vers_les_assets_locaux(): void
    {
        foreach (['/connexion', '/admin/connexion'] as $url) {
            $page = $this->get($url)->assertOk();

            $page->assertSee('vendor/fontawesome/css/all.min.css', false)
                ->assertSee('vendor/plus-jakarta-sans/font.css', false);

            foreach (self::HOTES_INTERDITS as $hote) {
                $page->assertDontSee($hote, false);
            }
        }
    }
}
