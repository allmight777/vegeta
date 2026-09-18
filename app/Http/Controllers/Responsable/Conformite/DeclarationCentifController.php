<?php

namespace App\Http\Controllers\Responsable\Conformite;

use App\Http\Controllers\Controller;
use App\Models\DeclarationCentif;
use App\Services\Audit\Consignateur;
use App\Services\Rapports\GenerateurDeclarationCentif;
use Illuminate\Http\RedirectResponse;

class DeclarationCentifController extends Controller
{
    public function generer(DeclarationCentif $declarationCentif, GenerateurDeclarationCentif $generateur): RedirectResponse
    {
        $generateur->generer($declarationCentif);

        Consignateur::enregistrer('agent', auth('agent')->id(), 'generation_declaration_centif', 'declaration_centif', $declarationCentif->id);

        return redirect()->route('responsable.tableau-de-bord.index')->with('statut', 'Déclaration CENTIF générée (gabarit de démonstration).');
    }
}
