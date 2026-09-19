<?php

namespace App\Services\Coherence;

use App\Enums\GraviteAlerte;

/**
 * Accumule les constats d'un client et décide s'ils forment un faisceau.
 *
 * Règle cardinale : UN INDICE ISOLÉ NE SIGNALE RIEN. Un agriculteur qui dépose
 * après la récolte, un commerçant qui encaisse du mobile money, un salarié
 * payé par virement : pris séparément, chacun de ces constats a une
 * explication banale. C'est leur concordance qui fait le soupçon.
 *
 * Alerter sur chaque indice pris isolément noierait la file du responsable
 * d'agence en quelques jours, et il cesserait de la lire — le résultat serait
 * moins de conformité, pas plus.
 */
class FaisceauIndices
{
    /** @var array<int, Constat> */
    private array $constats = [];

    public function ajouter(?Constat $constat): void
    {
        if ($constat !== null) {
            $this->constats[] = $constat;
        }
    }

    /**
     * @return array<int, Constat>
     */
    public function constats(): array
    {
        return $this->constats;
    }

    public function nombreConstats(): int
    {
        return count($this->constats);
    }

    public function poidsTotal(): int
    {
        return array_sum(array_map(fn (Constat $c) => $c->poids, $this->constats));
    }

    /**
     * Le faisceau est-il constitué ? Deux conditions cumulatives : assez de
     * constats distincts, et assez de poids. Trois constats faibles ne valent
     * pas un signalement ; un constat très fort non plus.
     */
    public function estConstitue(): bool
    {
        $config = config('coherence.faisceau');

        return $this->nombreConstats() >= (int) $config['constats_minimum']
            && $this->poidsTotal() >= (int) $config['poids_minimum'];
    }

    public function gravite(): GraviteAlerte
    {
        return $this->poidsTotal() >= (int) config('coherence.faisceau.poids_critique')
            ? GraviteAlerte::Critique
            : GraviteAlerte::Attention;
    }

    /**
     * Codes des indicateurs déclenchés, triés — sert d'empreinte du faisceau
     * pour que la mémoire des décisions reconnaisse un cas déjà tranché.
     */
    public function signature(): string
    {
        $codes = array_map(fn (Constat $c) => $c->code, $this->constats);
        sort($codes);

        return implode('+', $codes);
    }

    /**
     * @return array<string, mixed>
     */
    public function versFaits(): array
    {
        return [
            'nombre_constats' => $this->nombreConstats(),
            'poids_total' => $this->poidsTotal(),
            'signature' => $this->signature(),
            'constats' => array_map(fn (Constat $c) => $c->versTableau(), $this->constats),
        ];
    }
}
