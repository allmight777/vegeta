<?php

namespace App\Services\Securite;

class Chiffrement
{
    private const VERSION = 'v1';

    private const CHIFFRE = 'aes-256-gcm';

    public function __construct(private readonly GestionnaireCles $cles) {}

    public function chiffrer(string $clair): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $chiffre = openssl_encrypt($clair, self::CHIFFRE, $this->cles->cle(), OPENSSL_RAW_DATA, $iv, $tag);

        return self::VERSION.':'.base64_encode($iv.$tag.$chiffre);
    }

    public function dechiffrer(string $valeur): ?string
    {
        $parties = explode(':', $valeur, 2);

        if (count($parties) !== 2 || $parties[0] !== self::VERSION) {
            return null;
        }

        $brut = base64_decode($parties[1], true);

        if ($brut === false || strlen($brut) < 28) {
            return null;
        }

        $iv = substr($brut, 0, 12);
        $tag = substr($brut, 12, 16);
        $chiffre = substr($brut, 28);

        $clair = openssl_decrypt($chiffre, self::CHIFFRE, $this->cles->cle(), OPENSSL_RAW_DATA, $iv, $tag);

        return $clair === false ? null : $clair;
    }
}
