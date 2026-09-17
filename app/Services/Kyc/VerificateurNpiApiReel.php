<?php

namespace App\Services\Kyc;

use App\Contracts\VerificateurNpi;
use App\Data\ResultatVerificationNpi;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;

/**
 * Appelle l'API officielle de vérification NPI (RAVIP ou équivalent). Écrite mais non
 * branchable pour ce hackathon : aucune convention d'accès à une API NPI publique n'a
 * été obtenue par l'équipe — voir docs/DECISIONS.md. Jamais sélectionnée par défaut
 * (config('kyc.verificateur_npi') = 'simulateur').
 */
class VerificateurNpiApiReel implements VerificateurNpi
{
    public function verifier(string $npi): ResultatVerificationNpi
    {
        $reponse = Http::withToken((string) config('kyc.npi_api_cle'))
            ->timeout(5)
            ->get(rtrim((string) config('kyc.npi_api_url'), '/').'/verifier', ['npi' => $npi]);

        $donnees = $reponse->successful() ? $reponse->json() : [];

        return new ResultatVerificationNpi(
            estValide: (bool) ($donnees['est_valide'] ?? false),
            nomOfficiel: $donnees['nom_officiel'] ?? null,
            dateNaissanceOfficielle: $donnees['date_naissance'] ?? null,
            source: 'api_reelle',
            verifieLe: new DateTimeImmutable,
        );
    }
}
