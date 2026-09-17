<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — @yield('titre', 'Espace agent')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased" x-data="{ menuOuvert: false }">

    <header class="sticky top-0 z-10 border-b border-gray-200 bg-white">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2">
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500" title="Application hors ligne, fonctionne sans connexion"></span>
                <span class="text-sm font-semibold">CIF-Empreinte</span>
            </div>
            @auth('agent')
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <span>{{ auth('agent')->user()->nom }}</span>
                    <form method="POST" action="{{ route('agent.connexion.detruire') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline">Déconnexion</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    @if (session('statut'))
        <div class="mx-4 mt-3 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
            {{ session('statut') }}
        </div>
    @endif

    <main class="px-4 py-4 pb-24">
        <h1 class="mb-4 text-lg font-semibold text-gray-900">@yield('titre')</h1>
        @yield('contenu')
    </main>

    @auth('agent')
        @php
            $role = auth('agent')->user()->role->value;
            $entreesNav = [
                ['libelle' => 'Tableau', 'route' => 'agent.tableau-de-bord.index', 'roles' => null],
                ['libelle' => 'Clients', 'route' => 'agent.clients.index', 'roles' => null],
                ['libelle' => 'Opérations', 'route' => 'agent.operations.creer', 'roles' => null],
                ['libelle' => 'Filtrage', 'route' => 'agent.filtrage.index', 'roles' => ['responsable_lbcft', 'direction']],
                ['libelle' => 'Alertes', 'route' => 'agent.alertes.index', 'roles' => ['responsable_lbcft', 'direction']],
            ];
        @endphp
        <nav class="fixed inset-x-0 bottom-0 z-10 border-t border-gray-200 bg-white" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
            <div class="grid grid-cols-4">
                @foreach ($entreesNav as $entree)
                    @if (Route::has($entree['route']) && (is_null($entree['roles']) || in_array($role, $entree['roles'], true)))
                        <a href="{{ route($entree['route']) }}"
                           class="px-2 py-3 text-center text-xs font-medium {{ request()->routeIs(str($entree['route'])->before('.index').'*') ? 'text-emerald-700' : 'text-gray-500' }}">
                            {{ $entree['libelle'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </nav>
    @endauth
</body>
</html>
