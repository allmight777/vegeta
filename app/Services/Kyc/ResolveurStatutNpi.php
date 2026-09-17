<?php

namespace App\Services\Kyc;

use App\Contracts\DetecteurConnectivite;
use App\Contracts\VerificateurNpi;
use App\Data\ResultatVerificationNpi;
use App\Enums\StatutVerificationNpi;
use Illuminate\Support\Facades\Cache;

/**
 * Point de décision unique pour le NPI : en ligne, comportement inchangé (appel réel
 * bloquant) ; hors ligne, plus aucun blocage sur une simple absence de connexion — un
 * format plausible passe en attente de rattrapage (07_PROMPT_MODE_DEGRADE_NPI_OCR §2.1).
 * Utilisé à la fois par NpiValideRegle (validation) et par les services de création
 * (persistance), avec un court cache pour éviter un double appel sur la même soumission.
 */
class ResolveurStatutNpi
{
    private const DUREE_CACHE_SECONDES = 30;

    public function __construct(
        private readonly DetecteurConnectivite $connectivite,
        private readonly VerificateurNpi $verificateur,
        private readonly ValidateurFormatNpi $validateurFormat,
    ) {}

    /**
     * @return array{statut: StatutVerificationNpi, resultat: ?ResultatVerificationNpi}
     */
    public function resoudre(string $npi): array
    {
        $npi = trim($npi);

        return Cache::remember(
            'npi:statut:'.hash('sha256', $npi),
            self::DUREE_CACHE_SECONDES,
            fn () => $this->calculer($npi),
        );
    }

    /**
     * @return array{statut: StatutVerificationNpi, resultat: ?ResultatVerificationNpi}
     */
    private function calculer(string $npi): array
    {
        if (! $this->connectivite->estEnLigne()) {
            return [
                'statut' => $this->validateurFormat->estPlausible($npi)
                    ? StatutVerificationNpi::EnAttenteConnexion
                    : StatutVerificationNpi::FormatInvalide,
                'resultat' => null,
            ];
        }

        $resultat = $this->verificateur->verifier($npi);

        return [
            'statut' => $resultat->estValide ? StatutVerificationNpi::VerifieValide : StatutVerificationNpi::VerifieInvalide,
            'resultat' => $resultat,
        ];
    }
}
