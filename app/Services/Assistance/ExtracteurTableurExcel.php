<?php

namespace App\Services\Assistance;

use App\Contracts\ExtracteurDocument;
use App\Data\ResultatExtraction;
use App\Enums\MethodeExtraction;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Extraction `.xlsx`/`.xls` pour la bibliothèque documentaire de l'assistant IA
 * (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §1.3). Convertit chaque feuille en texte
 * tabulaire simple (une ligne = une ligne de texte, colonnes séparées par des
 * tabulations) plutôt que de garder le format binaire — c'est ce texte, pas le fichier,
 * qui alimente la recherche documentaire.
 */
class ExtracteurTableurExcel implements ExtracteurDocument
{
    public function extraire(string $cheminAbsolu, string $typeMime): ResultatExtraction
    {
        try {
            $classeur = IOFactory::load($cheminAbsolu);
        } catch (ReaderException $e) {
            return new ResultatExtraction(false, null, null, 'Fichier tableur illisible ou corrompu : '.$e->getMessage());
        }

        $blocs = [];

        foreach ($classeur->getAllSheets() as $feuille) {
            $texteFeuille = $this->feuilleEnTexte($feuille);

            if ($texteFeuille !== '') {
                $blocs[] = "# {$feuille->getTitle()}\n{$texteFeuille}";
            }
        }

        $texte = trim(implode("\n\n", $blocs));

        if ($texte === '') {
            return new ResultatExtraction(false, null, null, 'Fichier tableur vide.');
        }

        return new ResultatExtraction(true, $texte, MethodeExtraction::Tableur);
    }

    private function feuilleEnTexte(Worksheet $feuille): string
    {
        $lignes = [];

        foreach ($feuille->toArray(null, true, true, false) as $ligne) {
            $ligne = array_map(fn ($valeur) => trim((string) ($valeur ?? '')), $ligne);

            if (implode('', $ligne) === '') {
                continue;
            }

            $lignes[] = implode("\t", $ligne);
        }

        return implode("\n", $lignes);
    }
}
