@extends('layouts.agent')

@section('titre', 'Nouvelle opération')

@section('contenu')
    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 px-3 py-2 text-xs text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('agent.operations.stocker') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-4">
        @csrf

        <div>
            <label class="block text-xs font-medium text-gray-600">Compte</label>
            <select name="compte_id" required class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                <option value="">— sélectionner —</option>
                @foreach ($comptes as $compte)
                    <option value="{{ $compte->id }}">
                        {{ $compte->numero }} —
                        {{ $compte->client->type->value === 'personne_morale' ? $compte->client->personneMorale?->raison_sociale : trim(($compte->client->personnePhysique?->prenoms ?? '').' '.($compte->client->personnePhysique?->nom ?? '')) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600">Type</label>
            <select name="type" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                <option value="depot">Dépôt</option>
                <option value="retrait">Retrait</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600">Montant (FCFA)</label>
            <input type="number" step="0.01" name="montant" required class="mt-1 block w-full rounded-md border-gray-300 text-sm">
        </div>

        <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">
            Enregistrer l'opération
        </button>
    </form>
@endsection
