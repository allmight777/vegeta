<?php

namespace App\Services\Coherence;

/**
 * Un écart constaté entre le profil déclaré et le comportement observé.
 *
 * Immuable et entièrement chiffré : le `fait` doit permettre à un contrôleur
 * de refaire le calcul à la main. Un signalement LBC/FT non explicable est
 * inopposable — c'est pourquoi aucun constat ne sort d'un modèle statistique.
 */
class Constat
{
    /**
     * @param  string  $code  identifiant stable de l'indicateur
     * @param  string  $libelle  phrase courte affichée au responsable
     * @param  int  $poids  contribution au faisceau (plus fort = plus probant)
     * @param  array<string, mixed>  $fait  les chiffres qui fondent le constat
     */
    public function __construct(
        public readonly string $code,
        public readonly string $libelle,
        public readonly int $poids,
        public readonly array $fait = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function versTableau(): array
    {
        return [
            'code' => $this->code,
            'libelle' => $this->libelle,
            'poids' => $this->poids,
            'fait' => $this->fait,
        ];
    }
}
