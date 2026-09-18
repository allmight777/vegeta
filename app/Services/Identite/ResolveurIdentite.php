<?php

namespace App\Services\Identite;

use App\Enums\MethodeRattachement;
use App\Enums\StatutFusionIdentite;
use App\Enums\TypeClient;
use App\Models\Agent;
use App\Models\Client;
use App\Models\FusionIdentiteEnAttente;
use App\Models\Identite;
use App\Models\RattachementIdentite;
use App\Services\Audit\Consignateur;
use App\Services\Empreinte\ServiceEmpreinte;
use Illuminate\Support\Facades\DB;

/**
 * Rattache une fiche client à la personne physique réelle (§ identité).
 *
 * Ordre volontaire : NPI d'abord, empreinte seulement en secours.
 * Le NPI est délivré et vérifié par l'ANIP, il est unique et déjà contrôlé : c'est la
 * preuve la plus forte. L'empreinte ne sert que lorsque le NPI est absent (mode dégradé,
 * client ancien, saisie incomplète). Entre les deux seuils, on ne fusionne jamais tout
 * seul : deux homonymes proches (AGBO Paul / AGBO Pauline) partagent un score élevé sur
 * le nom seul. Le responsable LBC/FT tranche.
 */
class ResolveurIdentite
{
    public function __construct(
        private readonly ServiceEmpreinte $empreinte,
        private readonly CalculateurPlafondQuotidien $plafond,
    ) {}

    public function rattacher(Client $client, ?Agent $agent = null): ?Identite
    {
        if ($client->type !== TypeClient::PersonnePhysique) {
            return null;
        }

        $personne = $client->personnePhysique;
        if ($personne === null) {
            return null;
        }

        return DB::transaction(function () use ($client, $personne, $agent) {
            if ($personne->npi_idx !== null) {
                $existante = Identite::where('reseau_id', $client->reseau_id)
                    ->where('npi_idx', $personne->npi_idx)
                    ->first();

                if ($existante !== null) {
                    return $this->lier($client, $existante, MethodeRattachement::Npi, null, $agent);
                }

                return $this->creer($client, $personne->npi_idx, $agent);
            }

            [$candidate, $score] = $this->meilleureCandidate($client, $personne->empreinte_combinee);
            $config = config('identite.rapprochement');

            if ($candidate !== null && $score >= (float) $config['seuil_auto']) {
                return $this->lier($client, $candidate, MethodeRattachement::Empreinte, $score, $agent);
            }

            $identite = $this->creer($client, null, $agent);

            if ($candidate !== null && $score >= (float) $config['seuil_revue']) {
                FusionIdentiteEnAttente::create([
                    'identite_source_id' => $identite->id,
                    'identite_cible_id' => $candidate->id,
                    'score' => $score,
                    'statut' => StatutFusionIdentite::EnAttente,
                ]);

                Consignateur::enregistrer('systeme', null, 'fusion_identite_proposee', 'identite', $identite->id);
            }

            return $identite;
        });
    }

    /**
     * Fusion décidée par un responsable LBC/FT depuis la file d'attente : les fiches de
     * l'identité source rejoignent la cible, l'historique du rattachement est conservé.
     */
    public function fusionner(FusionIdentiteEnAttente $proposition, Agent $agent, ?string $motif = null): Identite
    {
        return DB::transaction(function () use ($proposition, $agent, $motif) {
            $source = $proposition->source;
            $cible = $proposition->cible;

            foreach ($source->clients()->get() as $client) {
                $this->lier($client, $cible, MethodeRattachement::Manuel, (float) $proposition->score, $agent, $motif);
            }

            $proposition->update([
                'statut' => StatutFusionIdentite::Confirmee,
                'decide_par_agent_id' => $agent->id,
                'motif' => $motif,
                'decide_le' => now(),
            ]);

            $source->delete();
            $this->plafond->appliquer($cible->fresh());

            Consignateur::enregistrer('agent', $agent->id, 'fusion_identite_confirmee', 'identite', $cible->id);

            return $cible->fresh();
        });
    }

    public function ecarterFusion(FusionIdentiteEnAttente $proposition, Agent $agent, string $motif): void
    {
        $proposition->update([
            'statut' => StatutFusionIdentite::Ecartee,
            'decide_par_agent_id' => $agent->id,
            'motif' => $motif,
            'decide_le' => now(),
        ]);

        Consignateur::enregistrer('agent', $agent->id, 'fusion_identite_ecartee', 'identite', $proposition->identite_source_id);
    }

    /**
     * @return array{0: ?Identite, 1: float}
     */
    private function meilleureCandidate(Client $client, ?string $empreinte): array
    {
        if ($empreinte === null) {
            return [null, 0.0];
        }

        $meilleure = null;
        $meilleurScore = 0.0;

        // Restreint au réseau : une empreinte n'est comparable qu'à clé identique.
        $candidates = Identite::where('reseau_id', $client->reseau_id)
            ->whereNotNull('empreinte_combinee')
            ->where('id', '!=', $client->identite_id ?? '')
            ->get();

        foreach ($candidates as $candidate) {
            $score = $this->empreinte->similariteCombinee($empreinte, $candidate->empreinte_combinee);

            if ($score !== null && $score > $meilleurScore) {
                $meilleurScore = $score;
                $meilleure = $candidate;
            }
        }

        return [$meilleure, $meilleurScore];
    }

    private function creer(Client $client, ?string $npiIdx, ?Agent $agent): Identite
    {
        $identite = Identite::create([
            'reseau_id' => $client->reseau_id,
            'npi_idx' => $npiIdx,
            'empreinte_combinee' => $client->personnePhysique?->empreinte_combinee,
        ]);

        return $this->lier($client, $identite, MethodeRattachement::Creation, null, $agent);
    }

    private function lier(Client $client, Identite $identite, MethodeRattachement $methode, ?float $score, ?Agent $agent, ?string $motif = null): Identite
    {
        $client->update(['identite_id' => $identite->id]);

        // Une identité créée sans NPI récupère celui de la première fiche qui en apporte un.
        if ($identite->npi_idx === null && $client->personnePhysique?->npi_idx !== null) {
            $identite->update(['npi_idx' => $client->personnePhysique->npi_idx]);
        }

        if ($identite->empreinte_combinee === null && $client->personnePhysique?->empreinte_combinee !== null) {
            $identite->update(['empreinte_combinee' => $client->personnePhysique->empreinte_combinee]);
        }

        RattachementIdentite::create([
            'identite_id' => $identite->id,
            'client_id' => $client->id,
            'methode' => $methode,
            'score' => $score,
            'decide_par_agent_id' => $agent?->id,
            'motif' => $motif,
        ]);

        $this->plafond->appliquer($identite->fresh());

        Consignateur::enregistrer(
            $agent === null ? 'systeme' : 'agent',
            $agent?->id,
            'rattachement_identite_'.$methode->value,
            'client',
            $client->id,
        );

        return $identite->fresh();
    }
}
