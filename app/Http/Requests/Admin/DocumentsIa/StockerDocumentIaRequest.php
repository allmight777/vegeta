<?php

namespace App\Http\Requests\Admin\DocumentsIa;

use Illuminate\Foundation\Http\FormRequest;

class StockerDocumentIaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documents' => ['required', 'array', 'max:'.config('extraction.nombre_max_fichiers', 10)],
            'documents.*' => ['file', 'max:'.config('extraction.taille_max_ko', 10240), 'mimes:pdf,doc,docx,xlsx,xls,txt,md'],
            'portee' => ['required', 'in:reseau,agence,toutes_agences'],
            'reseau_id' => ['required_if:portee,reseau', 'nullable', 'exists:reseaux,id'],
            'agence_id' => ['required_if:portee,agence', 'nullable', 'exists:agences,id'],
            'visible_caissier' => ['nullable', 'boolean'],
            'visible_responsable_agence' => ['nullable', 'boolean'],
        ];
    }
}
