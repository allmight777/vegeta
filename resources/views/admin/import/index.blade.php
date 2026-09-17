@extends('layouts.admin')

@section('titre', 'Imports')

@section('contenu')
    <a href="{{ route('admin.import.creer') }}" class="mb-4 inline-block rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
        Nouvel import
    </a>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Fichier</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Agence</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Lignes</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Nouveaux</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">À compléter</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Statut</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($lots as $lot)
                    <tr>
                        <td class="px-3 py-2">{{ $lot->nom_fichier }}</td>
                        <td class="px-3 py-2">{{ $lot->agence->reseau->nom }} — {{ $lot->agence->nom }}</td>
                        <td class="px-3 py-2">{{ $lot->nombre_lignes }}</td>
                        <td class="px-3 py-2">{{ $lot->nombre_nouveaux }}</td>
                        <td class="px-3 py-2">{{ $lot->nombre_a_completer }}</td>
                        <td class="px-3 py-2">{{ $lot->statut->libelle() }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $lot->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-6 text-center text-gray-400">Aucun import pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $lots->links() }}</div>
@endsection
