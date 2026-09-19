<?php

namespace Tests\Feature\Filtrage;

use App\Enums\RoleAgent;
use App\Enums\SourceListeType;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\EntreeListe;
use App\Models\Reseau;
use App\Services\Empreinte\ServiceEmpreinte;
use App\Services\Filtrage\MoteurFiltrage;
use App\Services\Import\ImportateurCsv;
use App\Services\Kyc\CalculateurCompletude;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 13_PROMPT_IA_VISIBLE_DANS_INTERFACE §2.3 : bug trouvé en parcourant l'écran de
 * filtrage responsable dans un vrai navigateur (pas par un test) — un client importé
 * par CSV n'avait pas `agence_creation_id`, donc `Client::scopeDeLAgence()` ne le
 * trouvait jamais, même avec une correspondance de filtrage déjà détectée à l'import.
 * La file de filtrage restait vide en permanence pour ce cas, invisible au responsable.
 */
class ImportCsvVisibleDansFiltrageTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_client_importe_avec_correspondance_apparait_dans_la_file_du_responsable(): void
    {
        Storage::fake('local');

        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT3']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT3']);
        $responsable = Agent::create([
            'agence_id' => $agence->id, 'nom' => 'Responsable', 'matricule' => 'RESP-IMPORT-1',
            'mot_de_passe' => Hash::make('mot-de-passe-solide'), 'role' => RoleAgent::ResponsableAgence,
        ]);

        EntreeListe::create(['source' => SourceListeType::Demo, 'nom' => 'KOUDOU Martine', 'version_liste' => 'v1']);

        $csv = "nom,prenoms\nKOUDOU,Martine\n";
        $fichier = UploadedFile::fake()->createWithContent('export.csv', $csv);
        $chemin = $fichier->store('imports', 'local');

        app(ImportateurCsv::class)->importer(
            $chemin,
            ['nom' => 'nom', 'prenoms' => 'prenoms'],
            $agence,
            app(ServiceEmpreinte::class),
            app(CalculateurCompletude::class),
            app(MoteurFiltrage::class),
        );

        $this->actingAs($responsable, 'agent')
            ->get(route('responsable.filtrage.index'))
            ->assertOk()
            ->assertSee('KOUDOU');
    }
}
