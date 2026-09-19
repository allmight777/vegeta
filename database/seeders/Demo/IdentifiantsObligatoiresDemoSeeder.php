<?php

namespace Database\Seeders\Demo;

use App\Models\Client;
use App\Models\PersonnePhysique;
use App\Services\Kyc\CalculateurCompletude;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Complète les champs obligatoires (NPI, téléphone, email, date de naissance, pièce
 * d'identité) des personnes physiques de démonstration, quelle que soit la façon dont le
 * client a été créé (import CSV, saisie, scénario). Valeurs entièrement synthétiques,
 * dérivées de manière déterministe de l'identifiant du client. À exécuter en dernier.
 */
class IdentifiantsObligatoiresDemoSeeder extends Seeder
{
    public function run(CalculateurCompletude $calculateur): void
    {
        if (! config('cif_demo.actif')) {
            return;
        }

        $nombre = 0;

        foreach (PersonnePhysique::with('client')->get() as $i => $personne) {
            $rang = $i + 1;

            $personne->fill([
                'date_naissance' => $personne->date_naissance ?: '1985-01-01',
                'piece_identite_type' => $personne->piece_identite_type ?: 'cni',
                'piece_identite_numero' => $personne->piece_identite_numero ?: sprintf('CIP-9%05d', $rang),
                'mere' => $personne->mere ?: 'Marie '.Str::title(Str::lower((string) $personne->nom)),
                'piece_identite_expiration' => $personne->piece_identite_expiration ?: now()->addYears(3)->toDateString(),
                'telephone' => $personne->telephone ?: sprintf('+2299700%04d', $rang),
                'email' => $personne->email ?: sprintf('%s.%d@exemple.test', Str::slug((string) $personne->nom), $rang),
            ]);
            $personne->definirNpi(sprintf('%010d', 1000000000 + $rang));
            $personne->save();

            if ($personne->client instanceof Client) {
                // Les dossiers volontairement vieillis et incomplets par SoupconDemoSeeder
                // gardent leur score forcé : ils illustrent l'indicateur « documents ».
                $scoreForce = $personne->client->created_at?->lt(now()->subDays(30)) ? $personne->client->score_completude_kyc : null;
                $calculateur->evaluer($personne->client->fresh());

                if ($scoreForce !== null) {
                    $personne->client->forceFill(['score_completude_kyc' => $scoreForce])->saveQuietly();
                }
            }

            $nombre++;
        }

        $this->command?->info("IdentifiantsObligatoiresDemoSeeder : {$nombre} personne(s) physique(s) complétée(s).");
    }
}
