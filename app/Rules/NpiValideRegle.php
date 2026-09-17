<?php

namespace App\Rules;

use App\Enums\StatutVerificationNpi;
use App\Services\Kyc\ResolveurStatutNpi;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Bloque la création/mise à jour uniquement si le format est manifestement invalide, ou
 * si une vérification en ligne réelle a échoué. Une absence de connexion ne bloque
 * jamais (07_PROMPT_MODE_DEGRADE_NPI_OCR §1, §2) — le NPI passe en attente de
 * rattrapage, géré par les services de création, pas par cette règle. Le NPI n'est
 * jamais journalisé en clair : cette règle ne fait qu'appeler le résolveur, sans écrire
 * dans journal_audit.
 */
class NpiValideRegle implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        $statut = app(ResolveurStatutNpi::class)->resoudre((string) $value)['statut'];

        if ($statut === StatutVerificationNpi::FormatInvalide) {
            $fail('Le format du NPI saisi est manifestement invalide.');
        } elseif ($statut === StatutVerificationNpi::VerifieInvalide) {
            $fail('Le NPI saisi n\'a pas pu être validé auprès du système officiel.');
        }
    }
}
