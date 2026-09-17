<?php

namespace App\Http\Requests\Agent\Clients;

use Illuminate\Foundation\Http\FormRequest;

class CreerClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:personne_physique,personne_morale'],
            'nature_relation' => ['required', 'in:titulaire_compte,occasionnel'],
            'nom' => ['required_if:type,personne_physique', 'nullable', 'string', 'max:255'],
            'prenoms' => ['required_if:type,personne_physique', 'nullable', 'string', 'max:255'],
            'raison_sociale' => ['required_if:type,personne_morale', 'nullable', 'string', 'max:255'],
        ];
    }
}
