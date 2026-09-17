<?php

namespace App\Http\Controllers\Admin\Authentification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Authentification\ConnexionRequest;
use App\Models\Admin;
use App\Services\Audit\Consignateur;
use App\Services\Securite\IndexAveugle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConnexionController extends Controller
{
    public function creer(): View
    {
        return view('admin.authentification.connexion');
    }

    public function stocker(ConnexionRequest $request, IndexAveugle $indexAveugle): RedirectResponse
    {
        $donnees = $request->validated();
        $idx = $indexAveugle->calculer($donnees['email'], 'email');
        $admin = Admin::where('email_idx', $idx)->first();

        if (! $admin || ! $admin->actif || ! Hash::check($donnees['mot_de_passe'], $admin->mot_de_passe)) {
            Consignateur::enregistrer('admin', $admin?->id, 'connexion_echouee');

            throw ValidationException::withMessages([
                'email' => 'Identifiants incorrects ou compte désactivé.',
            ]);
        }

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();
        Consignateur::enregistrer('admin', $admin->id, 'connexion');

        return redirect()->intended(route('admin.tableau-de-bord.index'));
    }

    public function detruire(Request $request): RedirectResponse
    {
        $adminId = Auth::guard('admin')->id();
        Auth::guard('admin')->logout();
        Consignateur::enregistrer('admin', $adminId, 'deconnexion');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/connexion');
    }
}
