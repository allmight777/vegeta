<?php

namespace App\Services\Conformite;

use App\Enums\IndicateurSoupcon;
use App\Models\Agent;
use App\Models\DossierAnalyseSoupcon;
use App\Models\SuggestionSoupcon;
use App\Services\Assistance\ConstructeurContexteIa;
use App\Services\Assistance\GestionnaireAssistant;
use App\Services\Assistance\ProviderIaApiExterne;
use App\Services\Assistance\SelecteurProviderIa;
use App\Services\Explication\GenerateurExplication;
use Throwable;

/**
 * Boutons d'aide de l'espace contrôleur (« Aide à l'analyse ») et de l'espace responsable
 * (« Résumer ce dossier ») — 18_PROMPT §5.3 et §6.3.
 *
 * Règle absolue : au fournisseur IA externe, UNIQUEMENT un texte construit à partir de
 * caractéristiques dérivées / de champs structurés (via le contexte fermé de
 * ConstructeurContexteIa). Jamais un nom, un NPI, une adresse, le résumé des faits ni l'analyse
 * rédigés par le contrôleur. Sans fournisseur (mode simulateur, hors connexion, échec), le texte
 * local est renvoyé tel quel : c'est le mode de la démonstration.
 */
class AssistantSoupcon
{
    public function __construct(
        private readonly PreparateurDossierSoupcon $preparateur,
        private readonly GenerateurExplication $explication,
        private readonly SelecteurProviderIa $selecteur,
        private readonly ConstructeurContexteIa $contexte,
    ) {}

    /** @return array{texte: string, source: string} */
    public function expliquerPourControleur(SuggestionSoupcon $suggestion, Agent $agent): array
    {
        $local = $this->explication->pourSuggestionSoupcon($this->preparateur->caracteristiquesDerivees($suggestion));

        return $this->reformulerSiPossible(
            $local,
            $agent,
            'soupcon.aide_analyse',
            "Explique en langage clair, en trois phrases, pourquoi le système a suspecté ce profil, à partir de l'explication fournie. Ne fais aucune supposition supplémentaire et ne conclus pas à une infraction.",
        );
    }

    /** @return array{texte: string, source: string} */
    public function resumerPourResponsable(DossierAnalyseSoupcon $dossier, Agent $agent): array
    {
        $dossier->loadMissing('suggestion');
        $derivees = $this->preparateur->caracteristiquesDerivees($dossier->suggestion);

        $local = $this->explication->pourResumeDossierSoupcon([
            'reference' => $dossier->reference(),
            'type_client' => $dossier->type_client->libelle(),
            'niveau_risque' => $dossier->niveau_risque->libelle(),
            'indicateurs' => collect($dossier->indicateurs ?? [])
                ->map(fn ($cle) => IndicateurSoupcon::tryFrom((string) $cle)?->libelle())->filter()->values()->all(),
            'canal' => $dossier->canal?->libelle(),
            'nombre_operations' => count($dossier->montants_concernes ?? []),
            'montant_total' => (float) array_sum($dossier->montants_concernes ?? []),
            'avis_controleur' => $dossier->avis_technique_controleur?->libelle(),
        ]);

        // Les caractéristiques dérivées ne sont pas envoyées ici : le texte structuré suffit.
        unset($derivees);

        return $this->reformulerSiPossible(
            $local,
            $agent,
            'soupcon.resume_dossier',
            'Synthétise en trois ou quatre phrases le texte fourni, sans rien ajouter ni retirer.',
        );
    }

    /**
     * @return array{texte: string, source: string}
     */
    private function reformulerSiPossible(string $texteLocal, Agent $agent, string $ecran, string $consigne): array
    {
        try {
            $provider = $this->selecteur->choisir();

            if (! $provider instanceof ProviderIaApiExterne) {
                return ['texte' => $texteLocal, 'source' => 'controle_local'];
            }

            // Contexte fermé : le texte structuré passe par la clé `explication_alerte` déjà autorisée.
            $contexte = $this->contexte->construire($agent, $ecran, ['explication_alerte' => $texteLocal]);
            $reponse = trim($provider->repondre($consigne, $contexte, [], $agent));

            if ($reponse !== '' && $reponse !== GestionnaireAssistant::AUCUNE_REPONSE) {
                return ['texte' => $reponse, 'source' => 'analyse_assistee'];
            }
        } catch (Throwable) {
            // Repli silencieux : jamais d'erreur visible pour l'utilisateur.
        }

        return ['texte' => $texteLocal, 'source' => 'controle_local'];
    }
}
