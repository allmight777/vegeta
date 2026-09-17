<?php

namespace App\Contracts;

/**
 * Détecte la connectivité internet — pas le réseau SFD (cf. App\Models\Reseau,
 * Services\Contexte\ContexteReseau, notion distincte). Utilisé par tout service qui
 * appelle l'extérieur (VerificateurNpi, ExtracteurDocument) pour basculer en mode local
 * sans attendre un timeout long (07_PROMPT_MODE_DEGRADE_NPI_OCR §2).
 */
interface DetecteurConnectivite
{
    public function estEnLigne(): bool;
}
