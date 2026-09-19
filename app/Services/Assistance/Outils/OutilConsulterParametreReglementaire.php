<?php

namespace App\Services\Assistance\Outils;

use App\Contracts\OutilAssistantIa;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\RegleDetection;
use App\Support\MotsCles;

/**
 * Retourne un seuil de détection avec sa source — réservé au responsable d'agence et à
 * l'administrateur (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §2.3). Jamais injecté dans
 * le jeu d'outils du caissier (OutilsParRole) — et, même si une autre voie exposait un
 * chiffre de seuil dans une réponse, FiltreConformiteReponseIa::masquerSeuils() le
 * masquerait quand même pour ce rôle (défense en profondeur, §5).
 */
class OutilConsulterParametreReglementaire implements OutilAssistantIa
{
    public function nom(): string
    {
        return 'consulter_parametre_reglementaire';
    }

    public function description(): string
    {
        return "Retourne la valeur configurée d'un seuil de détection (avec sa source réglementaire).";
    }

    public function schemaParametres(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'code' => ['type' => 'string', 'description' => 'Code de la règle de détection (ex. FRACTIONNEMENT_GUICHET, SEUIL_MENSUEL_CENTIF).'],
            ],
            'required' => ['code'],
        ];
    }

    public function motsCles(): array
    {
        return ['seuil', 'seuil exact', 'a partir de combien', 'montant declarable', 'parametre reglementaire'];
    }

    public function executer(array $arguments, Agent|Admin $utilisateur): array
    {
        $code = strtoupper((string) ($arguments['code'] ?? ''));

        $regle = $code !== ''
            ? RegleDetection::where('code', $code)->where('actif', true)->first()
            : $this->regleLaPlusProche((string) ($arguments['question'] ?? ''));

        if ($regle === null) {
            return ['trouve' => false];
        }

        return [
            'trouve' => true,
            'code' => $regle->code,
            'libelle' => $regle->libelle,
            'parametres' => $regle->parametres,
            'source' => $regle->source->value,
            'reference_texte' => $regle->reference_texte,
        ];
    }

    /**
     * Routage sans function-calling (simulateur) : aucun code de règle n'est fourni, seulement
     * la question. On retient la règle active dont le libellé/code recoupe le plus la question.
     */
    private function regleLaPlusProche(string $question): ?RegleDetection
    {
        $motsQuestion = MotsCles::extraire($question);

        if ($motsQuestion === []) {
            return null;
        }

        $meilleure = null;
        $meilleurScore = 0;

        foreach (RegleDetection::where('actif', true)->orderBy('id')->get() as $regle) {
            $motsRegle = MotsCles::extraire($regle->libelle.' '.str_replace('_', ' ', $regle->code));
            $score = count(array_intersect($motsQuestion, $motsRegle));

            if ($score > $meilleurScore) {
                $meilleurScore = $score;
                $meilleure = $regle;
            }
        }

        return $meilleure;
    }
}
