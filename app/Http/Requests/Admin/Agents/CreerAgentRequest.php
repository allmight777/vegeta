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
            'role' => ['required', 'in:caissier,responsable_agence,controleur_permanent'],
            'civilite' => ['nullable', 'in:m,f,non_precise'],
            // Contrôleur permanent : un réseau, pas d'agence (18_PROMPT §2). Autres rôles : une agence.
            'agence_id' => ['required_unless:role,controleur_permanent', 'nullable', 'exists:agences,id'],
            'reseau_id' => ['required_if:role,controleur_permanent', 'nullable', 'exists:reseaux,id'],
        ];
    }
}
