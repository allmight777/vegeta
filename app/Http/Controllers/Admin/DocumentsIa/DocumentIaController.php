<?php

namespace App\Http\Controllers\Admin\DocumentsIa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DocumentsIa\StockerDocumentIaRequest;
use App\Models\Agence;
use App\Models\DocumentIa;
use App\Models\Reseau;
use App\Services\Assistance\TraiteurDocumentIa;
use App\Services\Audit\Consignateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Bibliothèque documentaire de l'assistant IA (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA
 * §1) : le seul chemin d'entrée est l'upload direct de l'administrateur sur ce contrôleur
 * — aucun connecteur externe (Google Drive ou autre) n'existe dans cette application.
 */
class DocumentIaController extends Controller
{
    public function index(): View
    {
        return view('admin.documents-ia.index', [
            'documents' => DocumentIa::with(['reseau', 'agence'])->latest()->paginate(20),
        ]);
    }

    public function creer(): View
    {
        return view('admin.documents-ia.creer', [
            'reseaux' => Reseau::orderBy('nom')->get(),
            'agences' => Agence::with('reseau')->orderBy('nom')->get(),
        ]);
    }

    public function stocker(StockerDocumentIaRequest $request, TraiteurDocumentIa $traiteur): RedirectResponse
    {
        $donnees = $request->validated();

        $portee = [
            'reseau_id' => $donnees['portee'] === 'reseau' ? $donnees['reseau_id'] : null,
            'agence_id' => $donnees['portee'] === 'agence' ? $donnees['agence_id'] : null,
            'visible_caissier' => (bool) ($donnees['visible_caissier'] ?? false),
            'visible_responsable_agence' => (bool) ($donnees['visible_responsable_agence'] ?? true),
        ];

        $nombre = 0;
        foreach ($request->file('documents', []) as $fichier) {
            $document = $traiteur->traiter($fichier, $portee, auth('admin')->id());
            Consignateur::enregistrer('admin', auth('admin')->id(), 'televersement_document_ia', 'document_ia', $document->id);
            $nombre++;
        }

        return redirect()->route('admin.documents-ia.index')->with('statut', "{$nombre} document(s) téléversé(s).");
    }

    public function mettreAJourVisibilite(DocumentIa $documentIa): RedirectResponse
    {
        $documentIa->update([
            'visible_caissier' => request()->boolean('visible_caissier'),
            'visible_responsable_agence' => request()->boolean('visible_responsable_agence'),
        ]);

        Consignateur::enregistrer('admin', auth('admin')->id(), 'modification_visibilite_document_ia', 'document_ia', $documentIa->id);

        return redirect()->route('admin.documents-ia.index')->with('statut', 'Visibilité mise à jour.');
    }

    public function supprimer(DocumentIa $documentIa): RedirectResponse
    {
        $documentIa->delete();

        Consignateur::enregistrer('admin', auth('admin')->id(), 'suppression_document_ia', 'document_ia', $documentIa->id);

        return redirect()->route('admin.documents-ia.index')->with('statut', 'Document supprimé.');
    }
}
