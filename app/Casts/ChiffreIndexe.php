<?php

namespace App\Casts;

use App\Services\Securite\Chiffrement;
use App\Services\Securite\IndexAveugle;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Chiffre une colonne d'identité et maintient en parallèle sa colonne d'index aveugle
 * (recherche exacte uniquement, jamais de LIKE sur une colonne chiffrée).
 *
 * Usage : #[Cast(ChiffreIndexe::class.':email_idx,email')] ou via casts() avec arguments
 * "colonne_index,type_normalisation" (type_normalisation optionnel, "defaut" sinon).
 *
 * @implements CastsAttributes<?string, ?string>
 */
class ChiffreIndexe implements CastsAttributes
{
    public function __construct(
        private readonly string $colonneIndex,
        private readonly string $typeIndex = 'defaut',
    ) {}

    public function get($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return app(Chiffrement::class)->dechiffrer($value);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null, $this->colonneIndex => null];
        }

        $valeur = (string) $value;

        return [
            $key => app(Chiffrement::class)->chiffrer($valeur),
            $this->colonneIndex => app(IndexAveugle::class)->calculer($valeur, $this->typeIndex),
        ];
    }
}
