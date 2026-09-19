@extends('layouts.agent')



@section('contenu')

@php
    $totalClients = $clientsACompleter->count();

    $tauxMoyen = $totalClients > 0
        ? round($clientsACompleter->avg('score_completude_kyc'))
        : 100;

    $nomAgent = Auth::guard('agent')->user()->nom
        ?? Auth::guard('agent')->user()->name
        ?? 'Agent';

    $dateJour = now()->translatedFormat('l');
    $numeroJour = now()->format('d');
    $moisAnnee = now()->translatedFormat('F Y');
@endphp

<style>

    /* =========================================================
       DASHBOARD PAGE
    ========================================================= */

    .dashboard-page {

        --yellow: #F0E535;
        --yellow-dark: #D8CD18;
        --yellow-soft: rgba(240, 229, 53, 0.12);
        --yellow-light: #FFFDE7;

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --dark: #2C343D;
        --dark-soft: #3C4650;

        --background: #F8FAFC;
        --white: #FFFFFF;

        --text: #1E293B;
        --muted: #64748B;
        --muted-light: #94A3B8;

        --border: #E8EDF2;

        --danger: #DC2626;
        --danger-soft: rgba(220, 38, 38, 0.08);

        --shadow-sm:
            0 4px 15px rgba(44, 52, 61, 0.04);

        --shadow:
            0 12px 35px rgba(44, 52, 61, 0.07);

        --shadow-lg:
            0 20px 55px rgba(44, 52, 61, 0.10);

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 18px;
    }


    /* =========================================================
       TOPBAR
    ========================================================= */

    .dashboard-topbar {

        min-height: 55px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;
    }


    .dashboard-greeting {

        display: flex;

        align-items: center;

        gap: 12px;
    }


    .agent-avatar {

        width: 46px;

        height: 46px;

        border-radius: 50%;

        object-fit: cover;

        border: 3px solid #FFFFFF;

        box-shadow:
            0 4px 15px rgba(44, 52, 61, 0.12);
    }


    .greeting-text {

        display: flex;

        flex-direction: column;
    }


    .greeting-text span {

        font-size: 0.68rem;

        color: var(--muted);

        font-weight: 600;

        margin-bottom: 2px;
    }


    .greeting-text strong {

        font-size: 1rem;

        color: var(--dark);

        font-weight: 800;
    }


    .topbar-actions {

        display: flex;

        align-items: center;

        gap: 10px;
    }


    .search-box {

        width: 230px;

        height: 40px;

        display: flex;

        align-items: center;

        gap: 9px;

        padding: 0 13px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 12px;

        color: var(--muted-light);

        box-shadow: var(--shadow-sm);
    }


    .search-box i {

        font-size: 0.78rem;
    }


    .search-box input {

        border: none;

        outline: none;

        width: 100%;

        background: transparent;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.7rem;
    }


    .search-box input::placeholder {

        color: var(--muted-light);
    }


    /* =========================================================
       HERO
    ========================================================= */

    .dashboard-hero {

        min-height: 170px;

        border-radius: 24px;

        position: relative;

        overflow: hidden;

        display: grid;

        grid-template-columns: minmax(0, 1fr) 160px;

        gap: 20px;

        padding: 28px 30px;

        background:
            linear-gradient(
                135deg,
                #2C343D 0%,
                #3A4650 65%,
                #303A43 100%
            );

        box-shadow: var(--shadow-lg);
    }


    .dashboard-hero::before {

        content: "";

        position: absolute;

        width: 310px;

        height: 310px;

        right: -100px;

        top: -170px;

        border-radius: 50%;

        background: var(--yellow);

        opacity: 0.10;
    }


    .dashboard-hero::after {

        content: "";

        position: absolute;

        width: 160px;

        height: 160px;

        left: 40%;

        bottom: -120px;

        border-radius: 50%;

        background: var(--green);

        opacity: 0.06;
    }


    .hero-content {

        position: relative;

        z-index: 3;

        align-self: center;
    }


    .hero-tag {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 6px 10px;

        border-radius: 999px;

        background: rgba(240, 229, 53, 0.12);

        border:
            1px solid
            rgba(240, 229, 53, 0.25);

        color: #FFFDE7;

        font-size: 0.61rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.7px;

        margin-bottom: 11px;
    }


    .hero-tag i {

        color: var(--yellow);
    }


    .hero-content h1 {

        color: #FFFFFF;

        font-size: clamp(1.3rem, 2.1vw, 1.85rem);

        font-weight: 800;

        line-height: 1.15;

        letter-spacing: -0.7px;

        margin-bottom: 8px;
    }


    .hero-content h1 span {

        color: var(--yellow);
    }


    .hero-content p {

        color: rgba(255,255,255,0.62);

        font-size: 0.73rem;

        line-height: 1.5;

        max-width: 500px;
    }


    /* =========================================================
       DATE CARD
    ========================================================= */

    .hero-date {

        align-self: center;

        justify-self: end;

        width: 125px;

        height: 125px;

        position: relative;

        z-index: 4;

        display: flex;

        flex-direction: column;

        align-items: center;

        justify-content: center;

        text-align: center;

        color: var(--dark);

        background: var(--yellow);

        border-radius:
            38% 62% 58% 42% /
            45% 38% 62% 55%;

        box-shadow:
            0 15px 35px rgba(240, 229, 53, 0.20);
    }


    .hero-date .day-name {

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        opacity: 0.65;

        margin-bottom: 2px;
    }


    .hero-date .day-number {

        font-size: 2.35rem;

        font-weight: 900;

        line-height: 1;
    }


    .hero-date .month {

        font-size: 0.61rem;

        font-weight: 700;

        margin-top: 4px;
    }


    .hero-date .date-icon {

        position: absolute;

        width: 29px;

        height: 29px;

        right: -5px;

        bottom: 4px;

        border-radius: 50%;

        background: #FFFFFF;

        display: flex;

        align-items: center;

        justify-content: center;

        color: var(--dark);

        font-size: 0.7rem;

        box-shadow:
            0 4px 10px rgba(44,52,61,0.12);
    }


    /* =========================================================
       STATS
    ========================================================= */

    .stats-panel {

        background: #FFFFFF;

        border-radius: 20px;

        border: 1px solid var(--border);

        display: grid;

        grid-template-columns:
            repeat(4, minmax(0, 1fr));

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        margin-top: -4px;

        position: relative;

        z-index: 5;
    }


    .stat-item {

        padding: 17px 18px;

        display: flex;

        align-items: center;

        gap: 12px;

        position: relative;
    }


    .stat-item:not(:last-child)::after {

        content: "";

        position: absolute;

        width: 1px;

        height: 38px;

        right: 0;

        top: 50%;

        transform: translateY(-50%);

        background: var(--border);
    }


    .stat-icon {

        width: 39px;

        height: 39px;

        flex-shrink: 0;

        border-radius: 12px;

        display: flex;

        align-items: center;

        justify-content: center;

        background: var(--yellow-soft);

        color: var(--dark);

        font-size: 0.9rem;
    }


    .stat-item.success .stat-icon {

        background: var(--green-soft);

        color: var(--green);
    }


    .stat-info {

        min-width: 0;
    }


    .stat-info strong {

        display: block;

        color: var(--dark);

        font-size: 1.05rem;

        font-weight: 800;

        line-height: 1.1;

        margin-bottom: 3px;
    }


    .stat-info span {

        display: block;

        color: var(--muted);

        font-size: 0.61rem;

        font-weight: 600;

        white-space: nowrap;
    }


    /* =========================================================
       GRILLE
    ========================================================= */

    .dashboard-grid {

        display: grid;

        grid-template-columns:
            minmax(0, 1.65fr)
            minmax(260px, 0.85fr);

        gap: 18px;
    }


    /* =========================================================
       CARD
    ========================================================= */

    .dashboard-card {

        background: #FFFFFF;

        border:
            1px solid
            var(--border);

        border-radius: 20px;

        padding: 20px;

        box-shadow: var(--shadow-sm);

        min-width: 0;
    }


    .card-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        margin-bottom: 17px;
    }


    .card-title {

        display: flex;

        flex-direction: column;
    }


    .card-title h3 {

        color: var(--dark);

        font-size: 0.85rem;

        font-weight: 800;

        margin: 0;
    }


    .card-title span {

        color: var(--muted-light);

        font-size: 0.58rem;

        margin-top: 3px;

        font-weight: 600;
    }


    .card-tag {

        padding: 6px 10px;

        border-radius: 999px;

        background: var(--background);

        border: 1px solid var(--border);

        color: var(--muted);

        font-size: 0.59rem;

        font-weight: 700;

        white-space: nowrap;
    }


    /* =========================================================
       GRAPHIQUE
    ========================================================= */

    .chart-area {

        width: 100%;

        height: 210px;

        position: relative;
    }


    .chart-svg {

        width: 100%;

        height: 100%;

        display: block;

        overflow: visible;
    }


    .chart-labels {

        display: flex;

        justify-content: space-between;

        padding: 7px 4px 0;

        color: var(--muted-light);

        font-size: 0.58rem;

        font-weight: 600;
    }


    /* =========================================================
       JAUGE
    ========================================================= */

    .focus-card {

        display: flex;

        flex-direction: column;
    }


    .focus-content {

        flex: 1;

        display: flex;

        flex-direction: column;

        align-items: center;

        justify-content: center;

        text-align: center;

        min-height: 210px;
    }


    .gauge {

        width: 145px;

        height: 145px;

        border-radius: 50%;

        background:
            conic-gradient(
                var(--green)
                calc(var(--percentage) * 1%),
                #EEF2F4 0
            );

        display: flex;

        align-items: center;

        justify-content: center;

        position: relative;

        margin-bottom: 14px;

        box-shadow:
            0 8px 22px
            rgba(48,195,26,0.10);
    }


    .gauge::after {

        content: "";

        position: absolute;

        inset: 8px;

        border-radius: 50%;

        background: #FFFFFF;
    }


    .gauge-inner {

        position: relative;

        z-index: 2;

        display: flex;

        flex-direction: column;

        align-items: center;
    }


    .gauge-inner strong {

        color: var(--dark);

        font-size: 1.65rem;

        font-weight: 900;

        line-height: 1;
    }


    .gauge-inner span {

        color: var(--muted);

        font-size: 0.57rem;

        font-weight: 700;

        margin-top: 5px;

        text-transform: uppercase;

        letter-spacing: 0.5px;
    }


    .focus-caption {

        max-width: 230px;

        color: var(--muted);

        font-size: 0.65rem;

        line-height: 1.5;

        font-weight: 600;
    }


    /* =========================================================
       LISTE CLIENTS
    ========================================================= */

    .client-list {

        display: flex;

        flex-direction: column;

        gap: 8px;
    }


    .client-row {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 10px;

        min-height: 50px;

        padding: 7px 10px;

        background: #FFFFFF;

        border:
            1px solid
            var(--border);

        border-radius: 12px;

        text-decoration: none;

        transition:
            transform 0.18s ease,
            border-color 0.18s ease,
            background 0.18s ease,
            box-shadow 0.18s ease;
    }


    .client-row:hover {

        transform: translateX(3px);

        background: var(--yellow-light);

        border-color:
            rgba(240,229,53,0.55);

        box-shadow:
            0 5px 15px rgba(44,52,61,0.04);
    }


    .client-info {

        display: flex;

        align-items: center;

        gap: 10px;

        min-width: 0;
    }


    .client-avatar {

        width: 34px;

        height: 34px;

        border-radius: 10px;

        background:
            var(--yellow-soft);

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        flex-shrink: 0;

        font-size: 0.78rem;
    }


    .client-name {

        min-width: 0;
    }


    .client-name strong {

        display: block;

        color: var(--dark);

        font-size: 0.68rem;

        font-weight: 800;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .client-name span {

        display: block;

        color: var(--muted-light);

        font-size: 0.55rem;

        margin-top: 2px;

        font-weight: 600;
    }


    .completion-badge {

        padding: 5px 9px;

        border-radius: 999px;

        font-size: 0.56rem;

        font-weight: 800;

        white-space: nowrap;
    }


    .completion-badge.high {

        color: var(--green);

        background: var(--green-soft);
    }


    .completion-badge.mid {

        color: #9B8D00;

        background: var(--yellow-soft);
    }


    .completion-badge.low {

        color: var(--danger);

        background: var(--danger-soft);
    }


    .empty-state {

        padding: 35px 15px;

        text-align: center;

        color: var(--muted);

        font-size: 0.7rem;

        font-weight: 600;
    }


    .empty-state i {

        display: block;

        font-size: 1.6rem;

        margin-bottom: 8px;

        color: var(--green);
    }


    /* =========================================================
       ACTIONS RAPIDES
    ========================================================= */

    .quick-actions {

        display: grid;

        grid-template-columns:
            repeat(4, minmax(0, 1fr));

        gap: 9px;
    }


    .quick-action {

        min-height: 95px;

        background: var(--background);

        border:
            1px solid
            var(--border);

        border-radius: 13px;

        text-decoration: none;

        display: flex;

        flex-direction: column;

        align-items: center;

        justify-content: center;

        gap: 8px;

        color: var(--dark);

        transition:
            transform 0.2s ease,
            background 0.2s ease,
            border-color 0.2s ease;
    }


    .quick-action:hover {

        background: var(--yellow);

        border-color: var(--yellow);

        transform: translateY(-3px);
    }


    .quick-action i {

        font-size: 0.95rem;
    }


    .quick-action span {

        font-size: 0.57rem;

        font-weight: 800;

        text-align: center;
    }


    /* =========================================================
       BAS DE PAGE
    ========================================================= */

    .dashboard-footer {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        padding: 4px 4px 0;

        color: var(--muted-light);

        font-size: 0.58rem;

        font-weight: 600;
    }


    .system-status {

        display: inline-flex;

        align-items: center;

        gap: 6px;
    }


    .system-status-dot {

        width: 7px;

        height: 7px;

        border-radius: 50%;

        background: var(--green);

        box-shadow:
            0 0 0 3px
            rgba(48,195,26,0.08);
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1180px) {

        .dashboard-grid {

            grid-template-columns: 1fr;
        }

        .focus-card {

            min-height: 280px;
        }

        .quick-actions {

            grid-template-columns: repeat(4, 1fr);
        }
    }


    @media (max-width: 850px) {

        .dashboard-topbar {

            flex-wrap: wrap;
        }

        .search-box {

            width: min(230px, 60vw);
        }

        .dashboard-hero {

            grid-template-columns: 1fr;

            min-height: auto;

            padding: 23px;
        }

        .hero-date {

            justify-self: start;

            width: 105px;

            height: 105px;
        }

        .stats-panel {

            grid-template-columns: repeat(2, 1fr);
        }

        .stat-item:nth-child(2)::after {

            display: none;
        }

        .stat-item:nth-child(3) {

            border-top: 1px solid var(--border);
        }

        .stat-item:nth-child(4) {

            border-top: 1px solid var(--border);
        }

        .quick-actions {

            grid-template-columns: repeat(2, 1fr);
        }
    }


    @media (max-width: 560px) {

        .dashboard-topbar {

            min-height: auto;
        }

        .dashboard-greeting {

            width: 100%;
        }

        .topbar-actions {

            width: 100%;
        }

        .search-box {

            flex: 1;

            width: auto;
        }

        .dashboard-hero {

            border-radius: 18px;

            padding: 20px;
        }

        .hero-content h1 {

            font-size: 1.35rem;
        }

        .stats-panel {

            grid-template-columns: 1fr;
        }

        .stat-item {

            border-bottom: 1px solid var(--border);
        }

        .stat-item::after {

            display: none !important;
        }

        .stat-item:last-child {

            border-bottom: none;
        }

        .dashboard-card {

            padding: 15px;

            border-radius: 17px;
        }

        .chart-area {

            height: 175px;
        }

        .quick-actions {

            grid-template-columns: repeat(2, 1fr);
        }

        .dashboard-footer {

            flex-direction: column;

            align-items: flex-start;
        }
    }

</style>


<div class="dashboard-page">


    {{-- =================================================
         TOPBAR
    ================================================== --}}

    <div class="dashboard-topbar">

        <div class="dashboard-greeting">

            <img
                src="{{ asset('images/fececam.jpg') }}"
                alt="Agent"
                class="agent-avatar"
            >

            <div class="greeting-text">

                <span>
                    Bonjour,
                </span>

                <strong>
                    {{ $nomAgent }}
                </strong>

            </div>

        </div>


        <div class="topbar-actions">

            <form method="GET" action="{{ route('agent.clients.index') }}" class="search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    name="q"
                    placeholder="Rechercher un dossier..."
                    aria-label="Rechercher un dossier par nom exact"
                >

            </form>

        </div>

    </div>



    {{-- =================================================
         HERO
    ================================================== --}}

    <section class="dashboard-hero">

        <div class="hero-content">

            <div class="hero-tag">

                <i class="fa-solid fa-shield-halved"></i>

                Espace Agent

            </div>


            <h1>
                Bonjour,
                <span>{{ $nomAgent }}</span>
            </h1>


            <p>
                Voici l'activité de votre caisse et
                l'état des opérations biométriques
                pour aujourd'hui.
            </p>

        </div>


        <div class="hero-date">

            <div class="day-name">
                {{ $dateJour }}
            </div>

            <div class="day-number">
                {{ $numeroJour }}
            </div>

            <div class="month">
                {{ $moisAnnee }}
            </div>

            <div class="date-icon">
                <i class="fa-regular fa-calendar"></i>
            </div>

        </div>

    </section>



    {{-- =================================================
         STATISTIQUES
    ================================================== --}}

    <section class="stats-panel">


        <div class="stat-item">

            <div class="stat-icon">
                <i class="fa-solid fa-fingerprint"></i>
            </div>

            <div class="stat-info">

                <strong>
                    {{ $operationsDuJour ?? 0 }}
                </strong>

                <span>
                    Opérations du jour
                </span>

            </div>

        </div>


        <div class="stat-item">

            <div class="stat-icon">
                <i class="fa-solid fa-user-clock"></i>
            </div>

            <div class="stat-info">

                <strong>
                    {{ $totalClients }}
                </strong>

                <span>
                    Clients à compléter
                </span>

            </div>

        </div>


        <div class="stat-item success">

            <div class="stat-icon">
                <i class="fa-solid fa-chart-simple"></i>
            </div>

            <div class="stat-info">

                <strong>
                    {{ $tauxMoyen }}%
                </strong>

                <span>
                    Complétude KYC
                </span>

            </div>

        </div>


        <div class="stat-item">

            <div class="stat-icon">
                <i class="fa-solid fa-clock"></i>
            </div>

            <div class="stat-info">

                <strong>
                    {{ now()->format('H:i') }}
                </strong>

                <span>
                    Dernière synchronisation
                </span>

            </div>

        </div>

    </section>



    {{-- =================================================
         GRAPHIQUE + FOCUS
    ================================================== --}}

    <div class="dashboard-grid">


        <section class="dashboard-card">

            <div class="card-header">

                <div class="card-title">

                    <h3>
                        Activité des opérations
                    </h3>

                    <span>
                        Évolution des 7 derniers jours
                    </span>

                </div>

                <span class="card-tag">
                    Cette semaine
                </span>

            </div>


            <div class="chart-area">

                <svg
                    id="operationsChart"
                    class="chart-svg"
                    viewBox="0 0 700 210"
                    preserveAspectRatio="none"
                ></svg>

            </div>


            <div
                id="chartLabels"
                class="chart-labels"
            ></div>

        </section>



        <section class="dashboard-card focus-card">

            <div class="card-header">

                <div class="card-title">

                    <h3>
                        Focus du jour
                    </h3>

                    <span>
                        Performance KYC
                    </span>

                </div>

                <span class="card-tag">
                    {{ $tauxMoyen }}%
                </span>

            </div>


            <div class="focus-content">

                <div
                    class="gauge"
                    style="--percentage: {{ $tauxMoyen }};"
                >

                    <div class="gauge-inner">

                        <strong>
                            {{ $tauxMoyen }}%
                        </strong>

                        <span>
                            Complétude
                        </span>

                    </div>

                </div>


                <p class="focus-caption">

                    Taux moyen de complétude KYC
                    des dossiers actuellement
                    pris en charge.

                </p>

            </div>

        </section>

    </div>



    {{-- =================================================
         CLIENTS + ACTIONS
    ================================================== --}}

    <div class="dashboard-grid">


        <section class="dashboard-card">

            <div class="card-header">

                <div class="card-title">

                    <h3>
                        Clients à compléter
                    </h3>

                    <span>
                        Dossiers nécessitant une intervention
                    </span>

                </div>

                <span class="card-tag">
                    {{ $totalClients }} en attente
                </span>

            </div>


            <div class="client-list">

                @forelse ($clientsACompleter as $client)

                    @php

                        $score =
                            $client->score_completude_kyc;

                        $badgeClass =
                            $score >= 80
                                ? 'high'
                                : ($score >= 50
                                    ? 'mid'
                                    : 'low');

                    @endphp


                    <a
                        href="{{ route('agent.clients.completer', $client) }}"
                        class="client-row"
                    >

                        <div class="client-info">

                            <div class="client-avatar">

                                <i class="fa-solid fa-user"></i>

                            </div>


                            <div class="client-name">

                                <strong>
                                    {{ $client->nomAffichage() }}
                                </strong>

                                <span>
                                    Dossier KYC à compléter
                                </span>

                            </div>

                        </div>


                        <span
                            class="completion-badge {{ $badgeClass }}"
                        >
                            {{ $score }}%
                        </span>

                    </a>

                @empty

                    <div class="empty-state">

                        <i class="fa-solid fa-circle-check"></i>

                        Aucun dossier en attente.

                    </div>

                @endforelse

            </div>

        </section>



        <section class="dashboard-card">

            <div class="card-header">

                <div class="card-title">

                    <h3>
                        Actions rapides
                    </h3>

                    <span>
                        Accès rapide aux opérations
                    </span>

                </div>

            </div>


            <div class="quick-actions">


                <a
                    href="{{ Route::has('agent.clients.creer') ? route('agent.clients.creer') : url()->current() }}"
                    class="quick-action"
                >

                    <i class="fa-solid fa-user-plus"></i>

                    <span>
                        Nouveau client
                    </span>

                </a>


                <a
                    href="{{ Route::has('agent.operations.creer') ? route('agent.operations.creer') : url()->current() }}"
                    class="quick-action"
                >

                    <i class="fa-solid fa-fingerprint"></i>

                    <span>
                        Nouvelle empreinte
                    </span>

                </a>


                <a
                    href="{{ route('agent.operations.historique') }}"
                    class="quick-action"
                >

                    <i class="fa-solid fa-clock-rotate-left"></i>

                    <span>
                        Historique
                    </span>

                </a>


                <a
                    href="{{ route('agent.rapports.index') }}"
                    class="quick-action"
                >

                    <i class="fa-solid fa-file-export"></i>

                    <span>
                        Exporter
                    </span>

                </a>


            </div>

        </section>

    </div>



    {{-- =================================================
         FOOTER
    ================================================== --}}

    <div class="dashboard-footer">

        <span>
            {{ $identite['nom_systeme'] }} — Tableau de bord agent
        </span>


        <span class="system-status">

            <span class="system-status-dot"></span>

            Système opérationnel

        </span>

    </div>


</div>



<script>

document.addEventListener('DOMContentLoaded', function () {

    /* =========================================================
       DONNÉES
    ========================================================= */

    const data = @json($operationsSemaine);

    const labels = @json($joursSemaine);

    const svg =
        document.getElementById('operationsChart');

    const labelsContainer =
        document.getElementById('chartLabels');


    if (!svg || !labelsContainer || !data.length) {
        return;
    }


    /* =========================================================
       DIMENSIONS
    ========================================================= */

    const width = 700;

    const height = 210;

    const paddingX = 12;

    const paddingY = 18;


    const maxValue =
        Math.max(...data, 1);


    const chartMax =
        maxValue * 1.20;


    const stepX =
        (width - (paddingX * 2))
        /
        Math.max(data.length - 1, 1);


    /* =========================================================
       POINTS
    ========================================================= */

    const points = data.map(function(value, index) {

        const x =
            paddingX +
            (index * stepX);


        const y =
            height -
            paddingY -
            (
                (value / chartMax)
                *
                (height - paddingY * 2)
            );


        return {
            x: x,
            y: y,
            value: value
        };

    });


    /* =========================================================
       COURBE LISSÉE
    ========================================================= */

    let linePath = '';

    points.forEach(function(point, index) {

        if (index === 0) {

            linePath =
                `M ${point.x} ${point.y}`;

            return;
        }


        const previous =
            points[index - 1];


        const controlX =
            (previous.x + point.x) / 2;


        linePath +=
            ` C ${controlX} ${previous.y},
               ${controlX} ${point.y},
               ${point.x} ${point.y}`;

    });


    /* =========================================================
       ZONE SOUS LA COURBE
    ========================================================= */

    const firstPoint =
        points[0];

    const lastPoint =
        points[points.length - 1];


    const areaPath =
        linePath +
        ` L ${lastPoint.x} ${height}
          L ${firstPoint.x} ${height}
          Z`;


    /* =========================================================
       GRILLE
    ========================================================= */

    let gridLines = '';

    [25, 50, 75, 100].forEach(function(percent) {

        const y =
            height -
            paddingY -
            (
                (percent / 100)
                *
                (height - paddingY * 2)
            );


        gridLines += `
            <line
                x1="${paddingX}"
                y1="${y}"
                x2="${width - paddingX}"
                y2="${y}"
                stroke="#EEF2F5"
                stroke-width="1"
                stroke-dasharray="4 5"
            />
        `;

    });


    /* =========================================================
       SVG
    ========================================================= */

    svg.innerHTML = `

        <defs>

            <linearGradient
                id="chartGradient"
                x1="0"
                y1="0"
                x2="0"
                y2="1"
            >

                <stop
                    offset="0%"
                    stop-color="#F0E535"
                    stop-opacity="0.35"
                />

                <stop
                    offset="100%"
                    stop-color="#F0E535"
                    stop-opacity="0"
                />

            </linearGradient>

        </defs>


        ${gridLines}


        <path
            d="${areaPath}"
            fill="url(#chartGradient)"
        />


        <path
            d="${linePath}"
            fill="none"
            stroke="#2C343D"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
        />


        ${points.map(function(point, index) {

            const jour = labels[index] ?? '';
            const libelle = `${jour} : ${point.value} opération${point.value > 1 ? 's' : ''}`;

            return `

                <g>

                    <title>${libelle}</title>

                    <circle
                        cx="${point.x}"
                        cy="${point.y}"
                        r="10"
                        fill="transparent"
                    />

                    <circle
                        cx="${point.x}"
                        cy="${point.y}"
                        r="6"
                        fill="#FFFFFF"
                        stroke="#F0E535"
                        stroke-width="3"
                    />

                    <circle
                        cx="${point.x}"
                        cy="${point.y}"
                        r="2.5"
                        fill="#30C31A"
                    />

                </g>

            `;

        }).join('')}

    `;


    /* =========================================================
       LABELS
    ========================================================= */

    labelsContainer.innerHTML =
        labels.map(function(label) {

            return `<span>${label}</span>`;

        }).join('');

});

</script>

@endsection
