<?php

namespace App\Services\Assistance;

use App\Contracts\OutilAssistantIa;
use App\Contracts\ProviderIa;
use App\Models\Admin;
use App\Models\Agent;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Client générique compatible API de complétion de chat (forme {model, messages}, la
 * plus répandue — aucun fournisseur précis n'est mandaté ni testé dans ce dépôt, voir
 * docs/DECISIONS.md). URL, clé et modèles en configuration `.env`, jamais en dur.
 *
 * Function-calling (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §3) : le fournisseur ne
 * reçoit que `nom()`/`description()`/`schemaParametres()` de chaque outil autorisé —
 * jamais `executer()`, qui reste appelé ici, côté PHP, jamais transmis au fournisseur.
 * Une seule itération d'appel d'outil : appel → tool_call détecté → exécution PHP →
 * second appel avec le résultat → réponse finale.
 */
class ProviderIaApiExterne implements ProviderIa
{
    public function repondre(string $question, array $contexte, array $outils, Agent|Admin $utilisateur, array $historique = []): string
    {
        $messages = [
            ['role' => 'system', 'content' => $this->construirePromptSysteme($contexte)],
            ...$historique,
            ['role' => 'user', 'content' => $question],
        ];

        $tools = $this->schemasOutils($outils);

        foreach (config('assistance.modeles', []) as $modele) {
            try {
                $reponse = $this->appeler($modele, $messages, $tools);

                if (! $reponse->successful()) {
                    continue;
                }

                $appelsOutil = $reponse->json('choices.0.message.tool_calls');

                if (! empty($appelsOutil)) {
                    $messages[] = (array) $reponse->json('choices.0.message');
                    $this->executerAppelsOutil($appelsOutil, $outils, $utilisateur, $messages);

                    $reponse = $this->appeler($modele, $messages, []);

                    if (! $reponse->successful()) {
                        continue;
                    }
                }

                $contenu = $reponse->json('choices.0.message.content');

                if (filled($contenu)) {
                    return trim((string) $contenu);
                }
            } catch (Throwable) {
                continue;
            }
        }

        throw new ProviderIaIndisponibleException('Aucun modèle de repli n\'a répondu.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<int, array<string, mixed>>  $tools
     */
    private function appeler(string $modele, array $messages, array $tools): Response
    {
        $corps = ['model' => $modele, 'messages' => $messages];

        if ($tools !== []) {
            $corps['tools'] = $tools;
            $corps['tool_choice'] = 'auto';
        }

        return Http::withToken((string) config('assistance.api_cle'))
            ->timeout(12)
            ->post((string) config('assistance.api_url'), $corps);
    }

    /**
     * @param  array<int, OutilAssistantIa>  $outils
     * @return array<int, array<string, mixed>>
     */
    private function schemasOutils(array $outils): array
    {
        return array_map(fn (OutilAssistantIa $outil) => [
            'type' => 'function',
            'function' => [
                'name' => $outil->nom(),
                'description' => $outil->description(),
                'parameters' => $outil->schemaParametres(),
            ],
        ], $outils);
    }

    /**
     * @param  array<int, array<string, mixed>>  $appelsOutil
     * @param  array<int, OutilAssistantIa>  $outils
     * @param  array<int, array<string, mixed>>  $messages
     */
    private function executerAppelsOutil(array $appelsOutil, array $outils, Agent|Admin $utilisateur, array &$messages): void
    {
        foreach ($appelsOutil as $appel) {
            $nom = $appel['function']['name'] ?? null;
            $outil = collect($outils)->first(fn (OutilAssistantIa $o) => $o->nom() === $nom);

            $arguments = json_decode((string) ($appel['function']['arguments'] ?? '{}'), true) ?: [];
            $resultat = $outil?->executer($arguments, $utilisateur) ?? ['erreur' => 'Outil inconnu.'];

            $messages[] = [
                'role' => 'tool',
                'tool_call_id' => $appel['id'] ?? null,
                'content' => json_encode($resultat, JSON_UNESCAPED_UNICODE),
            ];
        }
    }

    /**
     * Construit le prompt système uniquement à partir du contexte fermé de
     * ConstructeurContexteIa — ne jamais y ajouter autre chose "pour être plus utile"
     * (08_PROMPT §4.2, §3.2) : toute donnée supplémentaire doit repasser par la liste
     * blanche, jamais être injectée ici directement.
     */
    private function construirePromptSysteme(array $contexte): string
    {
        $lignes = [
            'Tu es l\'assistant intégré à CIF-Empreinte, un outil de conformité LBC/FT/FP pour les SFD d\'Afrique de l\'Ouest.',
            'Tu expliques des concepts et guides l\'utilisation du produit. Tu ne prends jamais de décision de conformité.',
            'Si un outil est disponible et pertinent, utilise-le plutôt que de deviner une réponse.',
            'Si la question sort de ce que le contexte ou les outils ci-dessous permettent de répondre, réponds exactement "'.GestionnaireAssistant::AUCUNE_REPONSE.'".',
            'Rôle de l\'utilisateur : '.($contexte['role'] ?? 'inconnu'),
            'Écran actuel : '.($contexte['ecran'] ?? 'inconnu'),
        ];

        if (! empty($contexte['referentiel'])) {
            $lignes[] = 'Référentiel des champs disponible : '.json_encode($contexte['referentiel'], JSON_UNESCAPED_UNICODE);
        }

        if (! empty($contexte['lexique'])) {
            $lignes[] = 'Base de connaissances produit/réglementaire : '.json_encode($contexte['lexique'], JSON_UNESCAPED_UNICODE);
        }

        if (! empty($contexte['champs_manquants'])) {
            $lignes[] = 'Champs KYC actuellement manquants (codes uniquement) : '.implode(', ', $contexte['champs_manquants']);
        }

        if (! empty($contexte['explication_alerte'])) {
            $lignes[] = 'Explication d\'alerte déjà générée par le système, à reformuler si besoin : '.$contexte['explication_alerte'];
        }

        return implode("\n", $lignes);
    }
}
