<?php

namespace App\Services\Import;

use App\Enums\NatureRelation;
use App\Enums\SourceCreation;
use App\Enums\StatutImportLigne;
use App\Enums\StatutImportLot;
use App\Enums\TypeClient;
use App\Models\Agence;
use App\Models\Client;
use App\Models\ImportLigne;
use App\Models\ImportLot;
use App\Models\PersonnePhysique;
use App\Services\Empreinte\GenerateurEmpreinte;
use App\Services\Empreinte\ServiceEmpreinte;
use App\Services\Filtrage\MoteurFiltrage;
use App\Services\Kyc\CalculateurCompletude;
use App\Support\Bitset;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

/**
 * Import CSV « core banking » : aperçu avant validation (jamais d'import à l'aveugle),
 * puis rapprochement par empreinte + date de naissance pour éviter les doublons (§5.2).
 */
class ImportateurCsv
{
    public function __construct(private readonly GenerateurEmpreinte $generateur) {}

    private const SEUIL_RAPPROCHEMENT = 0.85;

    /** Champs cibles proposés pour le mapping (personne physique uniquement dans ce MVP). */
    public const CHAMPS_CIBLES = [
        'nom' => 'Nom',
        'prenoms' => 'Prénoms',
        'date_naissance' => 'Date de naissance (AAAA-MM-JJ)',
        'lieu_naissance' => 'Lieu de naissance',
        'piece_identite_numero' => 'Numéro de pièce d\'identité',
        'adresse' => 'Adresse',
        'profession' => 'Profession',
        'revenus_mensuels_estimes' => 'Revenus mensuels estimés',
    ];

    /**
     * @return array{entetes: array<string>, lignes: array<int, array<string, string>>}
     */
    public function apercu(string $cheminFichier, int $limite = 5): array
    {
        $reader = $this->lecteur($cheminFichier);
        $lignes = [];

        foreach ($reader->getRecords() as $i => $enregistrement) {
            if ($i >= $limite) {
                break;
            }
            $lignes[] = $enregistrement;
        }

        return ['entetes' => $reader->getHeader(), 'lignes' => $lignes];
    }

    /**
     * @param  array<string, string>  $mapping  colonne_csv => champ_cible
     */
    public function importer(
        string $cheminFichier,
        array $mapping,
        Agence $agence,
        ServiceEmpreinte $empreinte,
        CalculateurCompletude $completude,
        ?MoteurFiltrage $moteurFiltrage = null,
    ): ImportLot {
        $reader = $this->lecteur($cheminFichier);
        $enregistrements = [...$reader->getRecords()];

        $lot = ImportLot::create([
            'nom_fichier' => basename($cheminFichier),
            'agence_id' => $agence->id,
            'nombre_lignes' => count($enregistrements),
            'statut' => StatutImportLot::EnCours,
        ]);

        $nombreNouveaux = 0;
        $nombreACompleter = 0;

        foreach ($enregistrements as $enregistrement) {
            $donnees = $this->appliquerMapping($enregistrement, $mapping);

            if (blank($donnees['nom'] ?? null) && blank($donnees['prenoms'] ?? null)) {
                ImportLigne::create([
                    'import_lot_id' => $lot->id,
                    'client_id' => null,
                    'donnees_brutes' => $enregistrement,
                    'champs_manquants' => ['nom', 'prenoms'],
                    'statut' => StatutImportLigne::ACompleter,
                ]);
                $nombreACompleter++;

                continue;
            }

            [$client, $estNouveau] = $this->rapprocherOuCreer($donnees, $agence, $empreinte);

            if ($estNouveau) {
                $moteurFiltrage?->filtrer($client->fresh());
            }

            $resultat = $completude->evaluer($client);

            ImportLigne::create([
                'import_lot_id' => $lot->id,
                'client_id' => $client->id,
                'donnees_brutes' => $enregistrement,
                'champs_manquants' => $resultat['champs_manquants'],
                'statut' => $estNouveau
                    ? StatutImportLigne::Nouveau
                    : ($resultat['champs_manquants'] === [] ? StatutImportLigne::Complet : StatutImportLigne::ACompleter),
            ]);

            if ($estNouveau) {
                $nombreNouveaux++;
            }
            if ($resultat['champs_manquants'] !== []) {
                $nombreACompleter++;
            }
        }

        $lot->update([
            'nombre_nouveaux' => $nombreNouveaux,
            'nombre_a_completer' => $nombreACompleter,
            'statut' => StatutImportLot::Termine,
        ]);

        return $lot;
    }

    private function lecteur(string $cheminFichier): Reader
    {
        $reader = Reader::createFromPath(Storage::disk('local')->path($cheminFichier));
        $reader->setHeaderOffset(0);

        return $reader;
    }

    /**
     * @param  array<string, string>  $enregistrement
     * @param  array<string, string>  $mapping
     * @return array<string, ?string>
     */
    private function appliquerMapping(array $enregistrement, array $mapping): array
    {
        $donnees = [];

        foreach ($mapping as $colonneCsv => $champCible) {
            if ($champCible === '' || ! array_key_exists($colonneCsv, $enregistrement)) {
                continue;
            }
            $valeur = trim((string) $enregistrement[$colonneCsv]);
            $donnees[$champCible] = $valeur === '' ? null : $valeur;
        }

        return $donnees;
    }

    /**
     * @param  array<string, ?string>  $donnees
     * @return array{0: Client, 1: bool} client et vrai si nouvellement créé
     */
    private function rapprocherOuCreer(array $donnees, Agence $agence, ServiceEmpreinte $empreinte): array
    {
        $nomComplet = trim(($donnees['prenoms'] ?? '').' '.($donnees['nom'] ?? ''));
        $candidat = $this->generateur->encoderNom($nomComplet);
        $dateNormalisee = $this->generateur->normaliserDate($donnees['date_naissance'] ?? null);
        $vecteurCible = $dateNormalisee !== null
            ? Bitset::concatener($candidat, $this->generateur->encoderDate($dateNormalisee))
            : Bitset::concatener($candidat, Bitset::vide(500));

        $meilleur = null;
        $meilleurScore = 0.0;

        foreach (PersonnePhysique::with('client')->whereHas('client', fn ($q) => $q->where('reseau_id', $agence->reseau_id))->get() as $existante) {
            $score = $empreinte->similariteCombinee($vecteurCible->versOctets(), $existante->empreinte_combinee);
            if ($score !== null && $score > $meilleurScore) {
                $meilleurScore = $score;
                $meilleur = $existante;
            }
        }

        if ($meilleur !== null && $meilleurScore >= self::SEUIL_RAPPROCHEMENT) {
            $client = $meilleur->client;
            $meilleur->fill(array_intersect_key($donnees, PersonnePhysique::CHAMPS_COMPLETABLES));
            $meilleur->save();

            return [$client, false];
        }

        $client = Client::create([
            'reseau_id' => $agence->reseau_id,
            'type' => TypeClient::PersonnePhysique,
            'nature_relation' => NatureRelation::TitulaireCompte,
            'source_creation' => SourceCreation::ImportCsv,
        ]);

        PersonnePhysique::create(array_merge(
            ['client_id' => $client->id, 'champs_manquants' => []],
            array_intersect_key($donnees, PersonnePhysique::CHAMPS_COMPLETABLES),
        ));

        return [$client, true];
    }
}
