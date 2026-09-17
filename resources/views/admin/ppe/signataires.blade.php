@extends('layouts.admin')

@section('titre', 'Signataires à vérifier')

@section('contenu')
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Nom</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Rôle</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Personne morale</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Réseau</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Ajouté le</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($signataires as $signataire)
                    <tr>
                        <td class="px-3 py-2">{{ $signataire->nom }}</td>
                        <td class="px-3 py-2">{{ $signataire->role->libelle() }}</td>
                        <td class="px-3 py-2">{{ $signataire->personneMorale->raison_sociale }}</td>
                        <td class="px-3 py-2">{{ $signataire->personneMorale->client->reseau->nom }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $signataire->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-gray-400">Aucun signataire en attente.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $signataires->links() }}</div>
@endsection
