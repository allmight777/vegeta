<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleAgent;
use App\Models\Admin;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\ConfigurationSysteme;
use App\Models\JournalAudit;
use App\Models\Reseau;
use App\Services\Configuration\IdentiteSysteme;
use Database\Seeders\ConfigurationSystemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * 14_PROMPT §2 : identité visuelle configurable (nom, logos, couleurs), appliquée
 * partout, avec repli sur les valeurs d'origine et accès réservé à l'administrateur.
 */
class ConfigurationSystemeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        IdentiteSysteme::oublier();
    }

    protected function tearDown(): void
    {
        IdentiteSysteme::oublier();
        foreach (glob(public_path('identite/*_*.*')) ?: [] as $fichier) {
            @unlink($fichier);
        }
        parent::tearDown();
    }

    private function admin(): Admin
    {
        return Admin::create([
            'reseau_id' => null,
            'nom' => 'Admin',
            'email' => 'admin@test.demo',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
        ]);
    }

    private function agent(RoleAgent $role): Agent
    {
        $reseau = Reseau::firstOrCreate(['code' => 'RT'], ['nom' => 'Réseau Test']);
        $agence = Agence::firstOrCreate(['reseau_id' => $reseau->id, 'code' => 'AT'], ['nom' => 'Agence Test']);

        return Agent::create([
            'agence_id' => $agence->id,
            'nom' => 'Agent Test',
            'matricule' => 'M-'.$role->value,
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'role' => $role,
        ]);
    }

    /** Vrai fichier : le MIME est lu dans le contenu, pas déduit du nom (contrairement à fake()). */
    private function fichierReel(string $nom, string $contenu): UploadedFile
    {
        $chemin = tempnam(sys_get_temp_dir(), 'cfg');
        file_put_contents($chemin, $contenu);

        return new UploadedFile($chemin, $nom, null, null, true);
    }

    private function donneesValides(array $surcharge = []): array
    {
        return array_merge(
            ['nom_systeme' => 'Ma Marque', 'sous_titre' => 'Mon sous-titre'],
            array_diff_key(IdentiteSysteme::defauts(), array_flip(IdentiteSysteme::CHAMPS_LOGO) + ['nom_systeme' => 1, 'sous_titre' => 1]),
            $surcharge,
        );
    }

    public function test_le_seeder_cree_la_ligne_unique_aux_valeurs_d_origine(): void
    {
        $this->seed(ConfigurationSystemeSeeder::class);
        $this->seed(ConfigurationSystemeSeeder::class);

        $this->assertSame(1, ConfigurationSysteme::count());
        $ligne = ConfigurationSysteme::first();
        $this->assertSame('CIF-Empreinte', $ligne->nom_systeme);
        $this->assertSame('#F0E535', $ligne->couleur_primaire);
        $this->assertSame('#30C31A', $ligne->couleur_secondaire);
        $this->assertSame('#2C343D', $ligne->couleur_sombre);
    }

    public function test_les_valeurs_par_defaut_correspondent_aux_css_des_layouts(): void
    {
        $css = file_get_contents(resource_path('views/layouts/admin.blade.php'));
        $defauts = IdentiteSysteme::defauts();

        $this->assertStringContainsString('--cif-yellow: '.$defauts['couleur_primaire'], $css);
        $this->assertStringContainsString('--cif-green: '.$defauts['couleur_secondaire'], $css);
        $this->assertStringContainsString('--cif-dark: '.$defauts['couleur_sombre'], $css);
        $this->assertStringContainsString('--cif-accent: '.$defauts['couleur_accent'], file_get_contents(resource_path('views/layouts/responsable.blade.php')));
    }

    public function test_l_admin_voit_l_ecran_et_le_lien_de_navigation(): void
    {
        $this->seed(ConfigurationSystemeSeeder::class);

        $reponse = $this->actingAs($this->admin(), 'admin')->get(route('admin.configuration.index'));

        $reponse->assertOk()->assertSee('Rétablir les valeurs par défaut')->assertSee(route('admin.configuration.index'), false);
    }

    public function test_caissier_et_responsable_sont_refuses(): void
    {
        foreach ([RoleAgent::Caissier, RoleAgent::ResponsableAgence] as $role) {
            $agent = $this->agent($role);

            $this->actingAs($agent, 'agent')->get(route('admin.configuration.index'))->assertRedirect('/admin/connexion');
            $this->actingAs($agent, 'agent')->put(route('admin.configuration.mettre-a-jour'), $this->donneesValides())->assertRedirect('/admin/connexion');

            $this->assertFalse(Gate::forUser($agent)->allows('gerer', ConfigurationSysteme::class));
        }

        $this->assertTrue(Gate::forUser($this->admin())->allows('gerer', ConfigurationSysteme::class));
    }

    public function test_la_modification_s_applique_partout_et_est_journalisee(): void
    {
        $this->seed(ConfigurationSystemeSeeder::class);
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.configuration.mettre-a-jour'), $this->donneesValides(['couleur_primaire' => '#112233']))
            ->assertRedirect(route('admin.configuration.index'))
            ->assertSessionHasNoErrors();

        $page = $this->get(route('admin.configuration.index'));
        $page->assertSee('Ma Marque')->assertSee('--cif-yellow: #112233', false);

        $this->get(route('admin.journal-audit.index'))->assertSee('Ma Marque');

        $ligne = JournalAudit::where('action', 'configuration_systeme_modifiee')->latest('id')->first();
        $this->assertNotNull($ligne);
        $this->assertSame('admin', $ligne->acteur_type);
        $this->assertStringContainsString('nom_systeme', $ligne->cible_id);
        $this->assertStringContainsString('couleur_primaire', $ligne->cible_id);
        $this->assertStringNotContainsString('112233', $ligne->cible_id);

        auth('admin')->logout();
        $this->get('/admin/connexion')->assertOk()->assertSee('Ma Marque')->assertSee('--brand-yellow: #112233', false);
    }

    public function test_couleur_invalide_refusee(): void
    {
        $this->seed(ConfigurationSystemeSeeder::class);

        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.configuration.mettre-a-jour'), $this->donneesValides(['couleur_sombre' => 'red; } body{display:none']))
            ->assertSessionHasErrors('couleur_sombre');

        $this->assertSame('#2C343D', ConfigurationSysteme::first()->couleur_sombre);
    }

    public function test_logo_valide_stocke_et_affiche_puis_faux_png_refuse(): void
    {
        $this->seed(ConfigurationSystemeSeeder::class);
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.configuration.mettre-a-jour'), $this->donneesValides([
                'logo_principal_path' => UploadedFile::fake()->image('logo.png', 64, 64),
            ]))->assertSessionHasNoErrors();

        $chemin = ConfigurationSysteme::first()->logo_principal_path;
        $this->assertFileExists(public_path('identite/'.$chemin));
        $this->get(route('admin.configuration.index'))->assertSee('identite/'.$chemin, false);

        $faux = $this->fichierReel('logo.png', 'ceci est du texte, pas une image');
        $this->put(route('admin.configuration.mettre-a-jour'), $this->donneesValides(['favicon_path' => $faux]))
            ->assertSessionHasErrors('favicon_path');

        $svgActif = $this->fichierReel('logo.svg', '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->put(route('admin.configuration.mettre-a-jour'), $this->donneesValides(['favicon_path' => $svgActif]))
            ->assertSessionHasErrors('favicon_path');

        $trop = UploadedFile::fake()->image('gros.png')->size(3000);
        $this->put(route('admin.configuration.mettre-a-jour'), $this->donneesValides(['favicon_path' => $trop]))
            ->assertSessionHasErrors('favicon_path');
    }

    public function test_reinitialiser_remet_l_identite_d_origine_et_supprime_les_logos(): void
    {
        $this->seed(ConfigurationSystemeSeeder::class);
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->put(route('admin.configuration.mettre-a-jour'), $this->donneesValides([
            'couleur_secondaire' => '#000000',
            'logo_principal_path' => UploadedFile::fake()->image('logo.png'),
        ]));
        $chemin = ConfigurationSysteme::first()->logo_principal_path;

        $this->post(route('admin.configuration.reinitialiser'))->assertRedirect(route('admin.configuration.index'));

        $ligne = ConfigurationSysteme::first();
        $this->assertSame('CIF-Empreinte', $ligne->nom_systeme);
        $this->assertSame('#30C31A', $ligne->couleur_secondaire);
        $this->assertNull($ligne->logo_principal_path);
        $this->assertFileDoesNotExist(public_path('identite/'.$chemin));
        $this->assertSame(1, ConfigurationSysteme::count());
    }

    public function test_sans_ligne_de_configuration_l_application_reste_fonctionnelle(): void
    {
        $this->assertSame(0, ConfigurationSysteme::count());

        $this->get('/admin/connexion')->assertOk()->assertSee('CIF-Empreinte')->assertSee('--brand-yellow: #F0E535', false);
        $this->actingAs($this->admin(), 'admin')->get(route('admin.journal-audit.index'))->assertOk();
    }

    public function test_table_absente_ne_provoque_pas_de_page_blanche(): void
    {
        Schema::drop('configurations_systeme');
        Cache::flush();
        IdentiteSysteme::oublier();

        $this->get('/admin/connexion')->assertOk()->assertSee('CIF-Empreinte');
    }

    public function test_valeur_corrompue_en_base_ramene_au_defaut(): void
    {
        ConfigurationSysteme::create(IdentiteSysteme::defauts() + []);
        ConfigurationSysteme::query()->update(['couleur_primaire' => 'pas-une-couleur', 'nom_systeme' => '']);
        IdentiteSysteme::oublier();

        $this->assertSame('#F0E535', IdentiteSysteme::courante()['couleur_primaire']);
        $this->assertSame('CIF-Empreinte', IdentiteSysteme::nom());
    }

    public function test_les_espaces_caissier_et_responsable_appliquent_nom_et_couleur_d_espace(): void
    {
        $this->seed(ConfigurationSystemeSeeder::class);
        ConfigurationSysteme::query()->update([
            'nom_systeme' => 'Marque Test',
            'sous_titre' => 'Sous test',
            'couleur_espace_caissier' => '#AA0000',
            'couleur_espace_responsable' => '#00AA00',
        ]);
        IdentiteSysteme::oublier();

        $this->actingAs($this->agent(RoleAgent::Caissier), 'agent')->get(route('agent.tableau-de-bord.index'))
            ->assertOk()->assertSee('Marque Test')->assertSee('Sous test')->assertSee('--cif-espace: #AA0000', false);

        $this->actingAs($this->agent(RoleAgent::ResponsableAgence), 'agent')->get(route('responsable.tableau-de-bord.index'))
            ->assertOk()->assertSee('Marque Test')->assertSee('--cif-accent: #00AA00', false);
    }
}
