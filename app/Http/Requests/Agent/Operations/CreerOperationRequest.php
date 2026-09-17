<?php

namespace App\Http\Requests\Agent\Operations;

use Illuminate\Foundation\Http\FormRequest;

class CreerOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'compte_id' => ['required', 'exists:comptes,id'],
            'type' => ['required', 'in:depot,retrait'],
            'montant' => ['required', 'numeric', 'min:1'],
            'effectuee_le' => ['nullable', 'date'],
        ];
    }
}
