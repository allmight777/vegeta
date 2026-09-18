@extends('layouts.agent')

@section('titre')




@section('contenu')

@include('agent.clients._styles')


<div class="clients-page">


    {{-- =====================================================
         BARRE D'ACTIONS
    ====================================================== --}}

    <div class="clients-toolbar">

        <div class="clients-toolbar-left">

            <a
                href="{{ route('agent.clients.index') }}"
                class="filter-link {{ !request('a_completer') ? 'active' : '' }}"
            >

                <i class="fa-solid fa-list"></i>

                Tous les clients

            </a>


            <a
                href="{{ route('agent.clients.index', ['a_completer' => 1]) }}"
                class="filter-link {{ request('a_completer') ? 'active' : '' }}"
            >

                <i class="fa-solid fa-user-clock"></i>

                À compléter

            </a>


            <span class="clients-count">

                <i class="fa-solid fa-users"></i>

                {{ $clients->total() }} dossier{{ $clients->total() > 1 ? 's' : '' }}

            </span>

            <form method="GET" action="{{ route('agent.clients.index') }}" class="clients-toolbar-search">

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


        <div style="display: flex; gap: 10px;">

            <a
                href="{{ route('agent.clients.import.creer') }}"
                class="btn-secondary"
            >

                <i class="fa-solid fa-file-arrow-up"></i>

                Uploader des fiches

            </a>

            <a
                href="{{ route('agent.clients.creer') }}"
                class="btn-primary"
            >

                <i class="fa-solid fa-user-plus"></i>

                Nouveau client

            </a>

        </div>

    </div>



    {{-- =====================================================
         LISTE
    ====================================================== --}}

    @if ($clients->count())

        <div class="clients-list">

            @foreach ($clients as $client)

                @php

                    $estMorale =
                        $client->type->value === 'personne_morale';

                    $nom =
                        $estMorale
                            ? $client->personneMorale?->raison_sociale
                            : trim(
                                ($client->personnePhysique?->prenoms ?? '')
                                . ' '
                                . ($client->personnePhysique?->nom ?? '')
                            );

                    $score =
                        (int) $client->score_completude_kyc;

                    $scoreClass =
                        $score >= 80
                            ? 'high'
                            : ($score >= 50 ? 'mid' : 'low');

                    $scoreIcon =
                        $score >= 80
                            ? 'fa-circle-check'
                            : ($score >= 50
                                ? 'fa-circle-half-stroke'
                                : 'fa-triangle-exclamation');

                @endphp


                <a
                    href="{{ route('agent.clients.completer', $client) }}"
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
                    Aucun client pour le moment
                @endif
            </strong>

            <span>
                @if ($recherche !== '')
                    La recherche porte sur le nom exact (ou la raison sociale exacte) du dossier — vérifiez l'orthographe.
                @else
                    Commencez par créer un nouveau dossier client
                    ou modifiez vos filtres de recherche.
                @endif
            </span>

            <a
                href="{{ route('agent.clients.creer') }}"
                class="btn-primary"
                style="margin-top: 8px;"
            >

                <i class="fa-solid fa-user-plus"></i>

                Créer un client

            </a>

        </div>

    @endif



    {{-- =====================================================
         PAGINATION
    ====================================================== --}}

    @if ($clients->hasPages())

        <div class="clients-pagination">

            {{ $clients->links() }}

        </div>

    @endif


</div>

@endsection
