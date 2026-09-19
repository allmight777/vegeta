<?php

namespace App\Http\Requests\Agent\Filtrage;

use App\Enums\MotifDecisionFiltrage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeciderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Le motif codé est obligatoire dans les deux sens : écarter sans justification
     * laisse un trou dans la piste d'audit, et confirmer sans motif prive le dossier
     * de sa base juridique. Le texte libre ne sert qu'à préciser.
     */
    public function rules(): array
    {
        return [
            'statut' => ['required', 'in:ecarte,confirme'],
            'motif_code' => ['required', Rule::enum(MotifDecisionFiltrage::class)],
            'motif' => ['nullable', 'string', 'max:500'],
            'motif_detail_requis' => [],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->sometimes('motif', ['required', 'string', 'min:10'], function ($donnees) {
            return ($donnees->motif_code ?? null) === MotifDecisionFiltrage::Autre->value;
        });
    }

    public function messages(): array
    {
        return [
            'motif_code.required' => 'Choisissez le motif de votre décision.',
            'motif.required' => 'Précisez le motif lorsque vous choisissez « Autre ».',
        ];
    }
}
