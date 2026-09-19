<?php

namespace App\Http\Requests\Responsable\Comptes;

use Illuminate\Foundation\Http\FormRequest;

class GelerCompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Un gel conservatoire sans motif écrit ne serait pas une décision humaine tracée
     * (Loi art. 89 à 91) mais une simple case cochée — le motif est donc obligatoire.
     */
    public function rules(): array
    {
        return [
            'motif' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'motif.required' => 'Indiquez le motif du gel — obligatoire pour la traçabilité réglementaire.',
            'motif.min' => 'Le motif doit être un minimum explicite (10 caractères au moins).',
        ];
    }
}
