@extends('layouts.admin')

@section('titre', 'Importer un fichier core banking')

@section('contenu')
    <form method="POST" action="{{ route('admin.import.televerser') }}" enctype="multipart/form-data"
          class="max-w-lg space-y-4 rounded-lg border border-gray-200 bg-white p-5">
        @csrf

        @if ($errors->any())
            <div class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700">Agence</label>
            <select name="agence_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach ($agences as $agence)
                    <option value="{{ $agence->id }}">{{ $agence->reseau->nom }} — {{ $agence->nom }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Fichier CSV</label>
            <input type="file" name="fichier" accept=".csv,text/csv" required class="mt-1 block w-full text-sm">
            <p class="mt-1 text-xs text-gray-500">Export fictif d'un système existant — aucune donnée réelle.</p>
        </div>

        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
            Aperçu et mapping des colonnes
        </button>
    </form>
@endsection
