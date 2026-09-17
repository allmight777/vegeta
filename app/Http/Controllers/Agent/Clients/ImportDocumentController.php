<?php

namespace App\Http\Controllers\Agent\Clients;

use App\Enums\SourceCreation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Clients\CreerClientRequest;
use App\Http\Requests\Agent\Clients\ImporterDocumentsRequest;
use App\Models\DocumentClient;
use App\Services\Audit\Consignateur;
use App\Services\Contexte\ContexteReseau;
use App\Services\Kyc\CreateurClient;
use App\Services\Kyc\ExtracteurDocumentClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Import intelligent multi-documents (06_PROMPT §5) : dépôt de jusqu'à 10 fichiers,
 * extraction indépendante par fichier, revue et validation explicite un par un — jamais
 * de création automatique de client sans le clic "Valider et créer le client".
 */
class ImportDocumentController extends Controller
{
    public function creer(): View
    {
        return view('agent.clients.import.creer');
    }

    public function stocker(ImporterDocumentsRequest $request, ExtracteurDocumentClient $extracteur): RedirectResponse
    {
        $lot = (string) Str::uuid();
        $agentId = auth('agent')->id();

        foreach ($request->file('documents', []) as $fichier) {
            $extracteur->traiter($fichier, $lot, $agentId);
        }

        return redirect()->route('agent.clients.import.revue', $lot);
    }

    public function revue(string $lot): View
    {
        $documents = DocumentClient::where('import_lot_id', $lot)->orderBy('created_at')->get();

        return view('agent.clients.import.revue', ['documents' => $documents, 'lot' => $lot]);
    }

    public function revoir(DocumentClient $document): View
    {
        $type = in_array($document->type_client_devine, ['personne_physique', 'personne_morale'], true)
            ? $document->type_client_devine
            : 'personne_physique';

        $valeurs = collect($document->donnees_extraites ?? [])->map(fn ($champ) => $champ['valeur'] ?? null)->all();
        $confiances = collect($document->donnees_extraites ?? [])->map(fn ($champ) => $champ['confiance'] ?? null)->all();

        return view('agent.clients.import.revoir', [
            'document' => $document,
            'type' => $type,
            'valeurs' => $valeurs,
            'confiances' => $confiances,
        ]);
    }

    public function valider(CreerClientRequest $request, DocumentClient $document, ContexteReseau $contexte, CreateurClient $createurClient): RedirectResponse
    {
        $client = $createurClient->creer(
            $request->validated(),
            $contexte->reseauId(),
            SourceCreation::ImportDocument,
            $request->user('agent'),
        );

        $document->update(['client_id' => $client->id]);

        Consignateur::enregistrer('agent', auth('agent')->id(), 'import_document_valide', 'document_client', $document->id);

        return redirect()->route('agent.clients.completer', $client)
            ->with('statut', 'Client créé à partir du document importé.');
    }
}
