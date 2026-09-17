<?php

namespace Tests\Feature\Kyc;

use App\Contracts\DetecteurConnectivite;
use App\Enums\NatureRelation;
use App\Enums\SourceCreation;
use App\Enums\StatutFileAttenteNpi;
use App\Enums\StatutVerificationNpi;
use App\Enums\TypeAlerte;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Models\VerificationNpiEnAttente;
use App\Services\Kyc\VerificateurNpiSimulateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RattrapageVerificationNpiTest extends TestCase
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

    private function clientAvecNpiEnAttente(string $npi): array
    {
        $reseau = Reseau::create(['nom' => 'Réseau Test', 'code' => 'RT']);
        $agence = Agence::create(['reseau_id' => $reseau->id, 'nom' => 'Agence Test', 'code' => 'AT']);
        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::SaisieAgent,
            'statut_verification_npi' => StatutVerificationNpi::EnAttenteConnexion,
        ]);
        PersonnePhysique::create(['client_id' => $client->id, 'nom' => 'KPADONOU', 'prenoms' => 'Fidèle', 'champs_manquants' => []]);

        $ligne = VerificationNpiEnAttente::create([
            'client_id' => $client->id,
            'npi_idx' => hash('sha256', $npi),
            'npi_chiffre' => $npi,
            'statut' => StatutFileAttenteNpi::EnAttente,
        ]);

        return [$client, $ligne];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_la_commande_traite_une_ligne_en_attente_dont_le_npi_est_valide_des_le_retour_de_connexion(): void
    {
        $this->bindConnectivite(true);
        [$client, $ligne] = $this->clientAvecNpiEnAttente('1234567890');

        Artisan::call('npi:verifier-en-attente');

        $client->refresh();
        $ligne->refresh();

        $this->assertSame(StatutVerificationNpi::VerifieValide, $client->statut_verification_npi);
        $this->assertSame(StatutFileAttenteNpi::Traitee, $ligne->statut);
        $this->assertNull($ligne->npi_chiffre, 'Le NPI en clair doit être vidé une fois la ligne traitée.');
    }

    public function test_un_npi_invalide_apres_coup_cree_une_alerte_sans_jamais_supprimer_le_client(): void
    {
        $this->bindConnectivite(true);
        [$npiInvalide] = VerificateurNpiSimulateur::NPI_TEST_INVALIDES;
        [$client, $ligne] = $this->clientAvecNpiEnAttente($npiInvalide);

        Artisan::call('npi:verifier-en-attente');

        $this->assertNotNull(Client::find($client->id), 'Le client ne doit jamais être supprimé.');
        $client->refresh();
        $this->assertSame(StatutVerificationNpi::VerifieInvalide, $client->statut_verification_npi);

        $alerte = Alerte::where('client_id', $client->id)->first();
        $this->assertNotNull($alerte);
        $this->assertSame(TypeAlerte::NpiInvalideApresVerification, $alerte->type);
        $this->assertSame('critique', $alerte->gravite->value);
    }

    public function test_sans_connexion_la_commande_incremente_les_tentatives_sans_verifier(): void
    {
        $this->bindConnectivite(false);
        [$client, $ligne] = $this->clientAvecNpiEnAttente('1234567890');

        Artisan::call('npi:verifier-en-attente');

        $client->refresh();
        $ligne->refresh();

        $this->assertSame(StatutVerificationNpi::EnAttenteConnexion, $client->statut_verification_npi);
        $this->assertSame(StatutFileAttenteNpi::EnAttente, $ligne->statut);
        $this->assertSame(1, $ligne->tentatives);
        $this->assertNotNull($ligne->npi_chiffre, 'Le NPI reste nécessaire tant que la ligne n\'est pas traitée.');
    }

    public function test_apres_le_nombre_maximum_de_tentatives_sans_connexion_la_ligne_echoue_definitivement(): void
    {
        config(['kyc.npi_tentatives_max' => 2]);
        $this->bindConnectivite(false);
        [$client, $ligne] = $this->clientAvecNpiEnAttente('1234567890');

        Artisan::call('npi:verifier-en-attente');
        Artisan::call('npi:verifier-en-attente');

        $ligne->refresh();

        $this->assertSame(StatutFileAttenteNpi::EchoueeDefinitivement, $ligne->statut);
        $this->assertSame(2, $ligne->tentatives);
    }
}
