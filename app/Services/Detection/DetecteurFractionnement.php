<?php

namespace App\Services\Detection;

use App\Enums\GraviteAlerte;
use App\Enums\ModePaiement;
use App\Enums\StatutAlerte;
use App\Enums\TypeAlerte;
use App\Enums\TypeOperation;
use App\Models\Alerte;
use App\Models\DeclarationCentif;
use App\Models\Operation;
use App\Models\RegleDetection;
use App\Services\Audit\Consignateur;
use App\Services\Explication\GenerateurExplication;
use App\Services\Identite\CompteurCumuls;
use Illuminate\Support\Collection;

/**
 * Les 5 règles du MVP resserré. Chaque exécution est journalisée : quelles opérations ont
 * été rapprochées et sur quel critère (transparence, jamais une boîte noire).
 *
 * Deux principes tiennent tout le fichier :
 *  - l'unité de contrôle est la PERSONNE (identité), pas le compte ni la fiche client ;
 *  - seules les ESPÈCES sont concernées par les seuils et les cumuls (Loi art. 17 i, 72).
 */
class DetecteurFractionnement
{
    public function __construct(
        private readonly GenerateurExplication $explication,
        private readonly CompteurCumuls $compteurCumuls,
        private readonly SurveillantPlafondQuotidien $surveillantPlafond,
    ) {}

    public function analyserApresOperation(Operation $operation): void
    {
        // Le cumul du jour est mis à jour pour toute opération, puis le plafond est
        // vérifié : c'est l'alerte temps réel attendue au guichet.
        $cumul = $this->compteurCumuls->enregistrer($operation);
        $this->surveillantPlafond->verifier($operation, $cumul);

        if ($operation->mode_paiement !== ModePaiement::Especes) {
            return;
        }

        if ($operation->type !== TypeOperation::Depot) {
            $this->verifierSeuilCentif($operation);

            return;
        }

        $this->verifierGuichet($operation);
        $this->verifierMultiAgences($operation);
        $this->verifierDormant($operation);
        $this->verifierSeuilCentif($operation);
    }

    private function regleActive(string $code): ?RegleDetection
    {
        return RegleDetection::where('code', $code)->where('actif', true)->first();
    }

    /**
     * Fractionnement sur un même compte : ce qui signe l'intention, ce n'est pas le cumul
     * seul (un gros versement légitime le dépasserait aussi), c'est une série de dépôts
     * dont CHACUN reste sous le seuil unitaire alors que leur somme le franchit.
     */
    private function verifierGuichet(Operation $operation): void
    {
        $regle = $this->regleActive('FRACTIONNEMENT_GUICHET');
        if ($regle === null) {
            return;
        }
        $params = $regle->parametres;

        $fenetreDebut = $operation->effectuee_le->copy()->subHours((int) $params['fenetre_heures']);
        $operations = Operation::where('compte_id', $operation->compte_id)
            ->where('type', TypeOperation::Depot)
            ->where('mode_paiement', ModePaiement::Especes)
            ->whereBetween('effectuee_le', [$fenetreDebut, $operation->effectuee_le])
            ->get();

        if (! $this->ressembleAUnFractionnement($operations, $params)) {
            return;
        }

        $client = $operation->compte->client;
        if ($this->alerteDejaOuverte($client->clientIdsDeLIdentite(), TypeAlerte::FractionnementGuichet)) {
            return;
        }

        $cumul = (float) $operations->sum('montant');

        Alerte::create([
            'type' => TypeAlerte::FractionnementGuichet,
            'client_id' => $client->id,
            'regle_detection_id' => $regle->id,
            'gravite' => GraviteAlerte::Attention,
            'explication_texte' => $this->explication->pourFractionnementGuichet(
                $operations->count(),
                $this->formaterMontant($cumul, $operation->devise_code),
                $this->formaterMontant((float) $params['seuil_cumul'], $operation->devise_code),
                (int) $params['fenetre_heures'],
            ),
            'faits' => [
                'nb_operations' => $operations->count(),
                'montant_cumule' => $cumul,
                'seuil_unitaire' => $params['seuil_unitaire'],
                'seuil_cumul' => $params['seuil_cumul'],
                'compte_id' => $operation->compte_id,
                'operations_ids' => $operations->pluck('id')->all(),
            ],
            'statut' => StatutAlerte::Nouvelle,
        ]);

        Consignateur::enregistrer('systeme', null, 'detection_fractionnement_guichet', 'client', $client->id);
    }

