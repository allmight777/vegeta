<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\EntreeListe;
use App\Models\RegleDetection;
use App\Services\Audit\Consignateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EcransAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'reseau_id' => null,
            'nom' => 'Admin',
            'email' => 'admin@test.demo',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
        ]);
    }

    public function test_ecran_regles_detection_affiche_la_source_de_chaque_regle(): void
    {
        RegleDetection::create([
            'code' => 'SEUIL_MENSUEL_CENTIF',
            'libelle' => 'Seuil mensuel CENTIF',
            'actif' => true,
            'parametres' => ['seuil' => 15000000],
            'source' => 'briefing_cif',
            'reference_texte' => 'Décision n°021/2023/CM/UMOA — à confirmer',
        ]);

        $reponse = $this->actingAs($this->admin(), 'admin')->get(route('admin.regles-detection.index'));

        $reponse->assertOk();
        $reponse->assertSee('SEUIL_MENSUEL_CENTIF');
        $reponse->assertSee('Briefing CIF', false);
    }

    public function test_ecran_listes_masque_les_noms(): void
    {
        EntreeListe::create(['source' => 'demo', 'nom' => 'AHOUANDJINOU Rachidatou', 'categorie' => 'Test']);

        $reponse = $this->actingAs($this->admin(), 'admin')->get(route('admin.listes.index'));

        $reponse->assertOk();
        $reponse->assertDontSee('AHOUANDJINOU Rachidatou');
    }

    public function test_ecran_journal_audit_verifie_la_chaine(): void
    {
        Consignateur::enregistrer('admin', 1, 'connexion');

        $reponse = $this->actingAs($this->admin(), 'admin')->followingRedirects()->post(route('admin.journal-audit.verifier-chaine'));

        $reponse->assertOk();
        $reponse->assertSee('Chaîne intègre');
    }
}
