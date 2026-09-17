<?php

namespace App\Casts;

use App\Services\Securite\Chiffrement;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @implements CastsAttributes<?string, ?string>
 */
class Chiffre implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return app(Chiffrement::class)->dechiffrer($value);
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return app(Chiffrement::class)->chiffrer((string) $value);
    }
}
