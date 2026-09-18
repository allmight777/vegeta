<?php

namespace App\Http\Requests\Agent\Operations;

use Illuminate\Foundation\Http\FormRequest;

class CreerOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'compte_id' => ['required', 'exists:comptes,id'],
            'type' => ['required', 'in:depot,retrait'],
            'montant' => ['required', 'numeric', 'min:1'],
            // Les règles de cumul et de seuil ne visent que les espèces : sans ce
            // champ, un virement de salaire déclencherait une alerte (Loi art. 17 i).
            'mode_paiement' => ['required', 'in:especes,virement,mobile_money'],
            'effectuee_le' => ['nullable', 'date'],
        ];
    }
}
