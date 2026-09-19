<?php

namespace App\Http\Controllers\Admin\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MettreAJourConfigurationRequest;
use App\Models\ConfigurationSysteme;
use App\Services\Audit\Consignateur;
use App\Services\Configuration\IdentiteSysteme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ConfigurationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('gerer', ConfigurationSysteme::class);

        return view('admin.configuration.index', [
            'valeurs' => IdentiteSysteme::brute(),
            'defauts' => IdentiteSysteme::defauts(),
            'ligne' => ConfigurationSysteme::query()->orderBy('id')->first(),
        ]);
    }

    public function mettreAJour(MettreAJourConfigurationRequest $requete): RedirectResponse
    {
        $avant = IdentiteSysteme::brute();
        $nouveau = $requete->safe()->only(['nom_systeme', 'sous_titre', ...IdentiteSysteme::CHAMPS_COULEUR]);
        $nouveau['nom_systeme'] = trim($nouveau['nom_systeme']);
        $nouveau['sous_titre'] = trim($nouveau['sous_titre']);
        $couleurs = array_map('strtoupper', array_intersect_key($nouveau, array_flip(IdentiteSysteme::CHAMPS_COULEUR)));
        $nouveau = $couleurs + $nouveau;

        $anciensFichiers = [];

        foreach (IdentiteSysteme::CHAMPS_LOGO as $champ) {
            $fichier = $requete->file($champ);

            if ($fichier instanceof UploadedFile) {
                $nouveau[$champ] = $this->stocker($fichier, $champ);
                $anciensFichiers[] = $avant[$champ];
            } elseif ($requete->boolean('retirer_'.$champ)) {
                $nouveau[$champ] = null;
                $anciensFichiers[] = $avant[$champ];
            }
        }

        $champsModifies = array_keys(array_filter(
            $nouveau,
            fn ($valeur, $champ) => ($avant[$champ] ?? null) !== $valeur,
            ARRAY_FILTER_USE_BOTH,
        ));

        $this->enregistrer($nouveau, $champsModifies, $anciensFichiers);

        return redirect()
            ->route('admin.configuration.index')
            ->with('succes', $champsModifies === []
                ? 'Aucun changement à enregistrer.'
                : 'Identité du système enregistrée : elle est appliquée partout dès maintenant.');
    }

    public function reinitialiser(): RedirectResponse
    {
        Gate::authorize('gerer', ConfigurationSysteme::class);

        $avant = IdentiteSysteme::brute();
        $defauts = IdentiteSysteme::defauts();

        $champsModifies = array_keys(array_filter(
            $defauts,
            fn ($valeur, $champ) => ($avant[$champ] ?? null) !== $valeur,
            ARRAY_FILTER_USE_BOTH,
        ));

        $anciensFichiers = array_filter(array_map(fn ($champ) => $avant[$champ], IdentiteSysteme::CHAMPS_LOGO));

        $this->enregistrer($defauts, $champsModifies, $anciensFichiers);

        return redirect()
            ->route('admin.configuration.index')
            ->with('succes', "L'identité d'origine a été rétablie.");
    }

    private function enregistrer(array $valeurs, array $champsModifies, array $anciensFichiers): void
    {
        $admin = auth('admin')->user();

        ConfigurationSysteme::query()->updateOrCreate(
            ['id' => ConfigurationSysteme::query()->orderBy('id')->value('id')],
            $valeurs + ['modifie_par_admin_id' => $admin?->id, 'modifie_le' => now()],
        );

        IdentiteSysteme::oublier();

        foreach (array_filter($anciensFichiers) as $chemin) {
            $absolu = public_path(IdentiteSysteme::DOSSIER_PUBLIC.'/'.basename($chemin));

            if (is_file($absolu)) {
                @unlink($absolu);
            }
        }

        // Noms de champs uniquement, jamais leurs valeurs.
        Consignateur::enregistrer(
            'admin',
            $admin?->id,
            'configuration_systeme_modifiee',
            'configuration_systeme',
            Str::limit(implode(',', $champsModifies) ?: 'aucun_champ', 250, ''),
        );
    }

    private function stocker(UploadedFile $fichier, string $champ): string
    {
        $extension = match ($fichier->getMimeType()) {
            'image/png' => 'png',
            'image/svg+xml' => 'svg',
            default => 'jpg',
        };

        $nom = Str::before($champ, '_path').'_'.Str::random(12).'.'.$extension;
        $fichier->move(public_path(IdentiteSysteme::DOSSIER_PUBLIC), $nom);

        return $nom;
    }
}
