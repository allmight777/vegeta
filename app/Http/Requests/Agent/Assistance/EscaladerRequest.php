<?php

namespace App\Http\Requests\Agent\Assistance;

use Illuminate\Foundation\Http\FormRequest;

class EscaladerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:2000'],
            'ecran' => ['required', 'string', 'max:100'],
            'reponse_ia' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
