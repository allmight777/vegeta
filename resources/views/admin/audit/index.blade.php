@extends('layouts.admin')

@section('titre', 'Journal d\'audit')

@section('contenu')
    <form method="POST" action="{{ route('admin.journal-audit.verifier-chaine') }}" class="mb-4">
        @csrf
        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-semibold text-white">
            Vérifier la chaîne
        </button>
        @if ($ruptureId !== null)
            <span class="ml-2 text-sm {{ $ruptureId === 'aucune' ? 'text-emerald-600' : 'text-red-600' }}">
                {{ $ruptureId === 'aucune' ? 'Chaîne intègre, aucune rupture détectée.' : 'Rupture détectée à la ligne '.$ruptureId }}
            </span>
        @endif
    </form>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">#</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Acteur</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Action</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Cible</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Date</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Hash</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($lignes as $ligne)
                    <tr>
                        <td class="px-3 py-2">{{ $ligne->id }}</td>
                        <td class="px-3 py-2">{{ $ligne->acteur_type }}@if($ligne->acteur_id) #{{ $ligne->acteur_id }}@endif</td>
                        <td class="px-3 py-2 font-mono">{{ $ligne->action }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $ligne->cible_type }} {{ $ligne->cible_id }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $ligne->cree_le->format('d/m/Y H:i:s') }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-gray-400">{{ substr($ligne->hash_courant, 0, 12) }}…</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $lignes->links() }}</div>
@endsection
