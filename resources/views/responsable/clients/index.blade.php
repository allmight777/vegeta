@extends('layouts.responsable')

@section('titre', 'Supervision des clients')
@section('sous-titre', 'Lecture seule — la création et la complétion des dossiers restent au caissier')

@section('contenu')

@include('agent.clients._styles')

<div class="clients-page">

    <div class="clients-toolbar">

        <div class="clients-toolbar-left">

            <a
                href="{{ route('responsable.clients.index') }}"
                class="filter-link {{ !request('a_completer') ? 'active' : '' }}"
            >
                <i class="fa-solid fa-list"></i>
                Tous les clients
            </a>

            <a
                href="{{ route('responsable.clients.index', ['a_completer' => 1]) }}"
                class="filter-link {{ request('a_completer') ? 'active' : '' }}"
            >
                <i class="fa-solid fa-user-clock"></i>
                À compléter
            </a>

            <span class="clients-count">
                <i class="fa-solid fa-users"></i>
                {{ $clients->total() }} dossier{{ $clients->total() > 1 ? 's' : '' }}
            </span>

        </div>

        <form method="GET" action="{{ route('responsable.clients.index') }}" class="clients-toolbar-search">

            @if (request('a_completer'))
                <input type="hidden" name="a_completer" value="1">
            @endif

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                name="q"
                value="{{ $recherche }}"
                placeholder="Nom exact du client ou raison sociale..."
                aria-label="Rechercher un dossier"
            >

        </form>

    </div>

    @if ($clients->count())

        <div class="clients-list">

            @foreach ($clients as $client)

                @php
                    $estMorale = $client->type->value === 'personne_morale';

                    $nom = $estMorale
                        ? $client->personneMorale?->raison_sociale
                        : trim(($client->personnePhysique?->prenoms ?? '').' '.($client->personnePhysique?->nom ?? ''));

                    $score = (int) $client->score_completude_kyc;
                    $scoreClass = $score >= 80 ? 'high' : ($score >= 50 ? 'mid' : 'low');
                    $scoreIcon = $score >= 80 ? 'fa-circle-check' : ($score >= 50 ? 'fa-circle-half-stroke' : 'fa-triangle-exclamation');
                @endphp

                <a
                    href="{{ route('responsable.clients.afficher', $client) }}"
                    class="client-card {{ $estMorale ? 'morale' : '' }}"
                >

                    <div class="client-card-left">

                        <div class="client-avatar">
                            <i class="fa-solid {{ $estMorale ? 'fa-building' : 'fa-user' }}"></i>
                        </div>

                        <div class="client-card-body">

                            <div class="client-name">
                                {{ $nom ?: 'Client sans nom' }}
                            </div>

                            <div class="client-meta">

                                <span class="meta-chip">
                                    <i class="fa-solid {{ $estMorale ? 'fa-building' : 'fa-id-card' }}"></i>
                                    {{ $client->type->libelle() }}
                                </span>

                                <span class="meta-chip nature">
                                    <i class="fa-solid fa-handshake"></i>
                                    {{ $client->nature_relation->libelle() }}
                                </span>

                            </div>

                        </div>

                    </div>

                    <div class="client-card-right">

                        <span class="score-badge {{ $scoreClass }}">
                            <i class="fa-solid {{ $scoreIcon }}"></i>
                            {{ $score }} %
                        </span>

                        <span class="client-arrow">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>

                    </div>

                </a>

            @endforeach

        </div>

    @else

        <div class="clients-empty">

            <div class="clients-empty-icon">
                <i class="fa-solid fa-users-slash"></i>
            </div>

            <strong>
                @if ($recherche !== '')
                    Aucun client ne correspond à « {{ $recherche }} »
                @else
                    Aucun client pour le moment dans cette agence
                @endif
            </strong>

            <span>
                @if ($recherche !== '')
                    La recherche porte sur le nom exact (ou la raison sociale exacte) du dossier — vérifiez l'orthographe.
                @else
                    Les nouveaux dossiers apparaîtront ici dès qu'un caissier les aura créés.
                @endif
            </span>

        </div>

    @endif

    @if ($clients->hasPages())

        <div class="clients-pagination">
            {{ $clients->links() }}
        </div>

    @endif

</div>

@endsection
