@extends('layouts.agent')

@section('titre', 'Nouveau client')

@section('contenu')
    <form method="POST" action="{{ route('agent.clients.stocker') }}" x-data="{ type: 'personne_physique' }"
          class="space-y-4 rounded-lg border border-gray-200 bg-white p-4">
        @csrf

        @if ($errors->any())
            <div class="rounded-md bg-red-50 px-3 py-2 text-xs text-red-700">{{ $errors->first() }}</div>
        @endif

        <div>
            <label class="block text-xs font-medium text-gray-600">Type de client</label>
            <select name="type" x-model="type" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                <option value="personne_physique">Personne physique</option>
                <option value="personne_morale">Personne morale</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600">Nature de la relation</label>
            <select name="nature_relation" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                <option value="titulaire_compte">Titulaire de compte</option>
                <option value="occasionnel">Client occasionnel</option>
            </select>
        </div>

        <div x-show="type === 'personne_physique'">
            <label class="block text-xs font-medium text-gray-600">Nom</label>
            <input type="text" name="nom" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
            <label class="mt-2 block text-xs font-medium text-gray-600">Prénoms</label>
            <input type="text" name="prenoms" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
        </div>

        <div x-show="type === 'personne_morale'">
            <label class="block text-xs font-medium text-gray-600">Raison sociale</label>
            <input type="text" name="raison_sociale" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
        </div>

        <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">
            Créer et continuer la fiche
        </button>
    </form>
@endsection
