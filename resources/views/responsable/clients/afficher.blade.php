@extends('layouts.responsable')

@section('titre', 'Dossier client')
@section('sous-titre', 'Lecture seule — supervision')

@section('contenu')

    @php
        $estMorale = $client->type->value === 'personne_morale';
        $personne = $estMorale ? $client->personneMorale : $client->personnePhysique;
        $champsManquants = $personne?->champs_manquants ?? [];
    @endphp

    <div class="space-y-5">

        <section class="rounded-md border border-gray-200 bg-white px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">{{ $client->nomAffichage() ?: 'Client sans nom' }}</h2>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $client->type->libelle() }} &middot; {{ $client->nature_relation->libelle() }}
                    </p>
                </div>
                <span class="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">
                    Complétude KYC : {{ $client->score_completude_kyc }} %
                </span>
            </div>

            @if ($client->identite_id)
                <a href="{{ route('responsable.identites.afficher', $client->identite_id) }}" class="mt-3 inline-block text-xs font-semibold text-emerald-700 hover:underline">
                    Voir la vue consolidée de cette personne (toutes agences) →
                </a>
            @endif
        </section>

        <section>
            <h2 class="mb-2 text-sm font-semibold text-gray-700">Champs manquants</h2>
            <div class="rounded-md border border-gray-200 bg-white px-3 py-3 text-sm">
                @if (empty($champsManquants))
                    <p class="text-gray-400">Aucun champ manquant.</p>
                @else
                    <ul class="list-inside list-disc text-gray-700">
                        @foreach ($champsManquants as $champ)
                            <li>{{ $champ }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <section>
            <h2 class="mb-2 text-sm font-semibold text-gray-700">Comptes</h2>
            <div class="space-y-1.5">
                @forelse ($client->comptes as $compte)
                    <div class="flex items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">
                        <span class="text-gray-700">{{ $compte->agence?->nom }}</span>
                        <span class="text-xs text-gray-400">{{ $compte->created_at->format('d/m/Y') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucun compte ouvert.</p>
                @endforelse
            </div>
        </section>

        @if ($estMorale)
            <section>
                <h2 class="mb-2 text-sm font-semibold text-gray-700">Signataires</h2>
                <div class="space-y-1.5">
                    @forelse ($client->personneMorale?->signataires ?? [] as $signataire)
                        <div class="flex items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">
                            <span class="text-gray-700">{{ $signataire->nom }}</span>
                            <span class="text-xs text-gray-400">{{ $signataire->role }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Aucun signataire enregistré.</p>
                    @endforelse
                </div>
            </section>
        @endif

    </div>

@endsection
