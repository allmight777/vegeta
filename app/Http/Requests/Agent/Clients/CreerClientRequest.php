<?php

namespace App\Http\Requests\Agent\Clients;

use App\Services\Kyc\ReferentielFicheAdhesion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Règles générées depuis config/champs_fiche_adhesion.php (ReferentielFicheAdhesion),
 * conditionnées par le type de client soumis. Garde le pattern existant du dépôt (un
 * seul FormRequest par action, branché sur `type`) plutôt que deux classes dédiées par
 * type — cf. docs/DECISIONS.md.
 */
class CreerClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'documents_ppe.required' => 'Joignez au moins une pièce justificative pour une personne politiquement exposée.',
            'documents_ppe.min' => 'Joignez au moins une pièce justificative pour une personne politiquement exposée.',
        ];
    }

    public function rules(): array
    {
        $type = $this->input('type');
        $referentiel = app(ReferentielFicheAdhesion::class);

        $regles = [
            'type' => ['required', 'in:personne_physique,personne_morale'],
            'nature_relation' => ['required', 'in:titulaire_compte,occasionnel'],
            'ppe_declare' => ['nullable', 'boolean'],
            // Loi art. 29 : une PPE déclarée doit être justifiée par au moins une pièce.
            'documents_ppe' => ['exclude_unless:ppe_declare,1', 'required', 'array', 'min:1', 'max:5'],
            'documents_ppe.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];

        if ($type === 'personne_physique') {
            $regles += $referentiel->reglesValidation('personne_physique');
            $regles['mandataires'] = ['nullable', 'array', 'max:3'];
            foreach ($referentiel->groupeRepetable('personne_physique', 'mandataires')['champs'] ?? [] as $code => $definition) {
                $regles["mandataires.*.{$code}"] = ['nullable', ...array_slice($referentiel->reglesPourChamp($definition), 1)];
            }
        } elseif ($type === 'personne_morale') {
            $regles += $referentiel->reglesValidation('personne_morale');
            $regles['signataires'] = ['nullable', 'array', 'max:3'];
            foreach ($referentiel->groupeRepetable('personne_morale', 'signataires')['champs'] ?? [] as $code => $definition) {
                $regles["signataires.*.{$code}"] = ['nullable', ...array_slice($referentiel->reglesPourChamp($definition), 1)];
            }
        }

        if ($this->user('agent')?->estResponsableAgence()) {
            $groupeRlbcft = $referentiel->groupeFicheRlbcft($type ?? 'personne_physique');
            foreach ($groupeRlbcft['champs'] ?? [] as $code => $definition) {
                $regles["fiche_rlbcft.{$code}"] = $referentiel->reglesPourChamp($definition);
            }
        }

        return $regles;
    }
}
