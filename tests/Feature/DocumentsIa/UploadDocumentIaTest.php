<?php

namespace Tests\Feature\DocumentsIa;

use App\Models\Admin;
use App\Models\Agence;
use App\Models\DocumentIa;
use App\Models\Reseau;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §8 : l'upload direct de jusqu'à 10 fichiers
 * fonctionne, chaque fichier est traité indépendamment, et aucune route d'import externe
 * (Drive ou autre) n'existe dans l'application.
 */
class UploadDocumentIaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'reseau_id' => null,
            'nom' => 'Admin Test',
            'email' => 'admin.'.uniqid().'@cif.test',
            'mot_de_passe' => Hash::make('un-mot-de-passe-solide'),
            'actif' => true,
        ]);
    }

    public function test_luploaded_de_plusieurs_fichiers_cree_un_document_traite_independamment_par_fichier(): void
    {
        Storage::fake('documents_ia');
        $admin = $this->admin();

        $reponse = $this->actingAs($admin, 'admin')->post(route('admin.documents-ia.stocker'), [
            'documents' => [
                UploadedFile::fake()->create('guide.txt', 5, 'text/plain'),
                UploadedFile::fake()->create('note.md', 5, 'text/markdown'),
            ],
            'portee' => 'toutes_agences',
            'visible_caissier' => '1',
            'visible_responsable_agence' => '1',
        ]);

        $reponse->assertRedirect(route('admin.documents-ia.index'));
        $this->assertSame(2, DocumentIa::count());

        DocumentIa::all()->each(function (DocumentIa $document) {
            $this->assertTrue($document->visible_caissier);
            $this->assertTrue($document->visible_administrateur);
        });
    }

    public function test_un_fichier_illisible_echoue_sans_empecher_les_autres(): void
    {
        Storage::fake('documents_ia');
        $admin = $this->admin();

        // Un .txt ne contenant que des espaces échoue proprement à l'extraction
        // (ExtracteurDocumentTexteBrut : vide une fois nettoyé) sans bloquer le
        // traitement du fichier valide du même lot. Contenu non vide au niveau octet
        // (donc un type MIME text/plain détecté normalement), vide seulement une fois
        // trim() appliqué — évite toute dépendance à la détection MIME d'un fichier
        // réellement vide.
        $fichierVide = UploadedFile::fake()->createWithContent('vide.txt', "   \n  ");
        $bonTexte = UploadedFile::fake()->createWithContent('guide.txt', 'Procédure de guichet : vérifier la pièce d\'identité avant toute opération.');

        $this->actingAs($admin, 'admin')->post(route('admin.documents-ia.stocker'), [
            'documents' => [$fichierVide, $bonTexte],
            'portee' => 'toutes_agences',
        ]);

        $this->assertSame(2, DocumentIa::count());
        $this->assertSame(1, DocumentIa::where('statut_extraction', 'echouee')->count());
        $this->assertSame(1, DocumentIa::where('statut_extraction', 'reussie')->count());
    }

    public function test_la_limite_de_dix_fichiers_est_appliquee(): void
    {
        Storage::fake('documents_ia');
        $admin = $this->admin();

        $documents = array_map(
            fn (int $i) => UploadedFile::fake()->create("fichier{$i}.txt", 5, 'text/plain'),
            range(1, 11),
        );

        $reponse = $this->actingAs($admin, 'admin')->post(route('admin.documents-ia.stocker'), [
            'documents' => $documents,
            'portee' => 'toutes_agences',
        ]);

        $reponse->assertSessionHasErrors('documents');
    }

    public function test_un_document_supprime_disparait_de_la_liste_administrateur(): void
    {
        Storage::fake('documents_ia');
        $admin = $this->admin();

        $document = DocumentIa::create([
            'titre' => 'À supprimer',
            'nom_fichier_original' => 'a-supprimer.txt',
            'chemin_fichier' => 'fake/a-supprimer.txt',
            'type_mime' => 'text/plain',
            'taille_octets' => 10,
            'statut_extraction' => 'reussie',
        ]);

        $this->actingAs($admin, 'admin')->delete(route('admin.documents-ia.supprimer', $document));

        $this->assertSoftDeleted($document);
    }

    public function test_aucune_route_dimport_externe_google_drive_ou_autre_nexiste(): void
    {
        $noms = collect(Route::getRoutes())->map(fn ($route) => $route->getName());

        $this->assertTrue($noms->every(
            fn ($nom) => $nom === null || (! str_contains(strtolower((string) $nom), 'drive') && ! str_contains(strtolower((string) $nom), 'google'))
        ));
    }

    public function test_un_agence_est_correctement_associee_quand_la_portee_est_agence(): void
    {
        Storage::fake('documents_ia');
        $admin = $this->admin();
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);

        $this->actingAs($admin, 'admin')->post(route('admin.documents-ia.stocker'), [
            'documents' => [UploadedFile::fake()->create('guide.txt', 5, 'text/plain')],
            'portee' => 'agence',
            'agence_id' => $agence->id,
        ]);

        $document = DocumentIa::first();
        $this->assertSame($agence->id, $document->agence_id);
        $this->assertNull($document->reseau_id);
    }
}
