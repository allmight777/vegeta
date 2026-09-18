<?php
// app/Http/Controllers/Admin/Listes/EntreeListeController.php

namespace App\Http\Controllers\Admin\Listes;

use App\Http\Controllers\Controller;
use App\Enums\SourceListeType;
use App\Models\EntreeListe;
use App\Services\Audit\Consignateur;
use App\Services\Listes\ImportateurListes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EntreeListeController extends Controller
{
    public function index(): View
    {
        return view('admin.listes.index', [
            'entrees' => EntreeListe::orderBy('source')
                ->orderByDesc('importee_le')
                ->paginate(30),
            'sources' => SourceListeType::cases(),
        ]);
    }

    public function importer(Request $request, ImportateurListes $importateur): RedirectResponse
    {
        $donnees = $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10 Mo
            'source' => 'required|string',
            'version' => 'required|string|max:32',
            'categorie' => 'nullable|string|max:64',
        ]);

        $source = SourceListeType::from($donnees['source']);

        $compteur = $importateur->importer(
            $request->file('fichier'),
            $source,
            $donnees['version'],
            $donnees['categorie'] ?? null,
        );

        Consignateur::enregistrer(
            'admin',
            auth('admin')->id(),
            'import_liste_sanctions',
            'entree_liste',
            null,
            ['source' => $source->value, 'nombre' => $compteur]
        );

        return back()->with('statut', "{$compteur} entrée(s) importée(s) avec succès.");
    }
}
