<?php

namespace App\Console\Commands\Demo;

use App\Enums\ModePaiement;
use App\Enums\NatureRelation;
use App\Enums\RoleSignataire;
use App\Enums\SourceCreation;
use App\Enums\StatutCompte;
use App\Enums\TypeClient;
use App\Enums\TypeOperation;
use App\Models\Agence;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\Compte;
use App\Models\DeclarationCentif;
use App\Models\Operation;
use App\Models\PersonneMorale;
use App\Models\PersonnePhysique;
use App\Models\Reseau;
use App\Models\Signataire;
use App\Services\Detection\DetecteurFractionnement;
use App\Services\Filtrage\MoteurFiltrage;
use App\Services\Identite\ResolveurIdentite;
use App\Services\Kyc\CalculateurCompletude;
use App\Services\Securite\IndexAveugle;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Rejoue l'un des 6 scénarios de démonstration (05_PROMPT_MVP_RECENTRE.md §2/§10).
 * Idempotent : peut être relancé plusieurs fois pendant une répétition de pitch.
 */
class RejouerScenario extends Command
{
    protected $signature = 'demo:scenario {code : 1 a 7}';

    protected $description = 'Rejoue un des 7 scénarios de démonstration du MVP CIF-Empreinte.';

    public function handle(): int
    {
        if (! config('cif_demo.actif')) {
            $this->error('CIF_DEMO=false : scénarios de démonstration désactivés.');

            return self::FAILURE;
        }

        $code = $this->argument('code');
        $methode = 'scenario'.$code;

        if (! method_exists($this, $methode)) {
            $this->error('Code de scénario inconnu. Utilisez une valeur entre 1 et 7.');

            return self::FAILURE;
        }

        $this->{$methode}();

        return self::SUCCESS;
    }

    /** Problème 1 : le core banking n'a pas tous les champs KYC. */
    private function scenario1(): void
    {
        $this->info('Scénario 1 — Complétude KYC après import core banking');

        $clients = Client::with('personnePhysique')
            ->where('source_creation', SourceCreation::ImportCsv)
            ->get();

        if ($clients->isEmpty()) {
            $this->warn('Aucun client importé : lancez d\'abord `php artisan migrate:fresh --seed`.');

            return;
        }

        foreach ($clients as $client) {
            $manquants = $client->personnePhysique?->champs_manquants ?? [];
            $bloquant = app(CalculateurCompletude::class)->champsBloquantsManquants($client);
            $this->line(sprintf(
                '  %s — %d%% — manquants: %s%s',
                $client->nomAffichage(),
                $client->score_completude_kyc,
                $manquants === [] ? 'aucun' : implode(', ', $manquants),
                $bloquant !== [] ? '  [BLOQUÉ pour opération : '.implode(', ', $bloquant).']' : '',
            ));
        }
    }

    /** Problème 2 : une alerte de fractionnement — ici la correspondance liste — s'explique en clair. */
    private function scenario2(): void
    {
        $this->info('Scénario 2 — Filtrage sanctions/PPE avec explication en langage humain');

        $client = Client::whereHas('personnePhysique', fn ($q) => $q->where('nom_idx', app(IndexAveugle::class)->calculer('AHOUANDJINOU', 'nom')))->first();

        if ($client === null) {
            $this->warn('Client de démonstration introuvable : lancez `php artisan migrate:fresh --seed`.');

            return;
        }

        $resultats = app(MoteurFiltrage::class)->filtrer($client->fresh());
        $alerte = Alerte::where('client_id', $client->id)->latest()->first();

        $this->line('  Correspondances trouvées : '.$resultats->count());
        if ($alerte !== null) {
            $this->line('  Explication affichée au conformité : '.$alerte->explication_texte);
        }
    }