    /**
     * Même logique, mais à l'échelle de la personne : toutes ses fiches, tous ses comptes,
     * toutes les agences. Le groupe de fiches vient de l'identité (rapprochée par NPI, ou
     * par empreinte en secours) — plus aucune comparaison d'empreintes ici, donc plus de
     * balayage de toute la base à chaque opération.
     */
    private function verifierMultiAgences(Operation $operation): void
    {
        $regle = $this->regleActive('FRACTIONNEMENT_MULTI_AGENCES');
        if ($regle === null) {
            return;
        }
        $params = $regle->parametres;

        $client = $operation->compte->client;
        $identite = $client->identite;
        $clientIds = $client->clientIdsDeLIdentite();

        $fenetreDebut = $operation->effectuee_le->copy()->subDays((int) $params['fenetre_jours']);
        $operations = Operation::whereHas('compte', fn ($q) => $q->whereIn('client_id', $clientIds))
            ->where('type', TypeOperation::Depot)
            ->where('mode_paiement', ModePaiement::Especes)
            ->whereBetween('effectuee_le', [$fenetreDebut, $operation->effectuee_le])
            ->get();

        if (! $this->ressembleAUnFractionnement($operations, $params)) {
            return;
        }

        $nbAgences = $operations->pluck('agence_id')->unique()->count();
        if ($nbAgences < 2) {
            return;
        }

        if ($this->alerteDejaOuverte($clientIds, TypeAlerte::FractionnementMultiAgences)) {
            return;
        }

        $cumul = (float) $operations->sum('montant');
        $rapprochement = $identite?->rapprocheeParNpi() ? 'npi' : 'empreinte';

        Alerte::create([
            'type' => TypeAlerte::FractionnementMultiAgences,
            'client_id' => $client->id,
            'regle_detection_id' => $regle->id,
            'gravite' => GraviteAlerte::Critique,
            'explication_texte' => $this->explication->pourFractionnementMultiAgences(
                $operations->count(),
                $this->formaterMontant($cumul, $operation->devise_code),
                $nbAgences,
                $this->formaterMontant((float) $params['seuil_cumul'], $operation->devise_code),
                (int) $params['fenetre_jours'],
                $rapprochement,
            ),
            'faits' => [
                'identite_id' => $identite?->id,
                'rapprochement' => $rapprochement,
                'nb_operations' => $operations->count(),
                'montant_cumule' => $cumul,
                'nb_agences' => $nbAgences,
                'seuil_unitaire' => $params['seuil_unitaire'],
                'seuil_cumul' => $params['seuil_cumul'],
                'fiches_rapprochees' => count($clientIds),
                'operations_ids' => $operations->pluck('id')->all(),
            ],
            'statut' => StatutAlerte::Nouvelle,
        ]);

        Consignateur::enregistrer('systeme', null, 'detection_fractionnement_multi_agences', 'client', $client->id);
    }

    /**
     * @param  Collection<int, Operation>  $operations
     * @param  array<string, mixed>  $params
     */
    private function ressembleAUnFractionnement(Collection $operations, array $params): bool
    {
        if ($operations->count() < (int) $params['min_operations']) {
            return false;
        }

        // Aucun dépôt ne doit atteindre le seuil unitaire : sinon ce n'est pas un
        // fractionnement, c'est une opération visible, traitée par la règle de seuil.
        if ((float) $operations->max('montant') >= (float) $params['seuil_unitaire']) {
            return false;
        }

        return (float) $operations->sum('montant') >= (float) $params['seuil_cumul'];
    }

    private function verifierDormant(Operation $operation): void
    {
        $regle = $this->regleActive('COMPTE_DORMANT_REACTIVE');
        if ($regle === null) {
            return;
        }
        $params = $regle->parametres;
        $compte = $operation->compte;

        if ((float) $operation->montant < $params['montant_min'] || $compte->derniere_operation_le === null) {
            return;
        }

        $seuilDate = $operation->effectuee_le->copy()->subMonths((int) $params['mois_inactivite']);
        if ($compte->derniere_operation_le->gt($seuilDate)) {
            return;
        }

        $client = $compte->client;
        if ($this->alerteDejaOuverte([$client->id], TypeAlerte::CompteDormantReactive)) {
            return;
        }

        $moisInactivite = (int) $compte->derniere_operation_le->diffInMonths($operation->effectuee_le);

        Alerte::create([
            'type' => TypeAlerte::CompteDormantReactive,
            'client_id' => $client->id,
            'regle_detection_id' => $regle->id,
            'gravite' => GraviteAlerte::Attention,
            'explication_texte' => $this->explication->pourCompteDormant($moisInactivite, $this->formaterMontant((float) $operation->montant, $operation->devise_code)),
            'faits' => [
                'mois_inactivite' => $moisInactivite,
                'montant' => (float) $operation->montant,
                'compte_id' => $compte->id,
                'operation_id' => $operation->id,
            ],
            'statut' => StatutAlerte::Nouvelle,
        ]);

        Consignateur::enregistrer('systeme', null, 'detection_compte_dormant', 'client', $client->id);
    }

    /**
     * Cumul mensuel d'espèces de la PERSONNE (toutes ses fiches), pas du seul compte.
     * Libellé volontairement distinct d'une déclaration de soupçon : ici, c'est un seuil
     * qui déclenche, pas une analyse humaine.
     */
    private function verifierSeuilCentif(Operation $operation): void
    {
        $regle = $this->regleActive('SEUIL_MENSUEL_CENTIF');
        if ($regle === null) {
            return;
        }
        $seuil = (float) $regle->parametres['seuil'];
        $client = $operation->compte->client;
        $periode = $operation->effectuee_le->format('Y-m');

        $cumul = (float) Operation::whereHas('compte', fn ($q) => $q->whereIn('client_id', $client->clientIdsDeLIdentite()))
            ->where('mode_paiement', ModePaiement::Especes)
            ->whereBetween('effectuee_le', [$operation->effectuee_le->copy()->startOfMonth(), $operation->effectuee_le->copy()->endOfMonth()])
            ->sum('montant');

        if ($cumul < $seuil) {
            return;
        }

        DeclarationCentif::updateOrCreate(
            ['client_id' => $client->id, 'periode' => $periode],
            ['montant_cumule' => $cumul, 'statut' => 'a_preparer'],
        );

        Consignateur::enregistrer('systeme', null, 'seuil_centif_atteint', 'client', $client->id);
    }

    /**
     * @param  array<int, string>  $clientIds
     */
    private function alerteDejaOuverte(array $clientIds, TypeAlerte $type): bool
    {
        return Alerte::whereIn('client_id', $clientIds)
            ->where('type', $type)
            ->where('statut', '!=', StatutAlerte::Traitee)
            ->exists();
    }

    /**
     * Pas de dépendance à l'extension intl (CLAUDE.md §4) : séparateur de milliers
     * manuel, format "1 500 000 XOF".
     */
    private function formaterMontant(float $montant, string $devise): string
    {
        return number_format($montant, 0, ',', ' ').' '.$devise;
    }
}
