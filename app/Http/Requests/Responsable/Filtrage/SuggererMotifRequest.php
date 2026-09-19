<?php

namespace App\Http\Requests\Responsable\Filtrage;

use Illuminate\Foundation\Http\FormRequest;

class SuggererMotifRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'texte' => ['required', 'string', 'max:500'],
        ];
    }
}
