<?php

namespace App\Services\Conformite;

use App\Enums\IndicateurSoupcon;
use App\Enums\StatutSuggestionSoupcon;
use App\Enums\TypeAlerte;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Operation;
use App\Models\RegleDetection;
use App\Models\SuggestionSoupcon;
use App\Services\Audit\Consignateur;
use App\Services\Kyc\DetecteurIncoherencesSaisie;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * Surveillance continue de la cohérence profil / opérations (18_PROMPT §4). Produit une
 * SUGGESTION pour le contrôleur permanent, jamais une accusation, jamais une alerte visible
 * d'un caissier ni du responsable.
 *
 * Indicateurs = cases de la fiche d'analyse officielle, rien d'autre. Détectés seuls :
 * incohérence profil/opérations, dépôts fractionnés ou inhabituels (on relit les alertes de
 * DetecteurFractionnement au lieu de le réimplémenter), refus/absence de documents, absence de
 * source de revenus. Non détectables seuls : « pays à risque » (aucune donnée de destination sur
 * les opérations, canal Guichet/Import uniquement), « comportement évasif » et « autres » —
 * à cocher par le contrôleur.
 *
 * Toutes les valeurs de seuil viennent de la règle `ANALYSE_COMPORTEMENTALE_SOUPCON` (visible et
 * modifiable dans Admin > Règles de détection, avec sa source). Chaque exécution est journalisée.
 */
class AnalyseurComportementalContinu
{
    public const CODE_REGLE = 'ANALYSE_COMPORTEMENTALE_SOUPCON';

    public function __construct(private readonly DetecteurIncoherencesSaisie $incoherencesSaisie) {}

    /**
     * Ne lève jamais : l'analyse ne doit jamais empêcher une opération ni une saisie.
     */
    public function analyserSansEchec(Client $client, string $declencheur): ?SuggestionSoupcon
    {
        try {
            return $this->analyser($client, $declencheur);
        } catch (Throwable $e) {
            report($e);
            Consignateur::enregistrer('systeme', null, 'analyse_comportementale_echec', 'client', $client->id);

            return null;
        }
    }

    public function analyser(Client $client, string $declencheur): ?SuggestionSoupcon
    {
        $regle = RegleDetection::where('code', self::CODE_REGLE)->where('actif', true)->first();

        if ($regle === null) {
            Consignateur::enregistrer('systeme', null, 'analyse_comportementale_ignoree', 'client', $client->id);

            return null;
        }

        $p = $regle->parametres;
        $client = Client::withoutGlobalScopes()->with(['personnePhysique', 'personneMorale', 'identite'])->findOrFail($client->id);
        $detectes = $this->detecter($client, $p);
        $score = min(100.0, array_sum(array_map(fn (string $cle) => (float) ($p[$this->cleDePoids($cle)] ?? 0), $detectes)));

        // Chaque exécution laisse une trace (déclencheur dans l'action, jamais d'identité).
        Consignateur::enregistrer('systeme', null, 'analyse_comportementale_continue', 'client', $client->id);

        if ($detectes === [] || $score < (float) $p['seuil_creation']) {
            return null;
        }

        return $this->enregistrerSuggestion($client, $detectes, $score);
    }

