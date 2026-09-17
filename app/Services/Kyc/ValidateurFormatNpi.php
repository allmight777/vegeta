<?php

namespace App\Services\Kyc;

/**
 * Contrôle purement local du format d'un NPI (longueur, structure) — ne prouve jamais
 * qu'un NPI est réel, seulement qu'il est bien formé. C'est le maximum garanti sans
 * connexion (07_PROMPT_MODE_DEGRADE_NPI_OCR §3.1).
 *
 * Format « 10 chiffres » : hypothèse de démonstration (source = demo), jamais confirmée
 * par un texte réglementaire ni un briefing CIF — à ajuster si l'équipe obtient la
 * structure réelle du NPI béninois (RAVIP).
 */
class ValidateurFormatNpi
{
    public function estPlausible(string $npi): bool
    {
        return (bool) preg_match('/^\d{10}$/', trim($npi));
    }
}
