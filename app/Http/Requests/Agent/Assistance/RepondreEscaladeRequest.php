<?php

namespace App\Http\Requests\Agent\Assistance;

use Illuminate\Foundation\Http\FormRequest;

class RepondreEscaladeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reponse_responsable' => ['required', 'string', 'max:2000'],
        ];
    }
}
