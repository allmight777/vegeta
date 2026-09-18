<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Agent;
use App\Models\DocumentIa;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentIaVisualisationController extends Controller
{
    public function voir(DocumentIa $document): StreamedResponse
    {
        $utilisateur = auth('agent')->user() ?? auth('admin')->user();

        abort_if($utilisateur === null, 403);
        abort_unless($this->estVisible($document, $utilisateur), 403);

        return Storage::disk('documents_ia')->response(
            $document->chemin_fichier,
            $document->nom_fichier_original,
        );
    }

    private function estVisible(DocumentIa $document, Agent|Admin $utilisateur): bool
    {
        if ($document->statut_extraction->value !== 'reussie') {
            return false;
        }

        if ($utilisateur instanceof Admin) {
            if ($utilisateur->estAdminPlateforme()) {
                return true;
            }

            return ($document->reseau_id === null && $document->agence_id === null)
                || $document->reseau_id === $utilisateur->reseau_id;
        }

        $colonne = $utilisateur->estCaissier() ? 'visible_caissier' : 'visible_responsable_agence';

        if (! $document->{$colonne}) {
            return false;
        }

        if ($document->reseau_id === null && $document->agence_id === null) {
            return true;
        }

        if ($document->agence_id === $utilisateur->agence_id) {
            return true;
        }

        return $document->reseau_id === $utilisateur->agence->reseau_id && $document->agence_id === null;
    }
}
