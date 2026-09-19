<?php

namespace App\Support;

/**
 * Vecteur de bits à taille fixe, stocké en octets bruts (filtre de Bloom des empreintes).
 */
class Bitset
{
    private function __construct(
        private string $octets,
        private readonly int $tailleBits,
    ) {}

    public static function vide(int $tailleBits): self
    {
        return new self(str_repeat("\0", (int) ceil($tailleBits / 8)), $tailleBits);
    }

    public static function depuisOctets(string $octets, int $tailleBits): self
    {
        return new self($octets, $tailleBits);
    }

    public static function concatener(self $a, self $b): self
    {
        $resultat = self::vide($a->tailleBits + $b->tailleBits);

        foreach ($a->positionsActives() as $position) {
            $resultat->activer($position);
        }

        foreach ($b->positionsActives() as $position) {
            $resultat->activer($a->tailleBits + $position);
        }

        return $resultat;
    }

    public function activer(int $position): void
    {
        $octetIndex = intdiv($position, 8);
        $bitIndex = $position % 8;
        $this->octets[$octetIndex] = chr(ord($this->octets[$octetIndex]) | (1 << $bitIndex));
    }

    public function estActif(int $position): bool
    {
        $octetIndex = intdiv($position, 8);
        $bitIndex = $position % 8;

        return (ord($this->octets[$octetIndex]) & (1 << $bitIndex)) !== 0;
    }

    /**
     * ET bit à bit. L'opérateur & de PHP travaille nativement sur les chaînes binaires
     * et tronque au plus court : une seule opération au lieu d'une boucle ord()/chr()
     * par octet. Sur un refiltrage de parc, cette méthode est appelée des millions de
     * fois — c'est le chemin le plus chaud de toute l'application.
     */
    public function et(self $autre): self
    {
        $longueur = min(strlen($this->octets), strlen($autre->octets));

        return new self(
            substr($this->octets, 0, $longueur) & substr($autre->octets, 0, $longueur),
            min($this->tailleBits, $autre->tailleBits),
        );
    }

    /**
     * Table de popcount construite une seule fois (256 entrées) : compter les bits
     * d'un octet devient une lecture de tableau, au lieu d'un decbin() suivi d'un
     * substr_count() qui alloue une chaîne par octet.
     *
     * @var array<int, int>|null
     */
    private static ?array $popcount = null;

    public function nbBitsActifs(): int
    {
        $table = self::$popcount ??= self::construireTablePopcount();
        $total = 0;

        foreach (unpack('C*', $this->octets) ?: [] as $octet) {
            $total += $table[$octet];
        }

        return $total;
    }

    /**
     * @return array<int, int>
     */
    private static function construireTablePopcount(): array
    {
        $table = [];

        for ($octet = 0; $octet < 256; $octet++) {
            $table[$octet] = substr_count(decbin($octet), '1');
        }

        return $table;
    }

    /**
     * @return array<int>
     */
    public function positionsActives(): array
    {
        $positions = [];
        $index = 0;

        foreach (unpack('C*', $this->octets) ?: [] as $octet) {
            if ($octet !== 0) {
                for ($bit = 0; $bit < 8; $bit++) {
                    $position = $index + $bit;

                    if ($position >= $this->tailleBits) {
                        break;
                    }

                    if (($octet & (1 << $bit)) !== 0) {
                        $positions[] = $position;
                    }
                }
            }

            $index += 8;
        }

        return $positions;
    }

    public function versOctets(): string
    {
        return $this->octets;
    }

    public function tailleBits(): int
    {
        return $this->tailleBits;
    }
}
