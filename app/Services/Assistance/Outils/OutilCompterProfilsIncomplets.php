<?php

namespace App\Services\Assistance\Outils;

use App\Contracts\OutilAssistantIa;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\Client;
use App\Services\Assistance\Outils\Concerns\ResoutAgenceOutil;

/**
 * "Quels sont les profils incomplets" (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §2.4) :
 * un comptage et une liste d'identifiants de dossier tronqués, jamais un nom — le caissier
 * a déjà le droit de voir/corriger ces dossiers dans l'espace agent, cet outil reformule
 * une liste qu'il peut déjà obtenir par la page normale, il ne révèle rien de nouveau.
 * Le responsable d'agence (et l'administrateur) reçoit en plus une répartition par
 * ancienneté — jamais de champ RLBC/FT ajouté quel que soit le rôle : cet outil ne
 * concerne que la complétude KYC.
 */
class OutilCompterProfilsIncomplets implements OutilAssistantIa
{
    use ResoutAgenceOutil;

    private const LIMITE_DOSSIERS = 15;

    public function nom(): string
    {
        return 'profils_incomplets';
    }

    public function description(): string
    {
        return 'Compte et liste (par identifiant de dossier, jamais par nom) les profils clients dont le KYC est incomplet.';
    }

    public function schemaParametres(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'agence_id' => ['type' => 'integer', 'description' => "Identifiant de l'agence (administrateur uniquement)."],
            ],
            'required' => [],
        ];
    }

    public function motsCles(): array
    {
        return ['profils incomplets', 'dossiers incomplets', 'champs manquants', 'sans nom', 'a completer'];
    }

    public function executer(array $arguments, Agent|Admin $utilisateur): array
    {
        $agence = $this->agenceCiblee($arguments, $utilisateur);

        if ($agence === null) {
            return ['erreur' => 'Précisez une agence valide.'];
        }

        $incomplets = Client::deLAgence($agence->id)
            ->with(['personnePhysique', 'personneMorale'])
            ->where('score_completude_kyc', '<', 100)
            ->get();

        $dossiers = $incomplets->take(self::LIMITE_DOSSIERS)->map(function (Client $client) {
            $personne = $client->personneMorale ?? $client->personnePhysique;

            return [
                'dossier' => 'Dossier n°'.strtoupper(substr($client->id, 0, 8)),
                'champs_manquants' => count($personne?->champs_manquants ?? []),
            ];
        })->values()->all();

        $resultat = [
            'agence' => $agence->nom,
            'nombre_total' => $incomplets->count(),
            'dossiers' => $dossiers,
        ];

        $niveauEtendu = $utilisateur instanceof Admin
            || ($utilisateur instanceof Agent && $utilisateur->estResponsableAgence());

        if ($niveauEtendu) {
            $resultat['repartition_anciennete'] = [
                'moins_7_jours' => $incomplets->filter(fn (Client $c) => $c->created_at->gt(now()->subDays(7)))->count(),
                'entre_7_et_30_jours' => $incomplets->filter(fn (Client $c) => $c->created_at->between(now()->subDays(30), now()->subDays(7)))->count(),
                'plus_30_jours' => $incomplets->filter(fn (Client $c) => $c->created_at->lt(now()->subDays(30)))->count(),
            ];
        }

        return $resultat;
    }
}
