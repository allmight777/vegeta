<?php

namespace App\Http\Requests\Agent\Clients;

use Illuminate\Foundation\Http\FormRequest;

class SignataireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:signataire,beneficiaire_effectif,mandataire'],
            'pourcentage_detention' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
