<?php

namespace App\Http\Requests\Agent\Clients;

use App\Services\Kyc\ReferentielFicheAdhesion;
use Illuminate\Foundation\Http\FormRequest;

class CompleterClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $client = $this->route('client');
        $type = $client?->type?->value ?? 'personne_physique';
        $referentiel = app(ReferentielFicheAdhesion::class);

        $regles = $referentiel->reglesValidation($type);

        if ($this->user('agent')?->estResponsableAgence()) {
            $groupeRlbcft = $referentiel->groupeFicheRlbcft($type);

            if ($type === 'personne_morale') {
                foreach ($client?->personneMorale?->signataires ?? [] as $signataire) {
                    foreach ($groupeRlbcft['champs'] ?? [] as $code => $definition) {
                        $regles["fiche_rlbcft.{$signataire->id}.{$code}"] = $referentiel->reglesPourChamp($definition);
                    }
                }
            } else {
                foreach ($groupeRlbcft['champs'] ?? [] as $code => $definition) {
                    $regles["fiche_rlbcft.{$code}"] = $referentiel->reglesPourChamp($definition);
                }
            }
        }

        return $regles;
    }
}
