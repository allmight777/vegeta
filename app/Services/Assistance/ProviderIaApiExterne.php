<?php

namespace App\Services\Assistance;

use App\Contracts\ProviderIa;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Client générique compatible API de complétion de chat (forme {model, messages}, la
 * plus répandue — aucun fournisseur précis n'est mandaté ni testé dans ce dépôt, voir
 * docs/DECISIONS.md). URL, clé et modèles en configuration `.env`, jamais en dur.
 */
class ProviderIaApiExterne implements ProviderIa
{
    public function repondre(string $question, array $contexte, array $historique = []): string
    {
        $messages = [
            ['role' => 'system', 'content' => $this->construirePromptSysteme($contexte)],
            ...$historique,
            ['role' => 'user', 'content' => $question],
        ];

        foreach (config('assistance.modeles', []) as $modele) {
            try {
                $reponse = Http::withToken((string) config('assistance.api_cle'))
                    ->timeout(12)
                    ->post((string) config('assistance.api_url'), [
                        'model' => $modele,
                        'messages' => $messages,
                    ]);

                if ($reponse->successful()) {
                    $contenu = $reponse->json('choices.0.message.content');

                    if (filled($contenu)) {
                        return trim((string) $contenu);
                    }
                }
            } catch (Throwable) {
                continue;
            }
        }

        throw new ProviderIaIndisponibleException('Aucun modèle de repli n\'a répondu.');
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
            'Si la question sort de ce que le contexte ci-dessous permet de répondre, réponds exactement "'.GestionnaireAssistant::AUCUNE_REPONSE.'".',
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
