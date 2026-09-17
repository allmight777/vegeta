<?php

namespace App\Http\Requests\Admin\Assistance;

use Illuminate\Foundation\Http\FormRequest;

class PoserQuestionRequest extends FormRequest
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
        ];
    }
}
