@extends('layouts.agent')

@section('titre', 'Clients')

@section('contenu')
    <div class="mb-3 flex items-center justify-between">
        <a href="{{ route('agent.clients.index', ['a_completer' => 1]) }}" class="text-xs text-emerald-700 hover:underline">
            Voir seulement les dossiers à compléter
        </a>
        <a href="{{ route('agent.clients.creer') }}" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">
            + Nouveau client
        </a>
    </div>

    <div class="space-y-2">
        @forelse ($clients as $client)
            <a href="{{ route('agent.clients.completer', $client) }}" class="block rounded-lg border border-gray-200 bg-white p-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-900">
                        {{ $client->type->value === 'personne_morale' ? $client->personneMorale?->raison_sociale : trim(($client->personnePhysique?->prenoms ?? '').' '.($client->personnePhysique?->nom ?? '')) }}
                    </span>
                    <span class="text-xs {{ $client->score_completude_kyc >= 100 ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $client->score_completude_kyc }} %
                    </span>
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ $client->type->libelle() }} · {{ $client->nature_relation->libelle() }}</p>
            </a>
        @empty
            <p class="text-sm text-gray-400">Aucun client.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
@endsection
