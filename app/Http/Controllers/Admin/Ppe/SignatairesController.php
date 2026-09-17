<?php

namespace App\Http\Controllers\Admin\Ppe;

use App\Http\Controllers\Controller;
use App\Models\Signataire;
use Illuminate\View\View;

class SignatairesController extends Controller
{
    /**
     * Vue consolidée de tous les signataires en attente de vérification, tous réseaux
     * confondus pour un admin plateforme, limitée au réseau pour un admin réseau (§5.4).
     */
    public function index(): View
    {
        $admin = auth('admin')->user();

        $signataires = Signataire::with('personneMorale.client.reseau')
            ->where('statut_filtrage', 'a_verifier')
            ->when($admin->reseau_id !== null, fn ($q) => $q->whereHas(
                'personneMorale.client',
                fn ($sous) => $sous->where('reseau_id', $admin->reseau_id),
            ))
            ->latest()
            ->paginate(20);

        return view('admin.ppe.signataires', ['signataires' => $signataires]);
    }
}
