<?php

namespace App\Services\Securite;

use RuntimeException;

/**
 * Simplification assumée pour ce MVP resserré (voir docs/DECISIONS.md) : une seule clé
 * de base pour toute l'application (CLE_CIF_DEMO), jamais APP_KEY, dont on dérive une
 * sous-clé par usage (HMAC) plutôt que la clé par réseau + rotation du socle complet.
 */
class GestionnaireCles
{
    public function cle(string $usage = 'chiffrement_donnees'): string
    {
        $secret = $this->secretBase();

        return hash_hmac('sha256', 'usage:'.$usage, $secret, true);
    }

    private function secretBase(): string
    {
        $base64 = config('cles.demo');

        if (blank($base64)) {
            throw new RuntimeException(
                'Clé de chiffrement CLE_CIF_DEMO manquante dans .env. '
                .'Corriger en une commande : echo "CLE_CIF_DEMO=$(openssl rand -base64 32)" >> .env '
                .'puis php artisan config:clear.'
            );
        }

        $secret = base64_decode($base64, true);

        if ($secret === false || strlen($secret) < 32) {
            throw new RuntimeException('CLE_CIF_DEMO invalide : 32 octets aléatoires en base64 attendus.');
        }

        return $secret;
    }
}
