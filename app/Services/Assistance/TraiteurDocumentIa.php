<?php

namespace App\Services\Assistance;

use App\Enums\StatutExtractionDocument;
use App\Models\DocumentIa;
use App\Services\Kyc\SelecteurExtracteurDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Traite un document déposé dans la bibliothèque documentaire de l'assistant IA
 * (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §1.3) : stockage → extraction → mise à
 * disposition de `contenu_extrait` pour OutilRechercheDocumentaire. Chaque fichier est
 * traité indépendamment — une exception sur l'un n'empêche jamais les autres (même
 * garantie que Services\Kyc\ExtracteurDocumentClient).
 */
class TraiteurDocumentIa
{
    public function __construct(private readonly SelecteurExtracteurDocument $selecteur) {}

    /**
     * @param  array{reseau_id: ?int, agence_id: ?int, visible_caissier: bool, visible_responsable_agence: bool}  $portee
     */
    public function traiter(UploadedFile $fichier, array $portee, ?int $adminId): DocumentIa
    {
        set_time_limit(120);

        $cheminStocke = Storage::disk('documents_ia')->putFileAs(
            '',
            $fichier,
            Str::uuid().'.'.$fichier->getClientOriginalExtension(),
        );

        $document = DocumentIa::create([
            'titre' => pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME),
            'nom_fichier_original' => $fichier->getClientOriginalName(),
            'chemin_fichier' => $cheminStocke,
            'type_mime' => (string) $fichier->getClientMimeType(),
            'taille_octets' => $fichier->getSize(),
            'statut_extraction' => StatutExtractionDocument::EnAttente,
            'visible_caissier' => $portee['visible_caissier'],
            'visible_responsable_agence' => $portee['visible_responsable_agence'],
            'visible_administrateur' => true,
            'reseau_id' => $portee['reseau_id'],
            'agence_id' => $portee['agence_id'],
            'televerse_par_admin_id' => $adminId,
        ]);

        try {
            $this->extraire($document);
        } catch (Throwable $e) {
            $document->update([
                'statut_extraction' => StatutExtractionDocument::Echouee,
                'erreur_message' => 'Erreur inattendue pendant l\'extraction : '.$e->getMessage(),
            ]);
        }

        return $document->fresh();
    }

    private function extraire(DocumentIa $document): void
    {
        $cheminAbsolu = Storage::disk('documents_ia')->path($document->chemin_fichier);
        $extension = pathinfo($document->nom_fichier_original, PATHINFO_EXTENSION);

        $resultat = $this->selecteur->choisir($document->type_mime, $extension)->extraire($cheminAbsolu, $document->type_mime);

        if (! $resultat->reussie) {
            $document->update([
                'statut_extraction' => StatutExtractionDocument::Echouee,
                'erreur_message' => $resultat->erreurMessage,
            ]);

            return;
        }

        $document->update([
            'contenu_extrait' => $resultat->texte,
            'methode_extraction' => $resultat->methode,
            'statut_extraction' => StatutExtractionDocument::Reussie,
        ]);
    }
}
