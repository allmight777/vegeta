<?php

namespace App\Services\Kyc;

use App\Rules\NpiValideRegle;

/**
 * Point d'accès unique à config/champs_fiche_adhesion.php (référentiel des fiches
 * d'adhésion CIF). Le formulaire, sa validation, le calcul de complétude et le mappeur
 * d'extraction de documents passent tous par cette classe — jamais un config() direct
 * ailleurs (06_PROMPT_FORMULAIRE_CLIENT_ENRICHI §2, CLAUDE.md §10).
 */
class ReferentielFicheAdhesion
{
    /**
     * @return array<string, array{libelle: string, champs: array, repetable?: bool, min?: int, max?: int, visible_role?: string, par_signataire?: bool}>
     */
    public function groupes(string $type): array
    {
        return config("champs_fiche_adhesion.{$type}.groupes", []);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function groupe(string $type, string $code): ?array
    {
        return $this->groupes($type)[$code] ?? null;
    }

    public function champ(string $type, string $code): ?array
    {
        foreach ($this->groupes($type) as $groupe) {
            if (($groupe['repetable'] ?? false) === false && isset($groupe['champs'][$code])) {
                return $groupe['champs'][$code];
            }
        }

        return null;
    }

    /**
     * Champs des groupes non répétables et non RLBC/FT, aplatis — utilisé par la
     * validation et par CalculateurCompletude. Les groupes répétables (mandataires,
     * signataires) et fiche_rlbcft sont exclus : ils ont leurs propres règles.
     *
     * @return array<string, array<string, mixed>>
     */
    public function champsPlats(string $type): array
    {
        $champs = [];

        foreach ($this->groupes($type) as $code => $groupe) {
            if (($groupe['repetable'] ?? false) || $code === 'fiche_rlbcft') {
                continue;
            }

            foreach ($groupe['champs'] as $champCode => $definition) {
                $champs[$champCode] = $definition;
            }
        }

        return $champs;
    }

    /**
     * Champs synthétiques de complétude (jamais des champs de saisie), ex.
     * "au_moins_un_signataire" — gérés par des cas spéciaux dans CalculateurCompletude.
     *
     * @return array<string, array<string, mixed>>
     */
    public function champsCalcules(string $type): array
    {
        return config("champs_fiche_adhesion.champs_calcules.{$type}", []);
    }

    public function groupeRepetable(string $type, string $code): ?array
    {
        $groupe = $this->groupe($type, $code);

        return ($groupe['repetable'] ?? false) ? $groupe : null;
    }

    public function groupeFicheRlbcft(string $type): ?array
    {
        return $this->groupe($type, 'fiche_rlbcft');
    }

    public function seuilBeneficiaireEffectifPourcentage(): float
    {
        return (float) config('champs_fiche_adhesion.seuil_beneficiaire_effectif_pourcentage', 25);
    }

    /**
     * Règles Laravel générées depuis le référentiel pour les champs non répétables —
     * utilisées par CreerClientRequest/CompleterClientRequest, jamais dupliquées à la
     * main (CLAUDE.md §10).
     *
     * @return array<string, array<int, mixed>>
     */
    public function reglesValidation(string $type): array
    {
        $regles = [];

        foreach ($this->champsPlats($type) as $code => $definition) {
            $regles[$code] = $this->reglesPourChamp($definition);
        }

        return $regles;
    }

    /**
     * @return array<int, mixed>
     */
    public function reglesPourChamp(array $definition): array
    {
        $presence = ($definition['obligatoire'] ?? false) ? 'required' : 'nullable';

        return match ($definition['type_saisie'] ?? 'text') {
            'date' => [$presence, 'date'],
            'number' => [$presence, 'numeric'],
            'checkbox' => ['nullable', 'boolean'],
            'file' => ['nullable', 'file', 'max:5120'],
            'select' => [$presence, 'in:'.implode(',', array_keys($definition['options'] ?? []))],
            'npi' => [$presence, 'string', 'max:20', new NpiValideRegle],
            'email' => [$presence, 'email', 'max:255'],
            default => [$presence, 'string', 'max:255'],
        };
    }
}