    /**
     * @param  array<string, mixed>  $p
     * @return array<int, string> clés d'IndicateurSoupcon
     */
    private function detecter(Client $client, array $p): array
    {
        $ids = $client->clientIdsDeLIdentite();
        $debut = now()->subDays((int) $p['fenetre_jours']);
        $personne = $client->personnePhysique ?? $client->personneMorale;

        $comptes = Compte::whereIn('client_id', $ids)->pluck('id');
        $operations = Operation::whereIn('compte_id', $comptes)->where('effectuee_le', '>=', $debut)->get();
        $revenus = (float) ($personne?->revenus_mensuels_estimes ?? 0);
        $depotDeclare = (float) ($personne?->depot_especes ?? 0);

        $detectes = [];

        // 1. Incohérence entre le profil et les opérations.
        $agences = $operations->pluck('agence_id')->unique()->count();
        $sedentaire = $this->estSedentaire((string) ($client->personnePhysique?->profession ?? ''), (string) $p['professions_sedentaires']);
        $ratio = $revenus > 0 ? $depotDeclare / $revenus : null;
        $incoherencesSaisie = $this->incoherencesSaisie->verifier(
            $this->age($client),
            $client->personnePhysique?->profession,
            $ratio !== null && $ratio >= (float) $p['ratio_depot_revenu_min'] ? $ratio : null,
            false,
        )['avertissements'];

        if (($sedentaire && $agences >= (int) $p['min_agences_dispersion']) || $incoherencesSaisie !== []) {
            $detectes[] = IndicateurSoupcon::IncoherenceProfilOperations->value;
        }

        // 2. Dépôts fractionnés ou inhabituels : on relit ce que DetecteurFractionnement a déjà levé.
        $alerteFractionnement = Alerte::whereIn('client_id', $ids)
            ->whereIn('type', [
                TypeAlerte::FractionnementGuichet->value, TypeAlerte::FractionnementMultiAgences->value,
                TypeAlerte::PlafondQuotidienApproche->value, TypeAlerte::PlafondQuotidienDepasse->value,
                TypeAlerte::SeuilMensuelCentif->value,
            ])->where('created_at', '>=', $debut)->exists();

        if ($alerteFractionnement) {
            $detectes[] = IndicateurSoupcon::DepotsFractionnesInhabituels->value;
        }

        // 3. Refus / absence de documents requis (dossier ancien, complétude très basse).
        if ($client->score_completude_kyc !== null
            && (float) $client->score_completude_kyc < (float) $p['completude_tres_basse']
            && $client->created_at->lte(now()->subDays((int) $p['anciennete_dossier_jours']))) {
            $detectes[] = IndicateurSoupcon::RefusDocumentsRequis->value;
        }

        // 4. Absence de source de revenus malgré des dépôts importants.
        $depots = (float) $operations->where('type.value', 'depot')->sum('montant') + $depotDeclare;
        if ($revenus <= 0 && $depots >= (float) $p['depot_sans_revenu_min']) {
            $detectes[] = IndicateurSoupcon::RefusSourcesRevenus->value;
        }

        return $detectes;
    }

    /**
     * @param  array<int, string>  $detectes
     */
    private function enregistrerSuggestion(Client $client, array $detectes, float $score): ?SuggestionSoupcon
    {
        $suggestions = SuggestionSoupcon::where('client_id', $client->id)->orderByDesc('genere_le')->get();
        $ouverte = $suggestions->first(fn (SuggestionSoupcon $s) => $s->statut->estOuverte());

        if ($ouverte !== null) {
            // Rafraîchit une suggestion encore modifiable ; ne touche jamais à une suggestion déjà transmise.
            if (in_array($ouverte->statut, [StatutSuggestionSoupcon::Nouvelle, StatutSuggestionSoupcon::EnAnalyse], true)) {
                $ouverte->update(['score' => $score, 'indicateurs_detectes' => array_values(array_unique(array_merge($ouverte->indicateurs_detectes ?? [], $detectes)))]);
            }

            return $ouverte;
        }

        // Déjà traitée ou écartée : on ne re-suggère que s'il apparaît un indicateur nouveau.
        $dejaVus = $suggestions->flatMap(fn (SuggestionSoupcon $s) => $s->indicateurs_detectes ?? [])->unique()->all();
        if ($suggestions->isNotEmpty() && array_diff($detectes, $dejaVus) === []) {
            return null;
        }

        $suggestion = SuggestionSoupcon::create([
            'client_id' => $client->id,
            'reseau_id' => $client->reseau_id,
            'score' => $score,
            'indicateurs_detectes' => $detectes,
            'genere_le' => now(),
            'statut' => StatutSuggestionSoupcon::Nouvelle,
        ]);

        Consignateur::enregistrer('systeme', null, 'suggestion_soupcon_creee', 'suggestion_soupcon', $suggestion->id);

        return $suggestion;
    }

    private function cleDePoids(string $indicateur): string
    {
        return match ($indicateur) {
            IndicateurSoupcon::IncoherenceProfilOperations->value => 'poids_incoherence_profil',
            IndicateurSoupcon::DepotsFractionnesInhabituels->value => 'poids_depots_fractionnes',
            IndicateurSoupcon::RefusDocumentsRequis->value => 'poids_refus_documents',
            IndicateurSoupcon::RefusSourcesRevenus->value => 'poids_refus_revenus',
            default => 'poids_inconnu',
        };
    }

    private function estSedentaire(string $profession, string $motsCles): bool
    {
        $profession = Str::of($profession)->ascii()->lower()->toString();

        foreach (explode(',', $motsCles) as $mot) {
            $mot = Str::of($mot)->ascii()->lower()->trim()->toString();

            if ($mot !== '' && str_contains($profession, $mot)) {
                return true;
            }
        }

        return false;
    }

    private function age(Client $client): ?int
    {
        try {
            $date = $client->personnePhysique?->date_naissance;

            return $date ? Carbon::parse($date)->age : null;
        } catch (Throwable) {
            return null;
        }
    }
}
