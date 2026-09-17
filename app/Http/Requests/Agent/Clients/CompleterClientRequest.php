<?php

namespace App\Http\Requests\Agent\Clients;

use Illuminate\Foundation\Http\FormRequest;

class CompleterClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['nullable', 'string', 'max:255'],
            'prenoms' => ['nullable', 'string', 'max:255'],
            'date_naissance' => ['nullable', 'date'],
            'lieu_naissance' => ['nullable', 'string', 'max:255'],
            'piece_identite_numero' => ['nullable', 'string', 'max:255'],
            'piece_identite_expiration' => ['nullable', 'date'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'profession' => ['nullable', 'string', 'max:255'],
            'revenus_mensuels_estimes' => ['nullable', 'numeric', 'min:0'],

            'raison_sociale' => ['nullable', 'string', 'max:255'],
            'forme_juridique' => ['nullable', 'string', 'max:255'],
            'rccm' => ['nullable', 'string', 'max:255'],
            'ifu' => ['nullable', 'string', 'max:255'],
        ];
    }
}
