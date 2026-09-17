<?php

namespace App\Console\Commands\Kyc;

use App\Contracts\DetecteurConnectivite;
use App\Contracts\VerificateurNpi;
use App\Enums\GraviteAlerte;
use App\Enums\StatutFileAttenteNpi;
use App\Enums\StatutVerificationNpi;
use App\Enums\TypeAlerte;
use App\Models\Alerte;
use App\Models\VerificationNpiEnAttente;
use App\Services\Audit\Consignateur;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

/**
 * Rattrapage automatique du mode dégradé NPI (07_PROMPT_MODE_DEGRADE_NPI_OCR §2.4).
 * Planifiée toutes les 5 minutes (routes/console.php). Ne supprime jamais un client :
 * un NPI invalide après coup devient une alerte critique, jamais une suppression
 * silencieuse ("l'outil recommande, l'humain décide", CLAUDE.md §3).
 */
class VerifierNpiEnAttente extends Command
{
    protected $signature = 'npi:verifier-en-attente';

    protected $description = 'Traite les NPI mis en attente faute de connexion, dès que la connexion revient.';

    public function handle(DetecteurConnectivite $connectivite, VerificateurNpi $verificateur): int
    {
        $lignesEnAttente = VerificationNpiEnAttente::where('statut', StatutFileAttenteNpi::EnAttente->value);

        if (! $connectivite->estEnLigne()) {
            $this->incrementerTentativesSansConnexion($lignesEnAttente);

            $this->comment('Toujours hors connexion — tentatives incrémentées, aucune vérification effectuée.');

            return self::SUCCESS;
        }

        $traitees = 0;

        $lignesEnAttente->chunkById(50, function ($lignes) use ($verificateur, &$traitees) {
            foreach ($lignes as $ligne) {
                $this->traiterLigne($ligne, $verificateur);
                $traitees++;
            }
        });

        $this->info("{$traitees} vérification(s) NPI en attente traitée(s).");

        return self::SUCCESS;
    }

    private function incrementerTentativesSansConnexion(Builder $lignesEnAttente): void
    {
        $maxTentatives = (int) config('kyc.npi_tentatives_max', 20);

        $lignesEnAttente->chunkById(100, function ($lignes) use ($maxTentatives) {
            foreach ($lignes as $ligne) {
                $nouvellesTentatives = $ligne->tentatives + 1;

                $ligne->update([
                    'tentatives' => $nouvellesTentatives,
                    'derniere_tentative_le' => now(),
                    'statut' => $nouvellesTentatives >= $maxTentatives
                        ? StatutFileAttenteNpi::EchoueeDefinitivement
                        : StatutFileAttenteNpi::EnAttente,
                ]);
            }
        });
    }

    private function traiterLigne(VerificationNpiEnAttente $ligne, VerificateurNpi $verificateur): void
    {
        $client = $ligne->client;

        if ($client === null || blank($ligne->npi_chiffre)) {
            $ligne->update(['statut' => StatutFileAttenteNpi::Traitee, 'npi_chiffre' => null]);

            return;
        }

        $resultat = $verificateur->verifier($ligne->npi_chiffre);
        $cible = $ligne->signataire_id !== null ? $ligne->signataire : $client->personnePhysique;

        if ($cible !== null) {
            $cible->npi_verifie_le = $resultat->verifieLe;
            $cible->npi_verification_source = $resultat->source;
            $cible->saveQuietly();
        }

        if ($resultat->estValide) {
            if ($ligne->signataire_id === null) {
                $client->update([
                    'statut_verification_npi' => StatutVerificationNpi::VerifieValide,
                    'npi_verifie_le' => $resultat->verifieLe,
                ]);
            }
        } else {
            // Ne jamais toucher au client existant : une alerte, pas une suppression.
            Alerte::create([
                'type' => TypeAlerte::NpiInvalideApresVerification,
                'client_id' => $client->id,
                'gravite' => GraviteAlerte::Critique,
                'explication_texte' => 'Le NPI de ce dossier s\'est révélé invalide lors de la vérification différée (créé hors connexion). Vérification manuelle requise avant toute opération.',
                'faits' => ['signataire_id' => $ligne->signataire_id],
            ]);

            if ($ligne->signataire_id === null) {
                $client->update(['statut_verification_npi' => StatutVerificationNpi::VerifieInvalide]);
            }

            Consignateur::enregistrer('systeme', null, 'npi_invalide_apres_verification', 'client', $client->id);
        }

        $ligne->update([
            'statut' => StatutFileAttenteNpi::Traitee,
            'derniere_tentative_le' => now(),
            'npi_chiffre' => null,
        ]);
    }
}
