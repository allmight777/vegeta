<?php

namespace App\Services\Filtrage;

use App\Enums\GraviteAlerte;
use App\Enums\StatutAlerte;
use App\Enums\StatutFiltrage;
use App\Enums\TypeAlerte;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\EntreeListe;
use App\Models\ResultatFiltrage;
use App\Models\Signataire;
use App\Services\Empreinte\ServiceEmpreinte;
use App\Services\Explication\GenerateurExplication;
use Illuminate\Support\Collection;

/**
 * Filtrage sanctions/PPE (problème 2) : empreinte de la cible → comparaison à
 * entrees_liste → resultats_filtrage + alerte si le score dépasse le seuil bas.
 * Seuils calibrés (§6.1) : jamais de confirmation automatique, toujours une revue humaine.
 *
 * Le moteur interroge la mémoire des décisions avant d'alerter : une correspondance
 * déjà tranchée par le responsable est toujours contrôlée et journalisée, mais ne
 * relève plus d'alerte. Sans ça, le même homonyme revient chaque jour, sur chaque
 * dossier de la même personne, et le responsable finit par tout écarter en bloc.
 */
class MoteurFiltrage
{
    private const SEUIL_BAS = 0.70;

    private const SEUIL_CRITIQUE = 0.85;

    public function __construct(
        private readonly ServiceEmpreinte $empreinte,
        private readonly GenerateurExplication $explication,
        private readonly MemoireDecisions $memoire,
    ) {}

    /**
     * @return Collection<int, ResultatFiltrage>
     */
    public function filtrer(Client|Signataire $cible): Collection
    {
        $empreinteCible = $this->empreinteNomDe($cible);

        if ($empreinteCible === null) {
            return collect();
        }

        $resultats = collect();

        foreach (EntreeListe::all() as $entree) {
            $score = $this->empreinte->similariteNom($empreinteCible, $entree->empreinte_nom);

            if ($score === null || $score < self::SEUIL_BAS) {
                continue;
            }

            $resultat = ResultatFiltrage::firstOrNew([
                'filtrable_type' => $cible instanceof Client ? 'client' : 'signataire',
                'filtrable_id' => $cible->id,
                'entree_liste_id' => $entree->id,
            ]);
            $estNouveau = ! $resultat->exists;
            $resultat->fill(['score_similarite' => $score]);

            // Le responsable a-t-il déjà tranché ce cas exact ? La décision suit la
            // personne et non la fiche : écarté une fois à Dassa, valable à Savalou.
            $decision = $this->memoire->pour($cible, $entree);

            if ($estNouveau) {
                $resultat->statut = $decision?->statut ?? StatutFiltrage::AVerifier;
            }
            $resultat->save();

            if ($decision !== null) {
                // Contrôle effectué et journalisé (preuve de diligence), mais pas de
                // nouvelle alerte : c'est du bruit déjà qualifié par un humain.
                $this->memoire->appliquer($decision, $resultat);

                if ($decision->statut === StatutFiltrage::Confirme) {
                    // Une correspondance confirmée, elle, doit toujours ressortir.
                    $this->creerAlerte($cible, $entree, $resultat, $score, GraviteAlerte::Critique);
                }

                $resultats->push($resultat);

                continue;
            }

            if ($estNouveau) {
                $this->creerAlerte($cible, $entree, $resultat, $score);
            }
            $resultats->push($resultat);
        }

        $this->mettreAJourStatutCible($cible, $resultats);

        return $resultats;
    }

    /**
     * Client ne porte pas empreinte_nom en direct : le vecteur vit sur sa personne
     * physique ou morale. Un signataire porte le sien en propre.
     */
    private function empreinteNomDe(Client|Signataire $cible): ?string
    {
        if ($cible instanceof Signataire) {
            return $cible->empreinte_nom;
        }

        $cible->loadMissing(['personnePhysique', 'personneMorale']);

        return $cible->personnePhysique?->empreinte_nom ?? $cible->personneMorale?->empreinte_nom;
    }

    private function creerAlerte(Client|Signataire $cible, EntreeListe $entree, ResultatFiltrage $resultat, float $score, ?GraviteAlerte $graviteForcee = null): void
    {
        $client = $cible instanceof Client ? $cible : $cible->personneMorale->client;
        $estPpe = in_array($entree->source->value, ['ppe_benin', 'ppe_cedeao'], true);

        Alerte::create([
            'type' => $estPpe ? TypeAlerte::FiltragePpe : TypeAlerte::FiltrageSanction,
            'client_id' => $client->id,
            'resultat_filtrage_id' => $resultat->id,
            'gravite' => $graviteForcee ?? ($score >= self::SEUIL_CRITIQUE ? GraviteAlerte::Critique : GraviteAlerte::Attention),
            'explication_texte' => $this->explication->pourFiltrage($cible, $entree, $score),
            'faits' => [
                'score_similarite' => round($score, 4),
                'source_liste' => $entree->source->value,
                'resultat_filtrage_id' => $resultat->id,
            ],
            'statut' => StatutAlerte::Nouvelle,
        ]);

        if ($estPpe && $client->statut_ppe->value === 'non_ppe') {
            $client->update(['statut_ppe' => 'ppe_a_verifier']);
        }
    }

    /**
     * @param  Collection<int, ResultatFiltrage>  $resultats
     */
    private function mettreAJourStatutCible(Client|Signataire $cible, Collection $resultats): void
    {
        if (! $cible instanceof Signataire) {
            return;
        }

        $cible->statut_filtrage = $resultats->isEmpty() ? StatutFiltrage::Ecarte : StatutFiltrage::AVerifier;
        $cible->saveQuietly();
    }
}