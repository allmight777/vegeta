<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Connexion administration</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-900 px-4 antialiased">
    <div class="w-full max-w-sm">
        <h1 class="mb-1 text-center text-lg font-semibold text-white">CIF-Empreinte</h1>
        <p class="mb-6 text-center text-sm text-slate-400">Administration</p>

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.connexion.stocker') }}" class="space-y-4 rounded-lg border border-slate-700 bg-slate-800 p-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-slate-200">E-mail</label>
                <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
                       class="mt-1 block w-full rounded-md border-slate-600 bg-slate-900 text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label for="mot_de_passe" class="block text-sm font-medium text-slate-200">Mot de passe</label>
                <input id="mot_de_passe" name="mot_de_passe" type="password" required
                       class="mt-1 block w-full rounded-md border-slate-600 bg-slate-900 text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Se connecter
            </button>
        </form>

        <p class="mt-4 text-center text-xs text-slate-500">
            <a href="{{ url('/connexion') }}" class="hover:underline">Espace agent</a>
        </p>
    </div>
</body>
</html>
