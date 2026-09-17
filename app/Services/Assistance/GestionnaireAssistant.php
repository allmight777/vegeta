<?php

namespace App\Services\Assistance;

use App\Models\Admin;
use App\Models\Agent;
use App\Services\Audit\Consignateur;
use App\Support\MotsInterditsConformite;
use Throwable;

/**
 * Orchestre une question posée à l'assistant (08_PROMPT §3.3, §7.2) : contexte fermé →
 * choix du fournisseur → appel (avec repli automatique sur le simulateur en cas
 * d'échec) → filtre de sortie → journalisation. Point d'entrée unique partagé par les
 * contrôleurs agent et admin.
 */
class GestionnaireAssistant
{
    public const AUCUNE_REPONSE = 'JE_NE_SAIS_PAS';

    public function __construct(
        private readonly ConstructeurContexteIa $constructeurContexte,
        private readonly SelecteurProviderIa $selecteur,
        private readonly FiltreConformiteReponseIa $filtre,
    ) {}

    /**
     * @param  array<string, mixed>  $donneesEcran
     * @return array{reponse: string, source: string, peut_escalader: bool}
     */
    public function traiter(Agent|Admin $utilisateur, string $question, string $ecranActuel, array $donneesEcran = []): array
    {
        $contexte = $this->constructeurContexte->construire($utilisateur, $ecranActuel, $donneesEcran);
        $provider = $this->selecteur->choisir();
        $source = $provider instanceof ProviderIaApiExterne ? 'ia_externe' : 'simulateur';

        try {
            $reponseBrute = $provider->repondre($question, $contexte);
        } catch (Throwable) {
            $reponseBrute = app(ProviderIaSimulateur::class)->repondre($question, $contexte);
            $source = 'simulateur_repli';
        }

        $aucuneReponse = $reponseBrute === self::AUCUNE_REPONSE;
        $reponseFiltree = $aucuneReponse
            ? 'Je n\'ai pas d\'information là-dessus dans ma base de connaissances.'
            : $this->filtre->filtrer($reponseBrute, $utilisateur);

        $filtree = ! $aucuneReponse && $reponseFiltree === FiltreConformiteReponseIa::MESSAGE_NEUTRE;

        $this->journaliser($utilisateur, $question);

        return [
            'reponse' => $reponseFiltree,
            'source' => $source,
            'peut_escalader' => $aucuneReponse || $filtree,
        ];
    }

    private function journaliser(Agent|Admin $utilisateur, string $question): void
    {
        $acteurType = $utilisateur instanceof Agent ? 'agent' : 'admin';
        $action = MotsInterditsConformite::contient($question) !== null
            ? 'consultation_assistant_ia_filtree'
            : 'consultation_assistant_ia';

        Consignateur::enregistrer($acteurType, $utilisateur->id, $action);
    }
}
