<?php

namespace App\Services\Filtrage;

use App\Models\Client;
use App\Models\EntreeListe;
use App\Models\PersonneMorale;
use App\Models\Signataire;
use App\Services\Empreinte\ServiceEmpreinte;
use Carbon\CarbonInterface;
use Closure;

/**
 * Refiltrage du parc existant après publication d'une liste (problème réglementaire
 * n° 2 bis).
 *
 * Sans ce service, le filtrage n'a lieu qu'à la création du dossier : un membre
 * inscrit hier sur la liste ONU ne serait jamais détecté, puisqu'il ne repassera
 * plus jamais par `MoteurFiltrage::filtrer()`. C'est précisément ce que la Loi
 * uniforme (art. 2 §58) et l'Instruction BCEAO 001-03-2025 (art. 2 §23, art. 6)
 * interdisent : les mesures de gel s'appliquent « sans délai », soit 24 heures au
 * maximum après publication.
 *
 * Le service mesure donc aussi le délai entre la publication de la liste et la fin
 * du refiltrage, et le compare à l'échéance réglementaire — c'est cette mesure que
 * `CLAUDE.md` §41 exige d'afficher.
 */
class RefiltrageParc
{
    /** Échéance « sans délai » de l'Instruction BCEAO 001-03-2025 : 24 heures. */
    public const ECHEANCE_REGLEMENTAIRE_HEURES = 24;

    public function __construct(
        private readonly MoteurFiltrage $moteur,
        private readonly ServiceEmpreinte $empreinte,
    ) {}

    /**
     * Recalcule les empreintes manquantes des entrées de liste.
     *
     * Filet de sécurité : une entrée sans empreinte ne matche jamais, et l'échec
     * est totalement silencieux — le pire mode de défaillance possible pour un
     * dispositif de conformité.
     */
    public function recalculerEmpreintesListes(): int
    {
        $corrigees = 0;

        EntreeListe::whereNull('empreinte_nom')->chunkById(200, function ($entrees) use (&$corrigees) {
            foreach ($entrees as $entree) {
                $this->empreinte->calculerPourEntreeListe($entree);
                $entree->saveQuietly();
                $corrigees++;
            }
        });

        return $corrigees;
    }

    /**
     * Rejoue le filtrage sur tout le parc : clients (personnes physiques et morales)
     * puis signataires de personnes morales, qui doivent être contrôlés
     * individuellement (problème 4).
     *
     * @param  Closure(int, int): void|null  $progression  appelée (traités, total)
     */
    public function refiltrerTout(?CarbonInterface $publieeLe = null, ?Closure $progression = null): RapportRefiltrage
    {
        $this->moteur->rafraichirListes();

        $debut = now();
        $rapport = new RapportRefiltrage(
            demarreLe: $debut,
            listePublieeLe: $publieeLe,
        );

        $total = Client::count() + Signataire::count();
        $traites = 0;

        Client::with(['personnePhysique', 'personneMorale'])
            ->chunkById(100, function ($clients) use ($rapport, $progression, $total, &$traites) {
                foreach ($clients as $client) {
                    $resultats = $this->moteur->filtrer($client);
                    $rapport->enregistrer($resultats->count());
                    $traites++;
                    $progression && $progression($traites, $total);
                }
            });

        Signataire::with('personneMorale')
            ->chunkById(100, function ($signataires) use ($rapport, $progression, $total, &$traites) {
                foreach ($signataires as $signataire) {
                    $resultats = $this->moteur->filtrer($signataire);
                    $rapport->enregistrer($resultats->count());
                    $traites++;
                    $progression && $progression($traites, $total);
                }
            });

        $rapport->cloturer(now(), $traites);

        return $rapport;
    }

    /**
     * Refiltrage ciblé : uniquement les dossiers du réseau concerné. Utilisé quand
     * une liste n'est publiée que pour un réseau (liste interne d'un SFD).
     *
     * @param  Closure(int, int): void|null  $progression
     */
    public function refiltrerReseau(int $reseauId, ?CarbonInterface $publieeLe = null, ?Closure $progression = null): RapportRefiltrage
    {
        $this->moteur->rafraichirListes();

        $debut = now();
        $rapport = new RapportRefiltrage(demarreLe: $debut, listePublieeLe: $publieeLe);

        $idsPersonnesMorales = PersonneMorale::whereHas(
            'client',
            fn ($q) => $q->where('reseau_id', $reseauId)
        )->pluck('id');

        $total = Client::where('reseau_id', $reseauId)->count()
            + Signataire::whereIn('personne_morale_id', $idsPersonnesMorales)->count();
        $traites = 0;

        Client::where('reseau_id', $reseauId)
            ->with(['personnePhysique', 'personneMorale'])
            ->chunkById(100, function ($clients) use ($rapport, $progression, $total, &$traites) {
                foreach ($clients as $client) {
                    $rapport->enregistrer($this->moteur->filtrer($client)->count());
                    $traites++;
                    $progression && $progression($traites, $total);
                }
            });

        Signataire::whereIn('personne_morale_id', $idsPersonnesMorales)
            ->chunkById(100, function ($signataires) use ($rapport, $progression, $total, &$traites) {
                foreach ($signataires as $signataire) {
                    $rapport->enregistrer($this->moteur->filtrer($signataire)->count());
                    $traites++;
                    $progression && $progression($traites, $total);
                }
            });

        $rapport->cloturer(now(), $traites);

        return $rapport;
    }
}
