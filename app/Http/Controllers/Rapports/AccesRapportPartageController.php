<?php

namespace App\Http\Controllers\Rapports;

use App\Http\Controllers\Controller;
use App\Models\RapportJournalier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AccesRapportPartageController extends Controller
{
    public function afficher(string $jeton): View
    {
        $rapport = $this->rapportValide($jeton);

        return view('rapports.acces', compact('rapport', 'jeton'));
    }

    public function telecharger(Request $request, string $jeton)
    {
        $rapport = $this->rapportValide($jeton);
        $donnees = $request->validate(['code_acces' => ['required', 'string']]);

        if (! Hash::check($donnees['code_acces'], $rapport->code_acces_hash)) {
            return back()->withErrors(['code_acces' => 'Code d’accès incorrect.']);
        }

        abort_unless(Storage::disk('local')->exists($rapport->fichier_pdf_path), 404);

        return Storage::disk('local')->download($rapport->fichier_pdf_path, 'rapport-securise-'.$rapport->date_fin->format('Ymd').'.pdf');
    }

    private function rapportValide(string $jeton): RapportJournalier
    {
        $rapport = RapportJournalier::where('jeton_partage', $jeton)->firstOrFail();
        abort_if($rapport->expire_le === null || $rapport->expire_le->isPast(), 410, 'Ce lien sécurisé a expiré.');

        return $rapport;
    }
}
