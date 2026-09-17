<?php

namespace App\Services\Securite;

use Illuminate\Support\Str;

class IndexAveugle
{
    public function __construct(private readonly GestionnaireCles $cles) {}

    public function calculer(string $valeur, string $type = 'defaut'): string
    {
        return hash_hmac('sha256', $type.'|'.$this->normaliser($valeur, $type), $this->cles->cle('index_aveugle'));
    }

    private function normaliser(string $valeur, string $type): string
    {
        $majuscule = Str::of($valeur)->ascii()->upper()->toString();

        return match ($type) {
            'telephone', 'npi', 'numero_piece', 'rccm', 'ifu', 'numero_compte' => preg_replace('/[^0-9A-Z]/', '', $majuscule) ?? '',
            default => trim(preg_replace('/\s+/', ' ', $majuscule) ?? ''),
        };
    }
}
