@extends('layouts.agent')

@section('titre', 'Tableau de bord')

@section('contenu')
    <div class="mb-4 rounded-lg border border-gray-200 bg-white p-4">
        <p class="text-xs text-gray-500">Opérations du jour</p>
        <p class="mt-1 text-2xl font-semibold">{{ $operationsDuJour }}</p>
    </div>

    <h2 class="mb-2 text-sm font-semibold text-gray-700">Clients à compléter</h2>
    <div class="space-y-1.5">
        @forelse ($clientsACompleter as $client)
            <a href="{{ route('agent.clients.completer', $client) }}" class="flex items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">
                <span>{{ $client->nomAffichage() }}</span>
                <span class="text-amber-600">{{ $client->score_completude_kyc }} %</span>
            </a>
        @empty
            <p class="text-sm text-gray-400">Aucun dossier en attente.</p>
        @endforelse
    </div>
@endsection
