<?php

namespace App\Http\Requests\Admin\Import;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fichier' => ['required', 'string'],
            'agence_id' => ['required', 'exists:agences,id'],
            'mapping' => ['required', 'array'],
            'mapping.*' => ['nullable', 'string'],
        ];
    }
}
