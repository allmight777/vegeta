<?php

namespace App\Services\Assistance\Outils;

use App\Contracts\OutilAssistantIa;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\DocumentIa;
use App\Support\MotsCles;
use Illuminate\Database\Eloquent\Builder;

/**
 * Cherche dans `documents_ia.contenu_extrait` — uniquement les documents visibles pour le
 * rôle et l'agence de l'appelant (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §2.2).
 * Correspondance par mots-clés (même normalisation que la base de connaissances produit) —
 * pas de base vectorielle nécessaire pour le volume de démonstration.
 */
class OutilRechercheDocumentaire implements OutilAssistantIa
{
    private const NOMBRE_RESULTATS = 3;

    private const LONGUEUR_EXTRAIT = 1200;

    private const RAYON_DENSITE = 400;

    public function nom(): string
    {
        return 'recherche_documentaire';
    }

    public function description(): string
    {
        return "Cherche dans les documents internes déposés par l'administrateur (procédures, guides, textes) visibles pour l'utilisateur actuel.";
    }

    public function schemaParametres(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'requete' => ['type' => 'string', 'description' => 'Termes à rechercher dans les documents internes.'],
            ],
            'required' => ['requete'],
        ];
    }

    public function motsCles(): array
    {
        return ['document', 'documents', 'fiche', 'procedure', 'guide', 'politique', 'reglement interne'];
    }

    public function executer(array $arguments, Agent|Admin $utilisateur): array
    {
        $requete = (string) ($arguments['requete'] ?? $arguments['question'] ?? '');
        $motsRequete = MotsCles::extraire($requete);

        if ($motsRequete === []) {
            return ['resultats' => []];
        }

        $resultats = $this->documentsVisibles($utilisateur)
            ->get()
            ->map(function (DocumentIa $document) use ($motsRequete) {
                $motsDocument = MotsCles::extraire($document->titre.' '.$document->contenu_extrait);
                $score = count(array_intersect($motsRequete, $motsDocument));

                return ['document' => $document, 'score' => $score];
            })
            ->filter(fn (array $ligne) => $ligne['score'] > 0)
            ->sortByDesc('score')
            ->take(self::NOMBRE_RESULTATS)
            ->map(function (array $ligne) use ($motsRequete) {
                $contenu = (string) $ligne['document']->contenu_extrait;
                [$extrait, $page] = $this->extraitPertinent($contenu, $motsRequete);

                return [
                    'document_id' => $ligne['document']->id,
                    'titre' => $ligne['document']->titre,
                    'extrait' => $extrait,
                    'page' => $page,
                ];
            })
            ->values()
            ->all();

        return ['resultats' => $resultats];
    }

    /**
     * Trouve la zone du texte où le plus de mots-clés de la requête se regroupent
     * (densité), plutôt que la première occurrence d'un seul mot — un mot isolé comme
     * "article" apparaît partout dans un texte réglementaire sans rapport avec le
     * sujet réel de la question. Renvoie aussi le numéro de page (compté via les
     * sauts de page \f insérés par ExtracteurDocumentOcrLocal/ExtracteurDocumentTexteNatif),
     * ou null si le document n'a pas ce marquage (ex. texte natif sans découpage par page).
     *
     * @param  array<int, string>  $motsRequete
     * @return array{0: string, 1: ?int} [extrait, numéro de page (1-indexé) ou null]
     */
    private function extraitPertinent(string $contenu, array $motsRequete): array
    {
        $contenuLower = mb_strtolower($contenu);

        $positions = [];
        foreach (array_unique($motsRequete) as $mot) {
            $offset = 0;
            while (($trouve = mb_stripos($contenuLower, $mot, $offset)) !== false) {
                $positions[] = $trouve;
                $offset = $trouve + mb_strlen($mot);
            }
        }

        if ($positions === []) {
            return [mb_substr($contenu, 0, self::LONGUEUR_EXTRAIT), $this->numeroPage($contenu, 0)];
        }

        sort($positions);

        $meilleurePosition = $positions[0];
        $meilleureDensite = 0;

        foreach ($positions as $position) {
            $densite = count(array_filter(
                $positions,
                fn (int $autre) => abs($autre - $position) <= self::RAYON_DENSITE,
            ));

            if ($densite > $meilleureDensite) {
                $meilleureDensite = $densite;
                $meilleurePosition = $position;
            }
        }

        $debut = max(0, $meilleurePosition - 150);

        return [
            mb_substr($contenu, $debut, self::LONGUEUR_EXTRAIT),
            $this->numeroPage($contenu, $meilleurePosition),
        ];
    }

    /**
     * Numéro de page (1-indexé) déduit du nombre de sauts de page \f avant la
     * position donnée. Renvoie null si le texte ne contient aucun \f (document dont
     * l'extraction n'a pas conservé les limites de page).
     */
    private function numeroPage(string $contenu, int $position): ?int
    {
        $avant = mb_substr($contenu, 0, $position);

        if (! str_contains($avant, "\f")) {
            return str_contains($contenu, "\f") ? 1 : null;
        }

        return substr_count($avant, "\f") + 1;
    }

    private function documentsVisibles(Agent|Admin $utilisateur): Builder
    {
        $requete = DocumentIa::query()->where('statut_extraction', 'reussie');

        if ($utilisateur instanceof Admin) {
            return $requete->when(! $utilisateur->estAdminPlateforme(), fn (Builder $q) => $q->where(function (Builder $q) use ($utilisateur) {
                $q->whereNull('reseau_id')->whereNull('agence_id')
                    ->orWhere('reseau_id', $utilisateur->reseau_id);
            }));
        }

        $colonneVisibilite = $utilisateur->estCaissier() ? 'visible_caissier' : 'visible_responsable_agence';
        $reseauId = $utilisateur->agence->reseau_id;
        $agenceId = $utilisateur->agence_id;

        return $requete->where($colonneVisibilite, true)->where(function (Builder $q) use ($reseauId, $agenceId) {
            $q->where(fn (Builder $q) => $q->whereNull('reseau_id')->whereNull('agence_id'))
                ->orWhere('agence_id', $agenceId)
                ->orWhere(fn (Builder $q) => $q->where('reseau_id', $reseauId)->whereNull('agence_id'));
        });
    }
}
