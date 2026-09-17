@extends('layouts.admin')

@section('titre', 'Listes de sanctions et PPE')

@section('contenu')
    <p class="mb-4 text-sm text-gray-500">
        Import : <code class="rounded bg-gray-100 px-1 py-0.5">php artisan listes:importer {source} --fichier=...</code>
        (CSV colonnes <code>nom,categorie</code>, jamais d'appel réseau pendant la démo).
    </p>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Source</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Nom (masqué)</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Catégorie</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Version</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Importée le</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($entrees as $entree)
                    <tr>
                        <td class="px-3 py-2">{{ $entree->source->libelle() }}</td>
                        <td class="px-3 py-2 font-mono">{{ $entree->nomMasque() }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $entree->categorie }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $entree->version_liste }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $entree->importee_le?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-gray-400">Aucune liste importée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
