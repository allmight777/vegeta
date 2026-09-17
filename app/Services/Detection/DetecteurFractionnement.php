<?php

namespace App\Services\Detection;

use App\Enums\GraviteAlerte;
use App\Enums\StatutAlerte;
use App\Enums\TypeAlerte;
use App\Enums\TypeClient;
use App\Enums\TypeOperation;
use App\Models\Alerte;
use App\Models\DeclarationCentif;
use App\Models\Operation;
use App\Models\PersonnePhysique;
use App\Models\RegleDetection;
use App\Services\Audit\Consignateur;
use App\Services\Empreinte\ServiceEmpreinte;
use App\Services\Explication\GenerateurExplication;

/**
 * Les 4 règles du MVP resserré (§6.3, §11). Chaque exécution est journalisée : quelles
 * opérations ont été rapprochées et sur quel critère (transparence, jamais une boîte noire).
 */
class DetecteurFractionnement
{
    public function __construct(
        private readonly ServiceEmpreinte $empreinte,
        private readonly GenerateurExplication $explication,
    ) {}

    public function analyserApresOperation(Operation $operation): void
    {
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
            ->whereBetween('effectuee_le', [$fenetreDebut, $operation->effectuee_le])
            ->get();

        if ($operations->count() < $params['min_operations']) {
            return;
        }
        $cumul = (float) $operations->sum('montant');
        if ($cumul < $params['seuil']) {
            return;
        }

        $client = $operation->compte->client;
        if ($this->alerteDejaOuverte($client->id, TypeAlerte::FractionnementGuichet)) {
            return;
        }

        Alerte::create([
            'type' => TypeAlerte::FractionnementGuichet,
            'client_id' => $client->id,
            'regle_detection_id' => $regle->id,
            'gravite' => GraviteAlerte::Attention,
            'explication_texte' => $this->explication->pourFractionnementGuichet(
                $operations->count(),
                $this->formaterMontant($cumul, $operation->devise_code),
                $this->formaterMontant((float) $params['seuil'], $operation->devise_code),
                (int) $params['fenetre_heures'],
            ),
            'faits' => [
                'nb_operations' => $operations->count(),
                'montant_cumule' => $cumul,
                'seuil' => $params['seuil'],
                'compte_id' => $operation->compte_id,
                'operations_ids' => $operations->pluck('id')->all(),
            ],
            'statut' => StatutAlerte::Nouvelle,
        ]);

        Consignateur::enregistrer('systeme', null, 'detection_fractionnement_guichet', 'client', $client->id);
    }

    private function verifierMultiAgences(Operation $operation): void
    {
        $regle = $this->regleActive('FRACTIONNEMENT_MULTI_AGENCES');
        if ($regle === null) {
            return;
        }
        $params = $regle->parametres;

        $client = $operation->compte->client;
        if ($client->type !== TypeClient::PersonnePhysique) {
            return;
        }
        $personne = $client->personnePhysique;
        if ($personne?->empreinte_combinee === null) {
            return;
        }

        $groupeClientIds = collect([$client->id]);
        foreach (PersonnePhysique::whereHas('client', fn ($q) => $q->where('reseau_id', $client->reseau_id)->where('id', '!=', $client->id))->get() as $autre) {
            $score = $this->empreinte->similariteCombinee($personne->empreinte_combinee, $autre->empreinte_combinee);
            if ($score !== null && $score >= (float) $params['seuil_rapprochement']) {
                $groupeClientIds->push($autre->client_id);
            }
        }

        if ($groupeClientIds->count() < 2) {
            return;
        }

        $fenetreDebut = $operation->effectuee_le->copy()->subDays((int) $params['fenetre_jours']);
        $operations = Operation::whereHas('compte', fn ($q) => $q->whereIn('client_id', $groupeClientIds))
            ->where('type', TypeOperation::Depot)
            ->whereBetween('effectuee_le', [$fenetreDebut, $operation->effectuee_le])
            ->get();

        if ($operations->count() < $params['min_operations']) {
            return;
        }
        $cumul = (float) $operations->sum('montant');
        if ($cumul < $params['seuil'] || $operations->max('montant') >= $params['seuil']) {
            return;
        }

        $nbAgences = $operations->pluck('agence_id')->unique()->count();
        if ($nbAgences < 2) {
            return;
        }

        if ($this->alerteDejaOuverte($client->id, TypeAlerte::FractionnementMultiAgences)) {
            return;
        }

        Alerte::create([
            'type' => TypeAlerte::FractionnementMultiAgences,
            'client_id' => $client->id,
            'regle_detection_id' => $regle->id,
            'gravite' => GraviteAlerte::Critique,
            'explication_texte' => $this->explication->pourFractionnementMultiAgences(
                $operations->count(),
                $this->formaterMontant($cumul, $operation->devise_code),
                $nbAgences,
                $this->formaterMontant((float) $params['seuil'], $operation->devise_code),
                (int) $params['fenetre_jours'],
            ),
            'faits' => [
                'nb_operations' => $operations->count(),
                'montant_cumule' => $cumul,
                'nb_agences' => $nbAgences,
                'seuil' => $params['seuil'],
                'clients_rapproches' => $groupeClientIds->count(),
                'operations_ids' => $operations->pluck('id')->all(),
            ],
            'statut' => StatutAlerte::Nouvelle,
        ]);

        Consignateur::enregistrer('systeme', null, 'detection_fractionnement_multi_agences', 'client', $client->id);
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
        if ($this->alerteDejaOuverte($client->id, TypeAlerte::CompteDormantReactive)) {
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

    private function verifierSeuilCentif(Operation $operation): void
    {
        $regle = $this->regleActive('SEUIL_MENSUEL_CENTIF');
        if ($regle === null) {
            return;
        }
        $seuil = (float) $regle->parametres['seuil'];
        $client = $operation->compte->client;
        $periode = $operation->effectuee_le->format('Y-m');

        $cumul = (float) Operation::whereHas('compte', fn ($q) => $q->where('client_id', $client->id))
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

    private function alerteDejaOuverte(string $clientId, TypeAlerte $type): bool
    {
        return Alerte::where('client_id', $clientId)
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
        $entier = number_format($montant, 0, ',', ' ');

        return $entier.' '.$devise;
    }
}
