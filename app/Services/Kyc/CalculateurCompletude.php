<?php

namespace App\Services\Kyc;

use App\Enums\TypeClient;
use App\Models\Client;
use App\Models\PersonneMorale;
use App\Models\PersonnePhysique;

/**
 * Compare les champs renseignés au référentiel config/champs_kyc_obligatoires.php et
 * écrit le résultat sur le client et sa personne physique/morale (problème 1, §5.3).
 */
class CalculateurCompletude
{
    /**
     * @return array{score: int, champs_manquants: array<string>}
     */
    public function evaluer(Client $client): array
    {
        return $client->type === TypeClient::PersonneMorale
            ? $this->evaluerPersonneMorale($client)
            : $this->evaluerPersonnePhysique($client);
    }

    private function evaluerPersonnePhysique(Client $client): array
    {
        $personne = $client->personnePhysique;
        $champs = config('champs_kyc_obligatoires.personne_physique');

        $manquants = collect($champs)
            ->keys()
            ->reject(fn (string $code) => filled($personne?->{$code}))
            ->values()
            ->all();

        $resultat = $this->enregistrer($client, $personne, $champs, $manquants);

        return $resultat;
    }

    private function evaluerPersonneMorale(Client $client): array
    {
        $personne = $client->personneMorale;
        $champs = config('champs_kyc_obligatoires.personne_morale');

        $manquants = collect($champs)
            ->keys()
            ->reject(fn (string $code) => $this->champPersonneMoraleRenseigne($personne, $code))
            ->values()
            ->all();

        return $this->enregistrer($client, $personne, $champs, $manquants);
    }

    private function champPersonneMoraleRenseigne(?PersonneMorale $personne, string $code): bool
    {
        if ($personne === null) {
            return false;
        }

        if ($code === 'beneficiaire_effectif') {
            return $personne->beneficiaireEffectifConforme();
        }

        return filled($personne->{$code});
    }

    /**
     * @param  array<string, mixed>  $champs
     * @param  array<string>  $manquants
     * @return array{score: int, champs_manquants: array<string>}
     */
    private function enregistrer(Client $client, PersonnePhysique|PersonneMorale|null $personne, array $champs, array $manquants): array
    {
        $total = max(count($champs), 1);
        $score = (int) round((1 - count($manquants) / $total) * 100);

        if ($personne !== null) {
            $personne->champs_manquants = $manquants;
            $personne->saveQuietly();
        }

        $client->score_completude_kyc = $score;
        $client->saveQuietly();

        return ['score' => $score, 'champs_manquants' => $manquants];
    }

    /**
     * @return array<string>
     */
    public function champsBloquantsManquants(Client $client): array
    {
        $personne = $client->type === TypeClient::PersonneMorale ? $client->personneMorale : $client->personnePhysique;
        $champs = config('champs_kyc_obligatoires.'.$client->type->value);
        $manquants = $personne?->champs_manquants ?? [];

        return array_values(array_filter($manquants, fn (string $code) => (bool) ($champs[$code]['bloquant'] ?? false)));
    }
}