    /** Problème 3 : fractionnement multi-agences (scénario "KPADONOU / KPADENOU"). */
    private function scenario3(): void
    {
        $this->info('Scénario 3 — Fractionnement sur deux agences différentes');

        $reseau = Reseau::firstOrCreate(['code' => 'ALPHA'], ['nom' => 'Réseau Alpha']);
        $dassa = Agence::firstOrCreate(['reseau_id' => $reseau->id, 'code' => 'DASSA'], ['nom' => 'Agence de Dassa']);
        $savalou = Agence::firstOrCreate(['reseau_id' => $reseau->id, 'code' => 'SAVALOU'], ['nom' => 'Agence de Savalou']);

        // Noms distincts de ceux des autres seeders de démo pour éviter toute collision
        // de rapprochement par empreinte avec un client déjà seedé.
        // Deux fiches, deux orthographes, deux agences — mais le même NPI vérifié.
        $compteDassa = $this->clientEtCompte($dassa, 'GBAGUIDI', 'Sêmévo', '1985-09-03', 'CPT-DEMO-DASSA', '1985090312345');
        $compteSavalou = $this->clientEtCompte($savalou, 'GBAGUIDI', 'Semevo', '1985-09-03', 'CPT-DEMO-SAVALOU', '1985090312345');

        $maintenant = now();
        $detecteur = app(DetecteurFractionnement::class);

        // Chaque dépôt reste sous le seuil unitaire (5 M) ; cumulés, ils dépassent 15 M.
        $depots = [
            [$compteDassa, 4800000, $maintenant->copy()->subDays(2)],
            [$compteSavalou, 4900000, $maintenant->copy()->subDays(1)],
            [$compteDassa, 4700000, $maintenant->copy()->subHours(6)],
            [$compteSavalou, 4800000, $maintenant],
        ];

        foreach ($depots as [$compte, $montant, $date]) {
            $operation = $this->deposer($compte, $montant, $date);
            $detecteur->analyserApresOperation($operation->fresh(['compte']));
        }

        $alerte = Alerte::where('type', 'fractionnement_multi_agences')->latest()->first();
        $this->line($alerte !== null
            ? '  Alerte générée : '.$alerte->explication_texte
            : '  Aucune alerte (seuils déjà consommés — relancez `migrate:fresh --seed` pour un état propre).');
    }

    /** Problème 4 : un signataire de personne morale correspond à une PPE fictive. */
    private function scenario4(): void
    {
        $this->info('Scénario 4 — Signataire d\'une personne morale contrôlé individuellement');

        $reseau = Reseau::firstOrCreate(['code' => 'ALPHA'], ['nom' => 'Réseau Alpha']);
        $agence = Agence::firstOrCreate(['reseau_id' => $reseau->id, 'code' => 'DASSA'], ['nom' => 'Agence de Dassa']);

        $client = Client::firstOrCreate(
            ['reseau_id' => $reseau->id, 'source_creation' => SourceCreation::SaisieAgent, 'type' => TypeClient::PersonneMorale],
            ['nature_relation' => NatureRelation::TitulaireCompte],
        );
        $morale = PersonneMorale::firstOrCreate(['client_id' => $client->id], ['raison_sociale' => 'SARL DEMO SCENARIO 4', 'champs_manquants' => []]);
        $signataire = Signataire::firstOrCreate(
            ['personne_morale_id' => $morale->id, 'nom' => 'ALIDOU Bertin'],
            ['role' => RoleSignataire::Signataire, 'statut_filtrage' => 'a_verifier'],
        );

        app(MoteurFiltrage::class)->filtrer($signataire->fresh());
        $signataire->refresh();

        $this->line('  Statut du signataire après filtrage : '.$signataire->statut_filtrage->libelle());
        $this->line('  Validation de la fiche personne morale possible : '.($signataire->statut_filtrage->value === 'a_verifier' ? 'non (en attente de revue)' : 'oui'));
    }

    /** Problème 5 : le tableau de bord conformité en un coup d'œil. */
    private function scenario5(): void
    {
        $this->info('Scénario 5 — Tableau de bord actionnable');

        $this->line('  Dossiers à compléter : '.Client::where('score_completude_kyc', '<', 100)->count());
        $this->line('  Alertes ouvertes : '.Alerte::where('statut', '!=', 'traitee')->count());
        $this->line('  Déclarations CENTIF à préparer : '.DeclarationCentif::where('statut', 'a_preparer')->count());
    }

    /** Problème 6 : le guichet ne voit jamais un soupçon. */
    private function scenario6(): void
    {
        $this->info('Scénario 6 — Non-divulgation au guichet (Loi art. 63)');
        $this->line('  Vue guichet sur un dossier en vérification : « Vérification complémentaire requise — dossier transmis au responsable conformité. »');
        $this->line('  Vue conformité sur le même dossier : score, correspondances, explication détaillée.');
        $this->line('  Toute tentative d\'accès direct du guichet aux écrans filtrage/conformité est journalisée (action: tentative_acces_refusee).');
    }

