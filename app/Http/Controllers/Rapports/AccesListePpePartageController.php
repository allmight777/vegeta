<?php

namespace App\Http\Controllers\Rapports;

use App\Http\Controllers\Controller;
use App\Models\PartageListePpe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AccesListePpePartageController extends Controller
{
    public function afficher(string $jeton): View
    {
        $partage = $this->partageValide($jeton);

        return view('rapports.acces-ppe', compact('partage', 'jeton'));
    }

    public function telecharger(Request $request, string $jeton)
    {
        $partage = $this->partageValide($jeton);
        $donnees = $request->validate(['code_acces' => ['required', 'string']]);

        if (! Hash::check($donnees['code_acces'], $partage->code_acces_hash)) {
            return back()->withErrors(['code_acces' => 'Code d’accès incorrect.']);
        }

        abort_unless(Storage::disk('local')->exists($partage->fichier_pdf_path), 404);

        return Storage::disk('local')->download($partage->fichier_pdf_path, 'liste-ppe-'.$partage->envoye_le->format('Ymd').'.pdf');
    }

    private function partageValide(string $jeton): PartageListePpe
    {
        $partage = PartageListePpe::where('jeton_partage', $jeton)->firstOrFail();
        abort_if($partage->expire_le === null || $partage->expire_le->isPast(), 410, 'Ce lien sécurisé a expiré.');

        return $partage;
    }
}
