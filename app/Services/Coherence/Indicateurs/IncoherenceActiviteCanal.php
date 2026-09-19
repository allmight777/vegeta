<?php

namespace App\Services\Coherence\Indicateurs;

use App\Enums\ModePaiement;
use App\Models\Client;
use App\Services\Coherence\Constat;
use App\Services\Coherence\ContexteTransactionnel;
use Illuminate\Support\Str;

/**
 * Indicateur 3 — l'activité déclarée ne colle pas aux canaux réellement
 * utilisés.
 *
 * C'est ici que le champ `activite_1`, aujourd'hui saisi puis jamais exploité,
 * prend enfin une valeur opérationnelle. Un cultivateur dont la totalité des
 * flux arrive par virement, ou un fonctionnaire payé exclusivement en espèces,
 * décrivent une activité qui n'est pas celle qu'ils ont déclarée.
 *
 * La table de correspondance (config/coherence.php) est une hypothèse de
 * démonstration : chaque réseau connaît ses métiers mieux que nous, et
 * l'administrateur peut la corriger.
 */
class IncoherenceActiviteCanal implements Indicateur
{
    public function code(): string
    {
        return 'incoherence_activite_canal';
    }

    public function evaluer(Client $client, ContexteTransactionnel $contexte): ?Constat
    {
        $config = config('coherence.activite_canal');
        $activite = $contexte->activiteDeclaree;

        if ($activite === null) {
            return null;
        }

        $profil = $this->profilPour($activite, (array) $config['profils']);

        if ($profil === null) {
            return null;
        }

        $fenetre = (int) config('coherence.compte_de_passage.fenetre_jours');

        if ($contexte->nombreOperations($fenetre) < (int) $config['operations_minimum']) {
            return null;
        }

        $repartition = $contexte->repartitionModesPaiement($fenetre);
        $modeDominant = array_key_first($repartition);
        $part = $modeDominant === null ? 0.0 : $repartition[$modeDominant];

        if ($modeDominant === null || $part < (float) $config['part_dominante']) {
            return null;
        }

        if (in_array($modeDominant, $profil['attendus'], true)) {
            return null;
        }

        return new Constat(
            code: $this->code(),
            libelle: sprintf(
                'Activité déclarée « %s », mais %d %% des opérations passent par %s, canal inattendu pour ce profil.',
                $profil['libelle'],
                (int) round($part * 100),
                ModePaiement::from($modeDominant)->libelle(),
            ),
            poids: (int) $config['poids'],
            fait: [
                'profil_declare' => $profil['libelle'],
                'canaux_attendus' => $profil['attendus'],
                'canal_dominant' => $modeDominant,
                'part_canal_dominant' => $part,
                'repartition' => $repartition,
                'fenetre_jours' => $fenetre,
                'source_seuil' => 'demo',
            ],
        );
    }

    /**
     * @param  array<string, array{attendus: array<string>, libelle: string}>  $profils
     * @return array{attendus: array<string>, libelle: string}|null
     */
    private function profilPour(string $activiteNormalisee, array $profils): ?array
    {
        foreach ($profils as $fragment => $profil) {
            if (Str::contains($activiteNormalisee, $fragment)) {
                return $profil;
            }
        }

        return null;
    }
}
