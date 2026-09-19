<?php

namespace App\Http\Requests\Admin;

use App\Models\ConfigurationSysteme;
use App\Services\Configuration\IdentiteSysteme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

class MettreAJourConfigurationRequest extends FormRequest
{
    private const MIMES_IMAGE = 'image/png,image/jpeg,image/svg+xml';

    public function authorize(): bool
    {
        return Gate::allows('gerer', ConfigurationSysteme::class);
    }

    public function rules(): array
    {
        $regles = [
            'nom_systeme' => ['required', 'string', 'max:60'],
            'sous_titre' => ['required', 'string', 'max:100'],
        ];

        foreach (IdentiteSysteme::CHAMPS_COULEUR as $champ) {
            $regles[$champ] = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        }

        // Le type MIME réel (contenu du fichier) est contrôlé, pas seulement l'extension.
        foreach (IdentiteSysteme::CHAMPS_LOGO as $champ) {
            $regles[$champ] = ['nullable', 'file', 'max:2048', 'mimetypes:'.self::MIMES_IMAGE, $this->svgSain()];
            $regles['retirer_'.$champ] = ['nullable', 'boolean'];
        }

        return $regles;
    }

    public function messages(): array
    {
        return [
            'nom_systeme.required' => 'Le nom du système est obligatoire.',
            'nom_systeme.max' => 'Le nom du système ne peut pas dépasser 60 caractères.',
            'sous_titre.required' => 'Le sous-titre est obligatoire.',
            'sous_titre.max' => 'Le sous-titre ne peut pas dépasser 100 caractères.',
            '*.regex' => 'Choisissez une couleur valide (format #RRGGBB).',
            '*.required' => 'Ce champ est obligatoire.',
            '*.max' => 'Le fichier ne doit pas dépasser 2 Mo.',
            '*.mimetypes' => 'Le fichier doit être une image PNG, JPG ou SVG.',
            '*.file' => 'Le fichier envoyé est invalide.',
        ];
    }

    /**
     * Un SVG est du texte exécutable : on refuse scripts, gestionnaires d'événements
     * et liens javascript: (l'image n'est affichée que via <img>, mais défense en profondeur).
     */
    private function svgSain(): \Closure
    {
        return function (string $attribut, mixed $fichier, \Closure $echec) {
            if (! $fichier instanceof UploadedFile || $fichier->getMimeType() !== 'image/svg+xml') {
                return;
            }

            $contenu = (string) file_get_contents($fichier->getRealPath());

            if (preg_match('/<script|\son[a-z]+\s*=|javascript:|<foreignObject/i', $contenu) === 1) {
                $echec('Ce fichier SVG contient du code actif et a été refusé.');
            }
        };
    }
}
