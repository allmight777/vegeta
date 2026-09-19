<?php

namespace App\Http\Requests\Controleur;

use App\Enums\AvisTechniqueSoupcon;
use App\Enums\CanalSoupcon;
use App\Enums\IndicateurSoupcon;
use App\Enums\NiveauRisqueSoupcon;
use App\Enums\TypeClientSoupcon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Fiche d'analyse de soupçon (18_PROMPT §3, sections 1 à 4). Le brouillon accepte une fiche
 * incomplète ; la transmission exige le résumé des faits, l'analyse, un avis technique et au
 * moins un indicateur. Nom, signature et date du contrôleur ne sont jamais saisis : ils sont
 * remplis par le serveur.
 */
class EnregistrerDossierSoupconRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'dates_operations' => $this->liste($this->input('dates_operations')),
            'montants_concernes' => $this->liste($this->input('montants_concernes')),
            'indicateurs' => array_values((array) $this->input('indicateurs', [])),
        ]);
    }

    public function rules(): array
    {
        $transmission = $this->routeIs('controleur.soupcons.transmettre');
        $obligatoire = $transmission ? 'required' : 'nullable';

        return [
            'type_client' => ['required', Rule::enum(TypeClientSoupcon::class)],
            'niveau_risque' => ['required', Rule::enum(NiveauRisqueSoupcon::class)],
            'dates_operations' => ['nullable', 'array', 'max:100'],
            'dates_operations.*' => ['date_format:Y-m-d'],
            'montants_concernes' => ['nullable', 'array', 'max:100'],
            'montants_concernes.*' => ['numeric', 'min:0'],
            'canal' => ['nullable', Rule::enum(CanalSoupcon::class)],
            'resume_faits' => [$obligatoire, 'nullable', 'string', 'max:5000'],
            'indicateurs' => [$transmission ? 'required' : 'nullable', 'array'],
            'indicateurs.*' => [Rule::enum(IndicateurSoupcon::class)],
            'indicateur_autre_texte' => [Rule::requiredIf(fn () => in_array(IndicateurSoupcon::Autres->value, $this->input('indicateurs', []), true)), 'nullable', 'string', 'max:255'],
            'analyse_controleur' => [$obligatoire, 'nullable', 'string', 'max:8000'],
            'avis_technique_controleur' => [$obligatoire, 'nullable', Rule::enum(AvisTechniqueSoupcon::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'resume_faits.required' => 'Le résumé des faits est obligatoire pour transmettre le dossier.',
            'analyse_controleur.required' => "L'analyse et les résultats des investigations sont obligatoires pour transmettre le dossier.",
            'avis_technique_controleur.required' => 'Choisissez un avis technique avant de transmettre le dossier.',
            'indicateurs.required' => "Cochez au moins un indicateur de soupçon avant de transmettre le dossier.",
            'indicateur_autre_texte.required' => "Précisez l'indicateur « Autres ».",
            'dates_operations.*.date_format' => 'Les dates doivent être au format AAAA-MM-JJ, séparées par des virgules.',
            'montants_concernes.*.numeric' => 'Les montants doivent être des nombres, séparés par des virgules.',
        ];
    }

    /** @return array<int, string> */
    private function liste(mixed $valeur): array
    {
        if (is_array($valeur)) {
            return array_values(array_filter(array_map('trim', $valeur), fn ($v) => $v !== ''));
        }

        return array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', (string) $valeur) ?: []), fn ($v) => $v !== ''));
    }
}
