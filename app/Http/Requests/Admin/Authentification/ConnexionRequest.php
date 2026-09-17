<?php

namespace App\Http\Requests\Admin\Authentification;

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
            'email' => ['required', 'string', 'email'],
            'mot_de_passe' => ['required', 'string'],
        ];
    }
}
