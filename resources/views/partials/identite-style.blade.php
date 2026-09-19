{{--
    Identité visuelle configurable (Admin > Configuration) : redéfinit les variables CSS
    des layouts depuis la configuration, sans toucher aux feuilles de style existantes.
    Paramètres : $espace = admin | caissier | responsable | connexion
--}}
@php
    $couleurs = $identite;
    $espace = $espace ?? 'connexion';
    $espaceCouleur = match ($espace) {
        'admin' => $couleurs['couleur_espace_admin'],
        'caissier' => $couleurs['couleur_espace_caissier'],
        'responsable' => $couleurs['couleur_espace_responsable'],
        default => $couleurs['couleur_primaire'],
    };
    $rgba = fn (string $hex, float $a) => \App\Services\Configuration\IdentiteSysteme::rgba($hex, $a);
@endphp
<link rel="shortcut icon" href="{{ $identite['favicon_url'] }}">
<style>
    :root {
        --cif-yellow: {{ $couleurs['couleur_primaire'] }};
        --cif-yellow-soft: {{ $rgba($couleurs['couleur_primaire'], 0.12) }};
        --cif-green: {{ $couleurs['couleur_secondaire'] }};
        --cif-green-soft: {{ $rgba($couleurs['couleur_secondaire'], 0.09) }};
        --cif-dark: {{ $couleurs['couleur_sombre'] }};
        --cif-accent: {{ $espace === 'responsable' ? $espaceCouleur : $couleurs['couleur_accent'] }};
        --cif-accent-soft: {{ $rgba($espace === 'responsable' ? $espaceCouleur : $couleurs['couleur_accent'], 0.10) }};
        --cif-espace: {{ $espaceCouleur }};
        @if ($espace === 'connexion')
        --brand-yellow: {{ $couleurs['couleur_primaire'] }};
        --yellow-soft: {{ $rgba($couleurs['couleur_primaire'], 0.12) }};
        --yellow-medium: {{ $rgba($couleurs['couleur_primaire'], 0.22) }};
        --dark-accent: {{ $couleurs['couleur_sombre'] }};
        --bg-light: {{ $couleurs['couleur_page_connexion'] }};
        @endif
    }
    @if (in_array($espace, ['admin', 'caissier'], true))
    .admin-brand-icon, .agent-brand-icon, .sidebar-link.active i { color: var(--cif-espace); }
    @endif
    .marque-logo { width: 100%; height: 100%; object-fit: contain; display: block; }
</style>
