@extends('layouts.admin')

@section('titre', 'Faire correspondre les colonnes')

@section('contenu')
    <p class="mb-4 text-sm text-gray-500">
        Aperçu des {{ count($lignes) }} premières lignes. Choisissez à quel champ CIF-Empreinte correspond
        chaque colonne du fichier — colonne laissée sur « — » : ignorée.
    </p>

    <div class="mb-6 overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    @foreach ($entetes as $entete)
                        <th class="px-3 py-2 text-left font-medium text-gray-500">{{ $entete }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($lignes as $ligne)
                    <tr>
                        @foreach ($entetes as $entete)
                            <td class="px-3 py-2 text-gray-700">{{ $ligne[$entete] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('admin.import.confirmer') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-5">
        @csrf
        <input type="hidden" name="fichier" value="{{ $chemin }}">
        <input type="hidden" name="agence_id" value="{{ $agenceId }}">

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ($entetes as $entete)
                <div>
                    <label class="block text-xs font-medium text-gray-600">{{ $entete }}</label>
                    <select name="mapping[{{ $entete }}]" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        <option value="">— ignorer —</option>
                        @foreach ($champsCibles as $code => $libelle)
                            <option value="{{ $code }}" @selected(strtolower(str_replace(' ', '_', $entete)) === $code)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>

        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            Lancer l'import
        </button>
    </form>
@endsection
