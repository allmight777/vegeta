<?php

namespace App\Services\Kyc;

use App\Contracts\ExtracteurDocument;

/**
 * Aiguille vers la bonne implémentation d'extraction selon le fichier
 * (07_PROMPT_MODE_DEGRADE_NPI_OCR §4.3). L'OCR local est le seul chemin implémenté pour
 * tout ce qui n'est pas du `.docx` — il fonctionne à l'identique en ligne et hors ligne
 * (zéro dépendance externe supplémentaire), donc reste le défaut même avec une
 * connexion présente. Une IA en ligne resterait une amélioration optionnelle
 * (`config('extraction.preference_en_ligne')`), mais aucune implémentation n'existe
 * cette itération : il n'y a donc rien à sélectionner conditionnellement pour l'instant,
 * plutôt que de brancher sur une connectivité qui ne changerait rien au résultat.
 */
class SelecteurExtracteurDocument
{
    private const MIME_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    public function __construct(
        private readonly ExtracteurDocumentTexteNatif $texteNatif,
        private readonly ExtracteurDocumentOcrLocal $ocrLocal,
    ) {}

    public function choisir(string $typeMime, string $extension): ExtracteurDocument
    {
        $extension = strtolower($extension);

        if ($extension === 'docx' || $typeMime === self::MIME_DOCX) {
            return $this->texteNatif;
        }

        return $this->ocrLocal;
    }
}
