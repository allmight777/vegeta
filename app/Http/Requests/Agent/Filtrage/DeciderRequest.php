<?php

namespace App\Http\Requests\Agent\Filtrage;

use Illuminate\Foundation\Http\FormRequest;

class DeciderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut' => ['required', 'in:confirme,ecarte'],
            'motif' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
