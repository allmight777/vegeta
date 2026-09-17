<?php

namespace App\Services\Kyc;

use App\Enums\TypeClient;
use App\Models\Client;
use App\Models\PersonneMorale;
use App\Models\PersonnePhysique;

/**
 * Compare les champs renseignés au référentiel config/champs_fiche_adhesion.php et
 * écrit le résultat sur le client et sa personne physique/morale (problème 1, §5.3).
 */
class CalculateurCompletude
{
    public function __construct(private readonly ReferentielFicheAdhesion $referentiel) {}

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
        $champs = $this->referentiel->champsPlats('personne_physique');

        $manquants = collect($champs)
            ->keys()
            ->reject(fn (string $code) => $this->champRenseigne($personne, $code))
            ->values()
            ->all();

        return $this->enregistrer($client, $personne, $champs, $manquants);
    }

    private function evaluerPersonneMorale(Client $client): array
    {
        $personne = $client->personneMorale;
        $champs = $this->referentiel->champsPlats('personne_morale')
            + $this->referentiel->champsCalcules('personne_morale');

        $manquants = collect($champs)
            ->keys()
            ->reject(fn (string $code) => $this->champPersonneMoraleRenseigne($personne, $code))
            ->values()
            ->all();

        return $this->enregistrer($client, $personne, $champs, $manquants);
    }

    /**
     * Cas particuliers non représentables par un simple filled() sur une colonne.
     */
    private function champRenseigne(?PersonnePhysique $personne, string $code): bool
    {
        if ($personne === null) {
            return false;
        }

        if ($code === 'npi') {
            return filled($personne->npi_idx);
        }

        return filled($personne->{$code});
    }

    private function champPersonneMoraleRenseigne(?PersonneMorale $personne, string $code): bool
    {
        if ($personne === null) {
            return false;
        }

        if ($code === 'beneficiaire_effectif') {
            return $personne->beneficiaireEffectifConforme();
        }

        if ($code === 'au_moins_un_signataire') {
            return $personne->signataires()->exists();
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
        $champs = $client->type === TypeClient::PersonneMorale
            ? $this->referentiel->champsPlats('personne_morale') + $this->referentiel->champsCalcules('personne_morale')
            : $this->referentiel->champsPlats('personne_physique');
        $manquants = $personne?->champs_manquants ?? [];

        return array_values(array_filter($manquants, fn (string $code) => (bool) ($champs[$code]['bloquant'] ?? false)));
    }
}
