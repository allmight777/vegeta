<?php

namespace App\Http\Requests\Agent\Clients;

use App\Rules\NpiValideRegle;
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
            'date_naissance' => ['nullable', 'date'],
            'sexe' => ['nullable', 'in:m,f'],
            'lieu_naissance' => ['nullable', 'string', 'max:255'],
            'piece_identite_type' => ['nullable', 'in:cni,cip,carte_biometrique,passeport'],
            'piece_identite_numero' => ['nullable', 'string', 'max:255'],
            'piece_identite_expiration' => ['nullable', 'date'],
            'validation_methode' => ['nullable', 'in:numero,code_qr'],
            'nationalite' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'fonction' => ['nullable', 'string', 'max:255'],
            'npi' => ['nullable', 'string', 'max:20', new NpiValideRegle],
        ];
    }
}
