<?php

namespace App\Http\Controllers\Responsable\Ppe;

use App\Http\Controllers\Controller;
use App\Mail\ListePpeMail;
use App\Services\Audit\Consignateur;
use App\Services\Ppe\GenerateurListePpe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/** Envoi sécurisé (lien + code d'accès) de la liste des PPE de l'agence du responsable. */
class ListePpeController extends Controller
{
    public function envoyer(Request $request, GenerateurListePpe $generateur): RedirectResponse
    {
        $agent = $request->user('agent');

        $donnees = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
            'code_acces' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ], [
            'code_acces.confirmed' => 'La confirmation du code d’accès ne correspond pas.',
        ]);

        $partage = $generateur->generer($agent->agence, $agent);
        $partage->update([
            'destinataire_email' => $donnees['email'],
            'code_acces_hash' => Hash::make($donnees['code_acces']),
            'jeton_partage' => Str::random(64),
            'expire_le' => now()->addDays(7),
            'envoye_le' => now(),
        ]);

        Mail::to($donnees['email'])->send(new ListePpeMail($partage->fresh('agence'), $donnees['code_acces']));
        Consignateur::enregistrer('agent', $agent->id, 'envoi_liste_ppe', 'partage_liste_ppe', $partage->id);

        return back()->with('statut', 'Liste des PPE envoyée. Le lien expire dans 7 jours et exige le code d’accès défini.');
    }
}
