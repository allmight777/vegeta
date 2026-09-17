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

    public function et(self $autre): self
    {
        $longueur = min(strlen($this->octets), strlen($autre->octets));
        $resultat = '';

        for ($i = 0; $i < $longueur; $i++) {
            $resultat .= chr(ord($this->octets[$i]) & ord($autre->octets[$i]));
        }

        return new self($resultat, min($this->tailleBits, $autre->tailleBits));
    }

    public function nbBitsActifs(): int
    {
        $total = 0;

        foreach (str_split($this->octets) as $octet) {
            $total += substr_count(decbin(ord($octet)), '1');
        }

        return $total;
    }

    /**
     * @return array<int>
     */
    public function positionsActives(): array
    {
        $positions = [];

        for ($i = 0; $i < $this->tailleBits; $i++) {
            if ($this->estActif($i)) {
                $positions[] = $i;
            }
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
