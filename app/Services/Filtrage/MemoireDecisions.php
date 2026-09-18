<?php

namespace App\Services\Filtrage;

use App\Enums\MotifDecisionFiltrage;
use App\Enums\StatutFiltrage;
use App\Models\Agent;
use App\Models\Client;
use App\Models\DecisionFiltrage;
use App\Models\EntreeListe;
use App\Models\ResultatFiltrage;
use App\Models\Signataire;
use App\Services\Audit\Consignateur;
use App\Services\Securite\IndexAveugle;

/**
 * Mémoire des décisions de filtrage.
 *
 * Problème traité : un responsable qui retrouve chaque matin les correspondances qu'il
 * a déjà écartées la veille finit par tout écarter en bloc, et l'outil ne protège plus
 * personne. La mémoire supprime le bruit connu — sans jamais supprimer la trace, qui
 * reste la preuve de diligence exigible devant un inspecteur.
 *
 * Trois garde-fous, parce qu'une mémoire mal bornée devient une faille :
 *  - la clé porte le fait constaté, donc corriger le nom du membre invalide la décision ;
 *  - la décision est liée à la version de la liste : liste modifiée, revue à refaire ;
 *  - toute décision expire (config/filtrage.php).
 */
class MemoireDecisions
{
    public function __construct(private readonly IndexAveugle $indexAveugle) {}

    /**
     * Décision applicable à cette correspondance, ou null s'il faut alerter.
     */
    public function pour(Client|Signataire $cible, EntreeListe $entree): ?DecisionFiltrage
    {
        $decision = DecisionFiltrage::where('cle_decision', $this->cle($cible, $entree))->first();

        if ($decision === null || ! $decision->estValidePour($entree->version_liste)) {
            return null;
        }

        return $decision;
    }

    /**
     * Enregistre la décision du responsable pour qu'elle vaille à l'avenir, et
     * retourne la phrase d'audit assemblée à partir du motif codé.
     */
    public function enregistrer(
        ResultatFiltrage $resultat,
        Client|Signataire $cible,
        StatutFiltrage $statut,
        MotifDecisionFiltrage $motif,
        ?string $detail,
        Agent $agent,
    ): DecisionFiltrage {
        $entree = $resultat->entreeListe;
        $identiteId = $this->identiteIdDe($cible);

        $decision = DecisionFiltrage::updateOrCreate(
            ['cle_decision' => $this->cle($cible, $entree)],
            [
                'identite_id' => $identiteId,
                // Une décision prise sur une personne identifiée vaut pour tous ses
                // dossiers ; sans identité rattachée, elle ne vaut que pour cette fiche.
                'portee' => $identiteId !== null ? 'identite' : 'fiche',
                'source_liste' => $entree->source->value,
                'version_liste' => $entree->version_liste,
                'statut' => $statut,
                'motif_code' => $motif,
                'motif_detail' => $detail,
                'decide_par_agent_id' => $agent->id,
                'decide_le' => now(),
                'expire_le' => now()->addDays((int) config('filtrage.decisions.duree_jours')),
                'applications' => 0,
                'derniere_application_le' => null,
            ],
        );

        Consignateur::enregistrer('agent', $agent->id, 'memoire_decision_enregistree', 'decision_filtrage', $decision->id);

        return $decision;
    }

    /**
     * Applique une décision connue : la trace du contrôle est conservée (diligence),
     * mais aucune alerte n'est levée. Le compteur alimente la mesure des faux positifs
     * évités affichée sur le tableau de bord.
     */
    public function appliquer(DecisionFiltrage $decision, ResultatFiltrage $resultat): void
    {
        $decision->increment('applications');
        $decision->update(['derniere_application_le' => now()]);

        Consignateur::enregistrer('systeme', null, 'filtrage_decision_connue_appliquee', 'resultat_filtrage', $resultat->id);
    }

    /**
     * Empreinte du fait constaté : identité (ou nom aveugle à défaut) + nom aveugle de
     * l'entrée de liste. Aucun nom en clair n'entre ici — uniquement des index déjà
     * aveugles, recombinés par SHA-256.
     */
    public function cle(Client|Signataire $cible, EntreeListe $entree): string
    {
        $ancre = $this->identiteIdDe($cible) ?? $this->nomIdxDe($cible) ?? ('fiche:'.$cible->id);

        return hash('sha256', $ancre.'|'.$entree->nom_idx.'|'.$entree->source->value);
    }

    private function identiteIdDe(Client|Signataire $cible): ?string
    {
        if ($cible instanceof Client) {
            return $cible->identite_id;
        }

        // Un signataire n'a pas d'identité propre dans ce MVP : sa décision reste
        // attachée à son nom aveugle, donc à sa fiche de personne morale.
        return null;
    }

    private function nomIdxDe(Client|Signataire $cible): ?string
    {
        if ($cible instanceof Signataire) {
            return $cible->nom_idx;
        }

        $cible->loadMissing(['personnePhysique', 'personneMorale']);

        return $cible->personnePhysique?->nom_idx ?? $cible->personneMorale?->raison_sociale_idx;
    }
}