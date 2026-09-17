<?php

namespace App\Http\Requests\Agent\Clients;

use Illuminate\Foundation\Http\FormRequest;

class MandataireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['nullable', 'string', 'max:255'],
            'lien_parente' => ['nullable', 'string', 'max:255'],
        ];
    }
}
