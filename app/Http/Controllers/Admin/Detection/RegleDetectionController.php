<?php

namespace App\Http\Controllers\Admin\Detection;

use App\Http\Controllers\Controller;
use App\Models\RegleDetection;
use App\Services\Audit\Consignateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegleDetectionController extends Controller
{
    /**
     * Liste en lecture + accès aux actions de modification (activer/désactiver, éditer).
     */
    public function index(): View
    {
        return view('admin.detection.index', [
            'regles' => RegleDetection::orderBy('code')->get(),
        ]);
    }

    /**
     * Active ou désactive une règle sans toucher à ses paramètres.
     */
    public function basculer(RegleDetection $regle): RedirectResponse
    {
        $regle->update(['actif' => ! $regle->actif]);

        Consignateur::enregistrer(
            'admin',
            auth('admin')->id(),
            $regle->actif ? 'activation_regle_detection' : 'desactivation_regle_detection',
            'regle_detection',
            $regle->id,
            ['code' => $regle->code]
        );

        return back()->with(
            'statut',
            $regle->actif
                ? "Règle {$regle->code} activée."
                : "Règle {$regle->code} désactivée."
        );
    }

    /**
     * Affiche le formulaire d'édition des paramètres d'une règle.
     */
    public function editer(RegleDetection $regle): View
    {
        return view('admin.detection.editer', [
            'regle' => $regle,
        ]);
    }

    /**
     * Enregistre les modifications de paramètres d'une règle.
     */
    public function mettreAJour(Request $request, RegleDetection $regle): RedirectResponse
    {
        $donnees = $request->validate([
            'libelle' => 'required|string|max:255',
            'parametres' => 'required|array',
            'parametres.*' => 'nullable',
            'reference_texte' => 'nullable|string|max:255',
        ]);

        // Normalise les paramètres : convertit les valeurs numériques en int/float,
        // garde les chaînes telles quelles, ignore les vides.
        $parametres = collect($donnees['parametres'])
            ->map(function ($valeur) {
                if ($valeur === null || $valeur === '') {
                    return null;
                }
                if (is_numeric($valeur)) {
                    return str_contains((string) $valeur, '.')
                        ? (float) $valeur
                        : (int) $valeur;
                }

                return (string) $valeur;
            })
            ->filter(fn ($valeur) => $valeur !== null)
            ->all();

        $regle->update([
            'libelle' => $donnees['libelle'],
            'parametres' => $parametres,
            'reference_texte' => $donnees['reference_texte'] ?? null,
        ]);

        Consignateur::enregistrer(
            'admin',
            auth('admin')->id(),
            'modification_regle_detection',
            'regle_detection',
            $regle->id,
            ['code' => $regle->code, 'parametres' => $parametres]
        );

        return redirect()
            ->route('admin.regles-detection.index')
            ->with('statut', "Règle {$regle->code} mise à jour.");
    }
}
