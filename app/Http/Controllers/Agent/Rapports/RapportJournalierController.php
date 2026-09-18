<?php

namespace App\Http\Controllers\Agent\Rapports;

use App\Http\Controllers\Controller;
use App\Mail\RapportJournalierMail;
use App\Models\RapportJournalier;
use App\Services\Audit\Consignateur;
use App\Services\Rapports\GenerateurRapportJournalier;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RapportJournalierController extends Controller
{
    public function index(Request $request, GenerateurRapportJournalier $generateur): View
    {
        $agent = $request->user('agent');
        $debut = CarbonImmutable::parse($request->input('date_debut', today()->toDateString()));
        $fin = CarbonImmutable::parse($request->input('date_fin', today()->toDateString()));

        abort_if($fin->lt($debut), 422, 'La date de fin doit être postérieure à la date de début.');

        $donnees = $generateur->donnees($agent->agence, $debut, $fin);
        $rapports = RapportJournalier::where('agence_id', $agent->agence_id)->latest()->limit(8)->get();

        return view('agent.rapports.index', compact('debut', 'fin', 'donnees', 'rapports'));
    }

    public function generer(Request $request, GenerateurRapportJournalier $generateur): RedirectResponse
    {
        $dates = $this->dates($request);
        $agent = $request->user('agent');
        [$debut, $fin] = $dates;
        $rapport = $generateur->generer($agent->agence, $debut, $fin, $agent);

        Consignateur::enregistrer('agent', $agent->id, 'generation_rapport_journalier', 'rapport_journalier', $rapport->id);

        return redirect()->route('agent.rapports.index', ['date_debut' => $dates[0]->toDateString(), 'date_fin' => $dates[1]->toDateString()])
            ->with('statut', 'Rapport généré. Vous pouvez maintenant le télécharger ou le partager de façon sécurisée.');
    }

    public function telecharger(Request $request, RapportJournalier $rapport)
    {
        abort_unless($rapport->agence_id === $request->user('agent')->agence_id, 403);
        abort_unless(Storage::disk('local')->exists($rapport->fichier_pdf_path), 404);

        return Storage::disk('local')->download($rapport->fichier_pdf_path, 'rapport-'.$rapport->date_debut->format('Ymd').'-'.$rapport->date_fin->format('Ymd').'.pdf');
    }

    public function envoyer(Request $request, RapportJournalier $rapport): RedirectResponse
    {
        abort_unless($rapport->agence_id === $request->user('agent')->agence_id, 403);

        $donnees = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
            'code_acces' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ], [
            'code_acces.confirmed' => 'La confirmation du code d’accès ne correspond pas.',
        ]);

        $rapport->update([
            'destinataire_email' => $donnees['email'],
            'code_acces_hash' => Hash::make($donnees['code_acces']),
            'jeton_partage' => Str::random(64),
            'expire_le' => now()->addDays(7),
            'envoye_le' => now(),
        ]);

        Mail::to($donnees['email'])->send(new RapportJournalierMail($rapport->fresh(), $donnees['code_acces']));
        Consignateur::enregistrer('agent', $request->user('agent')->id, 'envoi_rapport_journalier', 'rapport_journalier', $rapport->id);

        return back()->with('statut', 'Rapport envoyé. Le lien expire dans 7 jours et exige le code d’accès défini.');
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private function dates(Request $request): array
    {
        $donnees = $request->validate([
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
        ]);

        return [CarbonImmutable::parse($donnees['date_debut']), CarbonImmutable::parse($donnees['date_fin'])];
    }
}
