<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Administration — @yield('titre', '')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 text-gray-900 antialiased">

    <header class="border-b border-slate-700 bg-slate-800 text-white">
        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
            <div class="flex items-center gap-3">
                <span class="rounded bg-slate-700 px-2 py-0.5 text-xs font-semibold uppercase tracking-wide">Administration</span>
                <span class="text-sm font-semibold">CIF-Empreinte</span>
            </div>
            @auth('admin')
                <div class="flex items-center gap-3 text-sm text-slate-200">
                    <span>{{ auth('admin')->user()->nom }}</span>
                    <span class="text-slate-400">·</span>
                    <span>{{ auth('admin')->user()->estAdminPlateforme() ? 'Admin plateforme' : 'Admin réseau' }}</span>
                    <form method="POST" action="{{ route('admin.connexion.detruire') }}">
                        @csrf
                        <button type="submit" class="text-red-300 hover:underline">Déconnexion</button>
                    </form>
                </div>
            @endauth
        </div>
        @auth('admin')
            @php
                $entreesNav = [
                    ['libelle' => 'Tableau de bord', 'route' => 'admin.tableau-de-bord.index'],
                    ['libelle' => 'Listes', 'route' => 'admin.listes.index'],
                    ['libelle' => 'PPE / Signataires', 'route' => 'admin.ppe.signataires.index'],
                    ['libelle' => 'Règles de détection', 'route' => 'admin.regles-detection.index'],
                    ['libelle' => 'Import', 'route' => 'admin.import.creer'],
                    ['libelle' => 'Journal d\'audit', 'route' => 'admin.journal-audit.index'],
                ];
            @endphp
            <nav class="flex flex-wrap gap-1 border-t border-slate-700 px-4 py-1.5 text-sm">
                @foreach ($entreesNav as $entree)
                    @if (Route::has($entree['route']))
                        <a href="{{ route($entree['route']) }}"
                           class="rounded px-2 py-1 {{ request()->routeIs(str($entree['route'])->before('.')->append('.*')) ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700/60' }}">
                            {{ $entree['libelle'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
        @endauth
    </header>

    @if (session('statut'))
        <div class="mx-4 mt-3 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
            {{ session('statut') }}
        </div>
    @endif

    <main class="mx-auto max-w-6xl px-4 py-6">
        <h1 class="mb-4 text-xl font-semibold text-gray-900">@yield('titre')</h1>
        @yield('contenu')
    </main>
</body>
</html>
