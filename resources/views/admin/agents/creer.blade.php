@extends('layouts.admin')

@section('titre', 'Nouveau compte')

@section('contenu')
    <form method="POST" action="{{ route('admin.agents.stocker') }}"
          class="max-w-lg space-y-4 rounded-lg border border-gray-200 bg-white p-5">
        @csrf

        @if ($errors->any())
            <div class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700">Nom</label>
            <input type="text" name="nom" value="{{ old('nom') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Matricule</label>
            <input type="text" name="matricule" value="{{ old('matricule') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Agence</label>
            <select name="agence_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach ($agences as $agence)
                    <option value="{{ $agence->id }}" @selected(old('agence_id') == $agence->id)>{{ $agence->reseau->nom }} — {{ $agence->nom }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Rôle</label>
            <select name="role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="caissier" @selected(old('role') === 'caissier')>Caissier</option>
                <option value="responsable_agence" @selected(old('role') === 'responsable_agence')>Responsable d'agence</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Civilité (affichage uniquement)</label>
            <select name="civilite" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="non_precise" @selected(old('civilite', 'non_precise') === 'non_precise')>Non précisée</option>
                <option value="m" @selected(old('civilite') === 'm')>Masculin</option>
                <option value="f" @selected(old('civilite') === 'f')>Féminin</option>
            </select>
        </div>

        <p class="text-xs text-gray-500">
            Un mot de passe est généré automatiquement et affiché une seule fois après la création — aucun e-mail n'est envoyé.
        </p>

        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
            Créer le compte
        </button>
    </form>
@endsection
