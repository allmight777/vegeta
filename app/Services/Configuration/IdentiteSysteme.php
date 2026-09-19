<?php

namespace App\Services\Configuration;

use App\Models\ConfigurationSysteme;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Point d'accès unique à l'identité visuelle du produit.
 *
 * Ne lève jamais d'exception : table absente, base injoignable ou valeur invalide
 * ramènent aux valeurs d'origine (jamais de page blanche à cause de la configuration).
 */
class IdentiteSysteme
{
    public const CLE_CACHE = 'configuration_systeme';

    public const CHAMPS_COULEUR = [
        'couleur_primaire', 'couleur_secondaire', 'couleur_accent', 'couleur_sombre',
        'couleur_espace_caissier', 'couleur_espace_responsable', 'couleur_espace_admin',
        'couleur_page_connexion',
    ];

    public const CHAMPS_LOGO = ['logo_principal_path', 'logo_connexion_path', 'favicon_path'];

    public const DOSSIER_PUBLIC = 'identite';

    private static ?array $memo = null;

    /**
     * Valeurs d'origine, relevées dans les CSS des layouts (--cif-yellow, --cif-green,
     * --cif-dark, --cif-accent du layout responsable, --bg-light des pages de connexion).
     */
    public static function defauts(): array
    {
        return [
            'nom_systeme' => 'CIF-Empreinte',
            'sous_titre' => "Système d'Empreinte Biométrique",
            'logo_principal_path' => null,
            'logo_connexion_path' => null,
            'favicon_path' => null,
            'couleur_primaire' => '#F0E535',
            'couleur_secondaire' => '#30C31A',
            'couleur_accent' => '#2563EB',
            'couleur_sombre' => '#2C343D',
            'couleur_espace_caissier' => '#F0E535',
            'couleur_espace_responsable' => '#2563EB',
            'couleur_espace_admin' => '#F0E535',
            'couleur_page_connexion' => '#F8FAFC',
        ];
    }

    /**
     * Valeurs effectives : défauts surchargés par la ligne configurée.
     * Les logos sont exposés en URL, avec repli sur l'image d'origine du dépôt.
     */
    public static function courante(): array
    {
        return self::$memo ??= self::construire(self::lireLigne());
    }

    /**
     * Valeurs brutes (chemins de fichiers, pas d'URL) pour le formulaire d'édition.
     */
    public static function brute(): array
    {
        return self::fusionner(self::lireLigne());
    }

    public static function nom(): string
    {
        return self::courante()['nom_systeme'];
    }

    public static function oublier(): void
    {
        self::$memo = null;

        try {
            Cache::forget(self::CLE_CACHE);
        } catch (Throwable) {
            // cache indisponible : rien à invalider
        }
    }

    public static function couleurValide(mixed $valeur): bool
    {
        return is_string($valeur) && preg_match('/^#[0-9A-Fa-f]{6}$/', $valeur) === 1;
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    public static function rgb(string $hex): array
    {
        return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    }

    public static function rgba(string $hex, float $alpha): string
    {
        [$r, $g, $b] = self::rgb($hex);

        return "rgba($r, $g, $b, $alpha)";
    }

    private static function lireLigne(): ?array
    {
        try {
            return Cache::remember(
                self::CLE_CACHE,
                86400,
                fn () => ConfigurationSysteme::query()->orderBy('id')->first()?->only(array_keys(self::defauts())),
            );
        } catch (Throwable) {
            return null;
        }
    }

    private static function fusionner(?array $ligne): array
    {
        $valeurs = self::defauts();

        foreach ($valeurs as $champ => $defaut) {
            $valeur = $ligne[$champ] ?? null;

            if (! is_string($valeur) || trim($valeur) === '') {
                continue;
            }

            if (in_array($champ, self::CHAMPS_COULEUR, true) && ! self::couleurValide($valeur)) {
                continue;
            }

            $valeurs[$champ] = $valeur;
        }

        return $valeurs;
    }

    private static function construire(?array $ligne): array
    {
        $valeurs = self::fusionner($ligne);

        $url = fn (?string $chemin) => $chemin && is_file(public_path(self::DOSSIER_PUBLIC.'/'.$chemin))
            ? asset(self::DOSSIER_PUBLIC.'/'.$chemin)
            : null;

        $valeurs['logo_principal_url'] = $url($valeurs['logo_principal_path']);
        $valeurs['logo_connexion_url'] = $url($valeurs['logo_connexion_path']) ?? asset('images/fececam.jpg');
        $valeurs['favicon_url'] = $url($valeurs['favicon_path']) ?? asset('images/fececam.jpg');

        return $valeurs;
    }
}
