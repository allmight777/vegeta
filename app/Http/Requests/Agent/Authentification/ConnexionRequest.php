<?php

namespace App\Http\Requests\Agent\Authentification;

use Illuminate\Foundation\Http\FormRequest;

class ConnexionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricule' => ['required', 'string'],
            'mot_de_passe' => ['required', 'string'],
        ];
    }
}
