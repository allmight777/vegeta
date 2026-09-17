<?php

namespace App\Services\Kyc;

use App\Enums\MethodeExtraction;
use App\Enums\StatutExtractionDocument;
use App\Models\DocumentClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Orchestre le pipeline d'un document déposé (06_PROMPT_FORMULAIRE_CLIENT_ENRICHI §5.2) :
 * stockage chiffré au repos (chemin non devinable) → sélection de l'extracteur
 * (Services\Kyc\SelecteurExtracteurDocument) → mapping vers le référentiel →
 * enregistrement du résultat. Chaque fichier est traité indépendamment : une exception
 * sur l'un n'empêche jamais les autres. Plafond de confiance OCR :
 * 07_PROMPT_MODE_DEGRADE_NPI_OCR §4.2.
 */
class ExtracteurDocumentClient
{
    private const PLAFOND_CONFIANCE_OCR_LOCAL = 0.7;

    public function __construct(
        private readonly SelecteurExtracteurDocument $selecteur,
        private readonly MappeurChampsExtraits $mappeur,
    ) {}

    public function traiter(UploadedFile $fichier, string $importLotId, ?int $agentId): DocumentClient
    {
        $cheminStocke = Storage::disk('documents_clients')->putFileAs(
            '',
            $fichier,
            Str::uuid().'.'.$fichier->getClientOriginalExtension(),
        );

        $document = DocumentClient::create([
            'import_lot_id' => $importLotId,
            'chemin_fichier' => $cheminStocke,
            'nom_fichier_original' => $fichier->getClientOriginalName(),
            'type_mime' => (string) $fichier->getClientMimeType(),
            'statut_extraction' => StatutExtractionDocument::EnAttente,
            'traite_par_agent_id' => $agentId,
        ]);

        try {
            $this->extraireEtMapper($document);
        } catch (Throwable $e) {
            $document->update([
                'statut_extraction' => StatutExtractionDocument::Echouee,
                'erreur_message' => 'Erreur inattendue pendant l\'extraction : '.$e->getMessage(),
                'traite_le' => now(),
            ]);
        }

        return $document->fresh();
    }

    private function extraireEtMapper(DocumentClient $document): void
    {
        $cheminAbsolu = Storage::disk('documents_clients')->path($document->chemin_fichier);
        $extension = pathinfo($document->nom_fichier_original, PATHINFO_EXTENSION);

        $resultat = $this->selecteur->choisir($document->type_mime, $extension)->extraire($cheminAbsolu, $document->type_mime);

        if (! $resultat->reussie) {
            $document->update([
                'statut_extraction' => StatutExtractionDocument::Echouee,
                'erreur_message' => $resultat->erreurMessage,
                'traite_le' => now(),
            ]);

            return;
        }

        $typeDevine = $this->mappeur->typeClientDevine($resultat->texte);
        $plafondConfiance = $resultat->methode === MethodeExtraction::OcrLocal
            ? self::PLAFOND_CONFIANCE_OCR_LOCAL
            : 1.0;

        $document->update([
            'type_client_devine' => $typeDevine,
            'donnees_extraites' => $this->mappeur->mapper($resultat->texte, $typeDevine, $plafondConfiance),
            'statut_extraction' => StatutExtractionDocument::Reussie,
            'methode_extraction' => $resultat->methode,
            'traite_le' => now(),
        ]);
    }
}
