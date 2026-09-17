@extends('layouts.agent')

@section('titre', 'Questions escaladées — Assistant IA')

@section('contenu')
    <div class="space-y-4">
        @if (session('statut'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('statut') }}
            </div>
        @endif

        <h1 class="text-sm font-semibold text-gray-700">Questions en attente de réponse</h1>

        @forelse ($escalades as $escalade)
            <div class="rounded-md border border-gray-200 bg-white p-3 text-sm">
                <div class="flex items-center justify-between text-xs text-gray-400">
                    <span>{{ $escalade->role_agent }} — {{ $escalade->contexte_ecran }}</span>
                    <span>{{ $escalade->created_at->diffForHumans() }}</span>
                </div>
                <p class="mt-1 font-medium text-gray-900">{{ $escalade->question }}</p>
                @if ($escalade->reponse_ia)
                    <p class="mt-1 text-xs text-gray-500">Réponse de l'assistant : {{ $escalade->reponse_ia }}</p>
                @endif

                <form method="POST" action="{{ route('agent.assistance.escalades.repondre', $escalade) }}" class="mt-2 space-y-2">
                    @csrf
                    <textarea name="reponse_responsable" required rows="3" class="w-full rounded-md border-gray-300 text-sm" placeholder="Votre réponse..."></textarea>
                    <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white">
                        Répondre et ajouter à la base de connaissances
                    </button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucune question en attente.</p>
        @endforelse
    </div>
@endsection
