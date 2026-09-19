<?php

namespace App\Services\Conformite;

use App\Enums\CanalOperation;
use App\Enums\CanalSoupcon;
use App\Enums\NiveauRisqueSoupcon;
use App\Enums\StatutPpe;
use App\Enums\TypeClient;
use App\Enums\TypeClientSoupcon;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Operation;
use App\Models\RegleDetection;
use App\Models\SuggestionSoupcon;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Données connues du système sur un client suspecté, en deux usages (18_PROMPT §5.2, §5.3) :
 *  - `prefill()` : pré-remplit la fiche d'analyse (identité, compte, opérations…) — usage humain,
 *    plein accès à l'identité, c'est la fonction du contrôleur permanent ;
 *  - `caracteristiquesDerivees()` : uniquement des indicateurs et des mesures dérivées (âge, ratio
 *    dépôt/revenu, nombre d'agences…), SEULE matière qu'un fournisseur IA peut recevoir. Jamais
 *    un nom, un NPI, une adresse ni le texte rédigé par le contrôleur.
 */
class PreparateurDossierSoupcon
{
    /**
     * @return array<string, mixed>
     */
    public function prefill(SuggestionSoupcon $suggestion): array
    {
        $client = $this->client($suggestion->client_id);
        $operations = $this->operations($client, (int) ($this->fenetreJours()));
        $compte = $client->comptes->sortBy('created_at')->first();

        return [
            'nom' => $client->nomAffichage(),
            'numero_compte' => $compte?->numero,
            'date_ouverture' => $compte?->created_at?->format('d/m/Y') ?? $client->created_at?->format('d/m/Y'),
            'type_client' => $this->typeClient($client),
            'niveau_risque' => $this->niveauRisque((float) $suggestion->score),
            'dates_operations' => $operations->map(fn (Operation $o) => $o->effectuee_le->format('Y-m-d'))->unique()->values()->all(),
            'montants_concernes' => $operations->map(fn (Operation $o) => (float) $o->montant)->values()->all(),
            'canal' => $this->canal($operations),
            'indicateurs' => $suggestion->indicateurs_detectes ?? [],
            'operations' => $operations->map(fn (Operation $o) => [
                'date' => $o->effectuee_le->format('d/m/Y'),
                'type' => $o->type->libelle(),
                'montant' => (float) $o->montant,
                'agence' => $o->agence?->nom,
            ])->values()->all(),
        ];
    }

    /**
     * @return array{score: float, indicateurs: array<int, string>, age: ?int, ratio_depot_revenu: ?float, nombre_agences: int, nombre_operations: int, type_client: string, niveau_risque: string, montant_total: float}
     */
    public function caracteristiquesDerivees(SuggestionSoupcon $suggestion): array
    {
        $client = $this->client($suggestion->client_id);
        $operations = $this->operations($client, (int) $this->fenetreJours());
        $personne = $client->personnePhysique ?? $client->personneMorale;

        $revenus = (float) ($personne?->revenus_mensuels_estimes ?? 0);
        $depot = (float) ($personne?->depot_especes ?? 0);

        return [
            'score' => (float) $suggestion->score,
            'indicateurs' => collect($suggestion->indicateurs())->map(fn ($i) => $i->libelle())->all(),
            'age' => $this->age($client),
            'ratio_depot_revenu' => $revenus > 0 ? round($depot / $revenus, 1) : null,
            'nombre_agences' => $operations->pluck('agence_id')->unique()->count(),
            'nombre_operations' => $operations->count(),
            'type_client' => $this->typeClient($client)->libelle(),
            'niveau_risque' => $this->niveauRisque((float) $suggestion->score)->libelle(),
            'montant_total' => (float) $operations->sum('montant'),
        ];
    }

    public function niveauRisque(float $score): NiveauRisqueSoupcon
    {
        return match (true) {
            $score >= 70 => NiveauRisqueSoupcon::Eleve,
            $score >= 40 => NiveauRisqueSoupcon::Moyen,
            default => NiveauRisqueSoupcon::Faible,
        };
    }

    private function client(string $id): Client
    {
        return Client::withoutGlobalScopes()->with(['personnePhysique', 'personneMorale', 'comptes', 'identite'])->findOrFail($id);
    }

    private function typeClient(Client $client): TypeClientSoupcon
    {
        if ($client->statut_ppe === StatutPpe::PpeConfirme) {
            return TypeClientSoupcon::Ppe;
        }

        return $client->type === TypeClient::PersonneMorale ? TypeClientSoupcon::Entreprise : TypeClientSoupcon::Particulier;
    }

    /** @return Collection<int, Operation> */
    private function operations(Client $client, int $fenetreJours)
    {
        $comptes = Compte::whereIn('client_id', $client->clientIdsDeLIdentite())->pluck('id');

        return Operation::with('agence')
            ->whereIn('compte_id', $comptes)
            ->where('effectuee_le', '>=', now()->subDays($fenetreJours))
            ->orderBy('effectuee_le')
            ->get();
    }

    private function canal($operations): CanalSoupcon
    {
        $canal = $operations->first()?->canal;

        return $canal === CanalOperation::Guichet || $canal === null ? CanalSoupcon::Guichet : CanalSoupcon::Autre;
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

    private function fenetreJours(): int
    {
        return (int) (RegleDetection::where('code', 'ANALYSE_COMPORTEMENTALE_SOUPCON')->value('parametres')['fenetre_jours'] ?? 30);
    }
}