    private function clientEtCompte(Agence $agence, string $nom, string $prenoms, string $dateNaissance, string $numero, ?string $npi = null, float $revenus = 20000000): Compte
    {
        $nomIdx = app(IndexAveugle::class)->calculer($nom, 'nom');
        // Comparaison exacte sur nom ET prénoms (le nom seul ne suffit pas : deux
        // personnes distinctes peuvent partager un nom de famille — cf. scénario G).
        $personne = PersonnePhysique::with('client')
            ->whereHas('client', fn ($q) => $q->where('reseau_id', $agence->reseau_id))
            ->where('nom_idx', $nomIdx)
            ->get()
            ->first(fn (PersonnePhysique $p) => $p->prenoms === $prenoms);

        if ($personne === null) {
            $client = Client::create([
                'reseau_id' => $agence->reseau_id,
                'type' => TypeClient::PersonnePhysique,
                'nature_relation' => NatureRelation::TitulaireCompte,
                'source_creation' => SourceCreation::SaisieAgent,
            ]);
            $personne = PersonnePhysique::create([
                'client_id' => $client->id,
                'nom' => $nom,
                'prenoms' => $prenoms,
                'date_naissance' => $dateNaissance,
                'revenus_mensuels_estimes' => $revenus,
                'champs_manquants' => [],
            ]);

            if ($npi !== null) {
                $personne->definirNpi($npi);
                $personne->saveQuietly();
            }

            // Rattachement à la personne physique réelle : NPI d'abord, empreinte ensuite.
            app(ResolveurIdentite::class)->rattacher($client->fresh('personnePhysique'));
        }

        $client = $personne->client;

        $idx = app(IndexAveugle::class)->calculer($numero, 'numero_compte');
        $compte = Compte::where('numero_idx', $idx)->first();

        return $compte ?? Compte::create([
            'client_id' => $client->id,
            'agence_id' => $agence->id,
            'numero' => $numero,
            'statut' => StatutCompte::Actif,
        ]);
    }

    private function deposer(Compte $compte, float $montant, Carbon $date): Operation
    {
        return Operation::create([
            'compte_id' => $compte->id,
            'agence_id' => $compte->agence_id,
            'type' => TypeOperation::Depot,
            'montant' => $montant,
            'mode_paiement' => ModePaiement::Especes,
            'devise_code' => 'XOF',
            'effectuee_le' => $date,
            'canal' => 'guichet',
        ]);
    }

    /**
     * Plafond quotidien : le cumul d'espèces d'une même personne, sur tous ses comptes
     * et toutes les agences, dépasse le plafond déduit de son activité déclarée.
     * Fondement : Loi uniforme art. 17 i) — les opérations en espèces multiples d'une
     * même personne dans la journée sont considérées comme une opération unique.
     */
    private function scenario7(): void
    {
        $this->info('Scénario 7 — Plafond quotidien dépassé sur plusieurs comptes');

        $reseau = Reseau::firstOrCreate(['code' => 'ALPHA'], ['nom' => 'Réseau Alpha']);
        $dassa = Agence::firstOrCreate(['reseau_id' => $reseau->id, 'code' => 'DASSA'], ['nom' => 'Agence de Dassa']);
        $savalou = Agence::firstOrCreate(['reseau_id' => $reseau->id, 'code' => 'SAVALOU'], ['nom' => 'Agence de Savalou']);

        // Commerçante de marché : 800 000 FCFA déclarés au KYC, donc un plafond
        // quotidien de 1 200 000 FCFA (coefficient 1,5 de config/identite.php).
        $npi = '1979041556789';
        $compteDassa = $this->clientEtCompte($dassa, 'HOUNKPATIN', 'Alimatou', '1979-04-15', 'CPT-DEMO-PLAFOND-1', $npi, 800000);
        $compteSavalou = $this->clientEtCompte($savalou, 'HOUNKPATIN', 'Alimatou A.', '1979-04-15', 'CPT-DEMO-PLAFOND-2', $npi, 800000);

        $identite = $compteDassa->client->fresh('identite')->identite;
        $this->line('  Plafond quotidien calculé : '
            .number_format((float) $identite?->plafond_quotidien_especes, 0, ',', ' ').' XOF'
            .' ('.$identite?->base_calcul_plafond.')');

        $maintenant = now();
        $detecteur = app(DetecteurFractionnement::class);

        // Deux dépôts anodins pris séparément, sur deux comptes et deux agences.
        foreach ([[$compteDassa, 500000, 3], [$compteSavalou, 800000, 0]] as [$compte, $montant, $heures]) {
            $operation = $this->deposer($compte, $montant, $maintenant->copy()->subHours($heures));
            $detecteur->analyserApresOperation($operation->fresh(['compte']));

            $this->line('  Dépôt de '.number_format($montant, 0, ',', ' ').' XOF à '.$compte->agence->nom);
        }

        $alerte = Alerte::whereIn('type', ['plafond_quotidien_depasse', 'plafond_quotidien_approche'])
            ->latest()
            ->first();

        $this->line($alerte !== null
            ? '  Alerte '.$alerte->gravite->value.' : '.$alerte->explication_texte
            : '  Aucune alerte (alerte déjà levée aujourd\'hui — relancez `migrate:fresh --seed` pour un état propre).');
    }
}
