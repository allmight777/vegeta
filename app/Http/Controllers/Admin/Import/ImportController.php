<?php

namespace App\Http\Controllers\Admin\Import;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Import\ConfirmerRequest;
use App\Http\Requests\Admin\Import\TeleverserRequest;
use App\Models\Agence;
use App\Models\ImportLot;
use App\Services\Audit\Consignateur;
use App\Services\Empreinte\ServiceEmpreinte;
use App\Services\Filtrage\MoteurFiltrage;
use App\Services\Import\ImportateurCsv;
use App\Services\Kyc\CalculateurCompletude;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(): View
    {
        return view('admin.import.index', [
            'lots' => ImportLot::with('agence.reseau')->latest()->paginate(15),
        ]);
    }

    public function creer(): View
    {
        return view('admin.import.creer', ['agences' => Agence::with('reseau')->orderBy('nom')->get()]);
    }

    public function televerser(TeleverserRequest $request): View
    {
        $donnees = $request->validated();
        $chemin = $request->file('fichier')->store('imports');

        $apercu = app(ImportateurCsv::class)->apercu($chemin);

        return view('admin.import.mapping', [
            'chemin' => $chemin,
            'agenceId' => $donnees['agence_id'],
            'entetes' => $apercu['entetes'],
            'lignes' => $apercu['lignes'],
            'champsCibles' => ImportateurCsv::CHAMPS_CIBLES,
        ]);
    }

    public function confirmer(ConfirmerRequest $request, ImportateurCsv $importateur): RedirectResponse
    {
        $donnees = $request->validated();
        $agence = Agence::findOrFail($donnees['agence_id']);

        $lot = $importateur->importer($donnees['fichier'], array_filter($donnees['mapping']), $agence, app(ServiceEmpreinte::class), app(CalculateurCompletude::class), app(MoteurFiltrage::class));

        Storage::disk('local')->delete($donnees['fichier']);

        Consignateur::enregistrer('admin', auth('admin')->id(), 'import_csv', 'import_lot', (string) $lot->id);

        return redirect()->route('admin.import.index')->with('statut', "Import terminé : {$lot->nombre_lignes} ligne(s), {$lot->nombre_nouveaux} nouveau(x) client(s), {$lot->nombre_a_completer} à compléter.");
    }
}
