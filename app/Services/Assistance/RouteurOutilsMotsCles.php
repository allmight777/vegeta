<?php

namespace App\Services\Assistance;

use App\Contracts\OutilAssistantIa;
use App\Models\Admin;
use App\Models\Agent;
use App\Support\MotsCles;

/**
 * Routage d'outil sans function-calling (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §3,
 * utilisé par ProviderIaSimulateur) : associe la question à l'outil dont les mots-clés
 * se recoupent le plus, l'exécute, puis formate directement la réponse depuis le
 * résultat structuré — jamais via un texte généré. C'est le PHP qui décide ce qui est
 * montré, jamais un modèle de langage.
 */
class RouteurOutilsMotsCles
{
    /**
     * @param  array<int, OutilAssistantIa>  $outils
     */
    public function router(string $question, array $outils, Agent|Admin $utilisateur): ?string
    {
        $motsQuestion = MotsCles::extraire($question);

        if ($motsQuestion === []) {
            return null;
        }

        $meilleurOutil = null;
        $meilleurScore = 0;

        foreach ($outils as $outil) {
            $motsOutil = collect($outil->motsCles())
                ->flatMap(fn (string $motCle) => MotsCles::extraire($motCle))
                ->all();

            $score = count(array_intersect($motsQuestion, $motsOutil));

            if ($score > $meilleurScore) {
                $meilleurScore = $score;
                $meilleurOutil = $outil;
            }
        }

        // Un outil gagne clairement par mots-clés déclencheurs : on l'exécute.
        if ($meilleurOutil !== null) {
            $resultat = $meilleurOutil->executer(['question' => $question], $utilisateur);

            return $this->formater($meilleurOutil->nom(), $resultat);
        }

        // Aucun outil ne matche : filet de sécurité — on tente quand même la
        // recherche documentaire, qui compare la question au contenu réel des
        // documents, pas à une liste de mots-clés figée.
        return $this->tenterRechercheDocumentaire($question, $outils, $utilisateur);
    }

    /**
     * Filet de sécurité : quand aucun outil ne matche par mots-clés déclencheurs,
     * on tente quand même la recherche documentaire — elle compare la question au
     * contenu réel des documents (Support\MotsCles::extraire sur contenu_extrait),
     * pas à une liste de mots-clés fixe, donc une question sans les mots "document",
     * "fiche" etc. peut malgré tout trouver une réponse pertinente.
     *
     * @param  array<int, OutilAssistantIa>  $outils
     */
    private function tenterRechercheDocumentaire(string $question, array $outils, Agent|Admin $utilisateur): ?string
    {
        $outilDocumentaire = collect($outils)->first(
            fn (OutilAssistantIa $o) => $o->nom() === 'recherche_documentaire'
        );

        if ($outilDocumentaire === null) {
            return null;
        }

        $resultat = $outilDocumentaire->executer(['question' => $question], $utilisateur);

        // Pas de résultat documentaire = pas de faux positif, on renvoie null.
        if (empty($resultat['resultats'])) {
            return null;
        }

        return $this->formater($outilDocumentaire->nom(), $resultat);
    }

    /**
     * @param  array<string, mixed>  $resultat
     */
    private function formater(string $nomOutil, array $resultat): string
    {
        if (isset($resultat['erreur'])) {
            return (string) $resultat['erreur'];
        }

        return match ($nomOutil) {
            'recherche_documentaire' => $this->formaterRechercheDocumentaire($resultat),
            'base_connaissances_produit' => $this->formaterBaseConnaissances($resultat),
            'recherche_web' => $this->formaterRechercheWeb($resultat),
            'profils_incomplets' => $this->formaterProfilsIncomplets($resultat),
            'compter_alertes_du_jour' => $this->formaterAlertes($resultat),
            'consulter_parametre_reglementaire' => $this->formaterParametre($resultat),
            'rechercher_client_existant' => $resultat['trouve']
                ? 'Un dossier correspondant existe déjà dans le système.'
                : 'Aucun dossier correspondant trouvé.',
            'statistiques_agence' => $this->formaterStatistiques($resultat),
            default => GestionnaireAssistant::AUCUNE_REPONSE,
        };
    }

    private function formaterRechercheDocumentaire(array $resultat): string
    {
        if (empty($resultat['resultats'])) {
            return GestionnaireAssistant::AUCUNE_REPONSE;
        }

        $lignes = array_map(function (array $r) {
            $lien = route('documents-ia.voir', ['document' => $r['document_id']]);

            if ($r['page'] !== null) {
                $lien .= '#page='.$r['page'];
            }

            $mentionPage = $r['page'] !== null ? " (page {$r['page']})" : '';

            return "« {$r['titre']} »{$mentionPage} : {$r['extrait']}\n📄 Voir le document complet : {$lien}";
        }, $resultat['resultats']);

        return implode("\n\n", $lignes);
    }

    private function formaterBaseConnaissances(array $resultat): string
    {
        return $resultat['trouve']
            ? (string) $resultat['reponse']
            : GestionnaireAssistant::AUCUNE_REPONSE;
    }

    private function formaterRechercheWeb(array $resultat): string
    {
        if (empty($resultat['resultats'])) {
            return GestionnaireAssistant::AUCUNE_REPONSE;
        }

        $lignes = array_map(
            fn (array $r) => "{$r['titre']} ({$r['url']}) — {$r['extrait']}",
            $resultat['resultats']
        );

        return "Résultats de recherche web :\n".implode("\n", $lignes);
    }

    private function formaterProfilsIncomplets(array $resultat): string
    {
        if (($resultat['nombre_total'] ?? 0) === 0) {
            return "Aucun profil incomplet pour l'agence {$resultat['agence']}.";
        }

        $lignes = array_map(
            fn (array $d) => "{$d['dossier']} — {$d['champs_manquants']} champ(s) manquant(s)",
            $resultat['dossiers']
        );

        $texte = "{$resultat['nombre_total']} profil(s) incomplet(s) pour l'agence {$resultat['agence']} :\n"
            .implode("\n", $lignes);

        if (isset($resultat['repartition_anciennete'])) {
            $r = $resultat['repartition_anciennete'];
            $texte .= "\n\nAncienneté : {$r['moins_7_jours']} de moins de 7 jours, "
                ."{$r['entre_7_et_30_jours']} entre 7 et 30 jours, "
                ."{$r['plus_30_jours']} de plus de 30 jours.";
        }

        return $texte;
    }

    private function formaterAlertes(array $resultat): string
    {
        return "Alertes ouvertes pour l'agence {$resultat['agence']} : "
            ."{$resultat['critique']} critique(s), "
            ."{$resultat['attention']} attention, "
            ."{$resultat['info']} info.";
    }

    private function formaterParametre(array $resultat): string
    {
        if (! $resultat['trouve']) {
            return GestionnaireAssistant::AUCUNE_REPONSE;
        }

        $valeurs = collect($resultat['parametres'])
            ->map(fn ($valeur, $cle) => "{$cle} = {$valeur}")
            ->implode(', ');

        $reference = $resultat['reference_texte']
            ? ", {$resultat['reference_texte']}"
            : '';

        return "{$resultat['libelle']} : {$valeurs} "
            ."(source : {$resultat['source']}{$reference}).";
    }

    private function formaterStatistiques(array $resultat): string
    {
        return "Agence {$resultat['agence']} : {$resultat['nombre_dossiers']} dossier(s), "
            ."taux de complétude moyen {$resultat['taux_completude_moyen']} %, "
            ."{$resultat['comptes_dormants_reactives_ce_mois']} compte(s) dormant(s) réactivé(s) ce mois-ci.";
    }
}
