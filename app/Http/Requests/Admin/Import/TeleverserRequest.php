<?php

namespace App\Http\Requests\Admin\Import;

use Illuminate\Foundation\Http\FormRequest;

class TeleverserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fichier' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'agence_id' => ['required', 'exists:agences,id'],
        ];
    }
}
