<?php

namespace Database\Seeders\Demo;

use App\Enums\AvisTechniqueSoupcon;
use App\Enums\CanalOperation;
use App\Enums\CanalSoupcon;
use App\Enums\IndicateurSoupcon;
use App\Enums\ModePaiement;
use App\Enums\NiveauRisqueSoupcon;
use App\Enums\SourceCreation;
use App\Enums\StatutDossierSoupcon;
use App\Enums\StatutSuggestionSoupcon;
use App\Enums\TypeClientSoupcon;
use App\Enums\TypeOperation;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Compte;
use App\Models\DossierAnalyseSoupcon;
use App\Models\Operation;
use App\Models\Reseau;
use App\Models\SuggestionSoupcon;
use App\Services\Conformite\AnalyseurComportementalContinu;
use App\Services\Kyc\CreateurClient;
use App\Services\Securite\IndexAveugle;
use Illuminate\Database\Seeder;

/**
 * 18_PROMPT §9 : sans ces données, l'espace contrôleur est vide au premier lancement.
 *  - HOUNSOU Léon : enseignant (profession sédentaire) dont les dépôts sont dispersés dans trois
 *    agences, dossier ancien et très incomplet → l'analyseur crée une suggestion visible dès la
 *    première connexion du contrôleur (CTRL-0001).
 *  - ZOSSOU Clémence : dossier déjà transmis par le contrôleur, en attente de décision côté
 *    responsable d'agence (RES-0001, agence de Dassa).
 * Données 100 % synthétiques.
 */
class SoupconDemoSeeder extends Seeder
{
    public function run(CreateurClient $createur, AnalyseurComportementalContinu $analyseur, IndexAveugle $indexAveugle): void
    {
        if (! config('cif_demo.actif')) {
            return;
        }

        $reseau = Reseau::where('code', 'ALPHA')->firstOrFail();
        $agences = Agence::where('reseau_id', $reseau->id)->orderBy('id')->get()->keyBy('code');
        $dassa = $agences['DASSA'];
        $controleur = Agent::where('matricule_idx', $indexAveugle->calculer('CTRL-0001', 'matricule'))->first();

        // 1. Client dont le profil déclenche une suggestion.
        $suspect = $this->creerClient($createur, $reseau, $dassa, [
            'nom' => 'HOUNSOU', 'prenoms' => 'Léon', 'date_naissance' => '1979-05-02', 'lieu_naissance' => 'Dassa-Zoumè',
            'profession' => 'Enseignant', 'revenus_mensuels_estimes' => 45000, 'depot_especes' => 6000000,
        ]);
        $compte = $this->compte($suspect, $dassa, 'DASSA-000042');
        foreach ([[$dassa, 9, 4800000], [$agences['SAVALOU'], 7, 4700000], [$agences['BOHICON'] ?? $dassa, 5, 4900000], [$dassa, 3, 4600000]] as [$agence, $jours, $montant]) {
            $this->operation($compte, $agence, $jours, $montant);
        }
        // Dossier ouvert depuis longtemps mais toujours incomplet : rend l'indicateur « documents » observable.
        $suspect->forceFill(['created_at' => now()->subDays(40), 'score_completude_kyc' => 18])->saveQuietly();
        $analyseur->analyser($suspect, 'demonstration');

        // 2. Dossier déjà transmis, en attente du responsable d'agence.
        $transmis = $this->creerClient($createur, $reseau, $dassa, [
            'nom' => 'ZOSSOU', 'prenoms' => 'Clémence', 'date_naissance' => '1988-11-23', 'lieu_naissance' => 'Savalou',
            'profession' => 'Ménagère', 'revenus_mensuels_estimes' => 30000, 'depot_especes' => 3000000,
        ]);
        $compteTransmis = $this->compte($transmis, $dassa, 'DASSA-000057');
        foreach ([[$dassa, 12, 1900000], [$agences['SAVALOU'], 10, 1850000], [$agences['BOHICON'] ?? $dassa, 8, 1950000]] as [$agence, $jours, $montant]) {
            $this->operation($compteTransmis, $agence, $jours, $montant);
        }

        $suggestion = SuggestionSoupcon::create([
            'client_id' => $transmis->id,
            'reseau_id' => $reseau->id,
            'score' => 60,
            'indicateurs_detectes' => [IndicateurSoupcon::IncoherenceProfilOperations->value, IndicateurSoupcon::DepotsFractionnesInhabituels->value],
            'genere_le' => now()->subDays(6),
            'statut' => StatutSuggestionSoupcon::Transmise,
            'controleur_id' => $controleur?->id,
        ]);

        if ($controleur !== null) {
            DossierAnalyseSoupcon::create([
                'suggestion_soupcon_id' => $suggestion->id,
                'client_id' => $transmis->id,
                'controleur_id' => $controleur->id,
                'type_client' => TypeClientSoupcon::Particulier,
                'niveau_risque' => NiveauRisqueSoupcon::Eleve,
                'dates_operations' => [now()->subDays(12)->format('Y-m-d'), now()->subDays(10)->format('Y-m-d'), now()->subDays(8)->format('Y-m-d')],
                'montants_concernes' => [1900000, 1850000, 1950000],
                'canal' => CanalSoupcon::Guichet,
                'resume_faits' => 'Trois dépôts en espèces de montants proches, en huit jours, dans trois agences différentes, pour une cliente sans activité déclarée.',
                'indicateurs' => [IndicateurSoupcon::IncoherenceProfilOperations->value, IndicateurSoupcon::DepotsFractionnesInhabituels->value],
                'analyse_controleur' => "Les dépôts successifs restent chacun sous le seuil unitaire mais totalisent 5 700 000 XOF, sans rapport avec les revenus déclarés (30 000 XOF/mois). La cliente n'a pas pu justifier l'origine des fonds lors du dernier contact.",
                'avis_technique_controleur' => AvisTechniqueSoupcon::InvestigationAPoursuivre,
                'transmis_le' => now()->subDays(2),
                'statut' => StatutDossierSoupcon::Transmise,
            ]);
        }

        $this->command?->info('SoupconDemoSeeder : 1 suggestion nouvelle et 1 dossier transmis créés.');
    }

    private function creerClient(CreateurClient $createur, Reseau $reseau, Agence $agence, array $champs): Client
    {
        $client = $createur->creer(
            ['type' => 'personne_physique', 'nature_relation' => 'titulaire_compte'] + $champs,
            $reseau->id,
            SourceCreation::SaisieAgent,
            null,
        );
        $client->update(['agence_creation_id' => $agence->id]);

        return $client;
    }

    private function compte(Client $client, Agence $agence, string $numero): Compte
    {
        return Compte::create(['client_id' => $client->id, 'agence_id' => $agence->id, 'numero' => $numero, 'statut' => 'actif']);
    }

    private function operation(Compte $compte, Agence $agence, int $joursAvant, int $montant): void
    {
        Operation::create([
            'compte_id' => $compte->id, 'agence_id' => $agence->id, 'agent_id' => null,
            'type' => TypeOperation::Depot, 'montant' => $montant, 'mode_paiement' => ModePaiement::Especes,
            'devise_code' => 'XOF', 'effectuee_le' => now()->subDays($joursAvant), 'canal' => CanalOperation::Guichet,
        ]);
    }
}
