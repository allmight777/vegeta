<?php

namespace App\Http\Requests\Admin\Agents;

use Illuminate\Foundation\Http\FormRequest;

class CreerAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'matricule' => ['required', 'string', 'max:50'],
            'role' => ['required', 'in:caissier,responsable_agence'],
            'civilite' => ['nullable', 'in:m,f,non_precise'],
            'agence_id' => ['required', 'exists:agences,id'],
        ];
    }
}
