<?php

namespace App\Http\Controllers\Controleur;

use App\Enums\StatutSuggestionSoupcon;
use App\Http\Controllers\Controller;
use App\Models\SuggestionSoupcon;
use Illuminate\View\View;

/**
 * Une seule page : « Clients suspectés » (18_PROMPT §5.1). Suggestions du réseau du contrôleur,
 * score décroissant, avec la liste des faits détectés (jamais un jugement). Une fois le dossier
 * traité par le responsable d'agence, seul le STATUT apparaît ici, jamais la décision.
 */
class TableauDeBordController extends Controller
{
    public function index(): View
    {
        $agent = auth('agent')->user();

        $suggestions = SuggestionSoupcon::query()
            ->duReseau((int) $agent->reseauId())
            ->where('statut', '!=', StatutSuggestionSoupcon::Ecartee->value)
            ->with(['client.personnePhysique', 'client.personneMorale', 'client.agenceCreation', 'controleur'])
            ->orderByDesc('score')
            ->orderByDesc('genere_le')
            ->get();

        return view('controleur.tableau.index', ['suggestions' => $suggestions]);
    }
}
