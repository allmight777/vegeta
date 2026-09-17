<?php

namespace App\Http\Requests\Agent\Clients;

use Illuminate\Foundation\Http\FormRequest;

class ImporterDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documents' => ['required', 'array', 'max:'.config('extraction.nombre_max_fichiers', 10)],
            // jpg/jpeg/png : cas réel des fiches photographiées au téléphone
            // (07_PROMPT_MODE_DEGRADE_NPI_OCR §4.2).
            'documents.*' => ['file', 'max:'.config('extraction.taille_max_ko', 10240), 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ];
    }
}
