@extends('layouts.admin')


@section('sous-titre', 'Vue synthétique de l\'activité et de la configuration de la plateforme.')


@section('contenu')

@php
    $dateJour = now()->translatedFormat('l');
    $numeroJour = now()->format('d');
    $moisAnnee = now()->translatedFormat('F Y');

    $nomAdmin = auth('admin')->user()->nom
        ?? auth('admin')->user()->name
        ?? 'Administrateur';

    $roleAdmin = auth('admin')->user()->estAdminPlateforme()
        ? 'Admin plateforme'
        : 'Admin réseau';
@endphp

<style>

    /* =========================================================
       DASHBOARD ADMIN
    ========================================================= */

    .admin-dashboard {

        --yellow: #F0E535;
        --yellow-soft: rgba(240, 229, 53, 0.12);
        --yellow-light: #FFFDE7;

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --dark: #2C343D;

        --text: #1E293B;
        --muted: #64748B;
        --muted-light: #94A3B8;

        --border: #E8EDF2;
        --white: #FFFFFF;
        --background: #F8FAFC;

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

    .admin-topbar {

        min-height: 55px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        flex-wrap: wrap;
    }


    .admin-greeting {

        display: flex;

        align-items: center;

        gap: 12px;
    }


    .admin-avatar {

        width: 46px;

        height: 46px;

        border-radius: 50%;

        background: var(--yellow-soft);

        border: 3px solid #FFFFFF;

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1rem;

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


    .notification-button {

        width: 40px;

        height: 40px;

        border-radius: 12px;

        border: 1px solid var(--border);

        background: #FFFFFF;

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        cursor: pointer;

        box-shadow: var(--shadow-sm);

        position: relative;
    }


    .notification-button::after {

        content: "";

        width: 6px;

        height: 6px;

        border-radius: 50%;

        background: var(--green);

        position: absolute;

        top: 8px;

        right: 8px;

        border: 1px solid #FFFFFF;
    }


    /* =========================================================
       HERO
    ========================================================= */

    .admin-hero {

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


    .admin-hero::before {

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


    .admin-hero::after {

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

        border: 1px solid rgba(240, 229, 53, 0.25);

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

        margin: 0 0 8px;
    }


    .hero-content h1 span {

        color: var(--yellow);
    }


    .hero-content p {

        color: rgba(255,255,255,0.62);

        font-size: 0.73rem;

        line-height: 1.5;

        max-width: 500px;

        margin: 0;
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

    .admin-grid {

        display: grid;

        grid-template-columns:
            minmax(0, 1.65fr)
            minmax(260px, 0.85fr);

        gap: 18px;
    }


    /* =========================================================
       CARD
    ========================================================= */

    .admin-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

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
       LISTE RÉSEAUX
    ========================================================= */

    .network-list {

        display: flex;

        flex-direction: column;

        gap: 8px;
    }


    .network-row {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 10px;

        min-height: 50px;

        padding: 7px 10px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 12px;

        text-decoration: none;

        transition:
            transform 0.18s ease,
            border-color 0.18s ease,
            background 0.18s ease,
            box-shadow 0.18s ease;
    }


    .network-row:hover {

        transform: translateX(3px);

        background: var(--yellow-light);

        border-color: rgba(240,229,53,0.55);

        box-shadow:
            0 5px 15px rgba(44,52,61,0.04);
    }


    .network-info {

        display: flex;

        align-items: center;

        gap: 10px;

        min-width: 0;
    }


    .network-avatar {

        width: 34px;

        height: 34px;

        border-radius: 10px;

        background: var(--yellow-soft);

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        flex-shrink: 0;

        font-size: 0.78rem;
    }


    .network-name {

        min-width: 0;
    }


    .network-name strong {

        display: block;

        color: var(--dark);

        font-size: 0.68rem;

        font-weight: 800;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .network-name span {

        display: block;

        color: var(--muted-light);

        font-size: 0.55rem;

        margin-top: 2px;

        font-weight: 600;
    }


    .network-count {

        padding: 5px 9px;

        border-radius: 999px;

        font-size: 0.56rem;

        font-weight: 800;

        color: var(--dark);

        background: var(--yellow-soft);

        white-space: nowrap;
    }


    /* =========================================================
       ACTIONS RAPIDES
    ========================================================= */

    .quick-actions {

        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 9px;
    }


    .quick-action {

        min-height: 95px;

        background: var(--background);

        border: 1px solid var(--border);

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

        padding: 0 6px;
    }


    /* =========================================================
       BAS DE PAGE
    ========================================================= */

    .admin-footer {

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

        .admin-grid {

            grid-template-columns: 1fr;
        }

        .quick-actions {

            grid-template-columns: repeat(4, 1fr);
        }
    }


    @media (max-width: 850px) {

        .admin-topbar {

            flex-wrap: wrap;
        }

        .search-box {

            width: min(230px, 60vw);
        }

        .admin-hero {

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

        .admin-topbar {

            min-height: auto;
        }

        .admin-greeting {

            width: 100%;
        }

        .topbar-actions {

            width: 100%;
        }

        .search-box {

            flex: 1;

            width: auto;
        }

        .admin-hero {

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

        .admin-card {

            padding: 15px;

            border-radius: 17px;
        }

        .quick-actions {

            grid-template-columns: repeat(2, 1fr);
        }

        .admin-footer {

            flex-direction: column;

            align-items: flex-start;
        }
    }

</style>


<div class="admin-dashboard">


    {{-- =================================================
         TOPBAR
    ================================================== --}}

    <div class="admin-topbar">

        <div class="admin-greeting">

            <div class="admin-avatar">

                <i class="fa-solid fa-user-shield"></i>

            </div>


            <div class="greeting-text">

                <span>
                    Bonjour,
                </span>

                <strong>
                    {{ $nomAdmin }}
                </strong>

            </div>

        </div>


        <div class="topbar-actions">

            <div class="search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    placeholder="Rechercher..."
                    aria-label="Rechercher"
                >

            </div>


            <button
                type="button"
                class="notification-button"
                aria-label="Notifications"
            >
                <i class="fa-regular fa-bell"></i>
            </button>

        </div>

    </div>



    {{-- =================================================
         HERO
    ================================================== --}}

    <section class="admin-hero">

        <div class="hero-content">

            <div class="hero-tag">

                <i class="fa-solid fa-user-shield"></i>

                Administration · {{ $roleAdmin }}

            </div>


            <h1>
                Bonjour,
                <span>{{ $nomAdmin }}</span>
            </h1>


            <p>
                Vue d'ensemble de la configuration et de
                l'activité de la plateforme CIF-Empreinte.
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
                <i class="fa-solid fa-network-wired"></i>
            </div>

            <div class="stat-info">

                <strong>
                    {{ $nombreReseaux }}
                </strong>

                <span>
                    Réseaux
                </span>

            </div>

        </div>


        <div class="stat-item">

            <div class="stat-icon">
                <i class="fa-solid fa-building"></i>
            </div>

            <div class="stat-info">

                <strong>
                    {{ $nombreAgences }}
                </strong>

                <span>
                    Agences
                </span>

            </div>

        </div>


        <div class="stat-item">

            <div class="stat-icon">
                <i class="fa-solid fa-user-shield"></i>
            </div>

            <div class="stat-info">

                <strong>
                    {{ $nombreAdmins }}
                </strong>

                <span>
                    Administrateurs
                </span>

            </div>

        </div>


        <div class="stat-item success">

            <div class="stat-icon">
                <i class="fa-solid fa-users"></i>
            </div>

            <div class="stat-info">

                <strong>
                    {{ $nombreAgents }}
                </strong>

                <span>
                    Agents actifs
                </span>

            </div>

        </div>

    </section>



    {{-- =================================================
         RÉSEAUX + ACTIONS
    ================================================== --}}

    <div class="admin-grid">


        <section class="admin-card">

            <div class="card-header">

                <div class="card-title">

                    <h3>
                        Répartition des agences
                    </h3>

                    <span>
                        Vue synthétique par réseau
                    </span>

                </div>

                <span class="card-tag">
                    {{ $nombreReseaux }} réseau{{ $nombreReseaux > 1 ? 'x' : '' }}
                </span>

            </div>


            <div class="network-list">

                @forelse (($reseaux ?? []) as $reseau)

                    <a
                        href="{{ Route::has('admin.listes.index')
                            ? route('admin.listes.index', ['reseau' => $reseau->id])
                            : '#' }}"
                        class="network-row"
                    >

                        <div class="network-info">

                            <div class="network-avatar">

                                <i class="fa-solid fa-network-wired"></i>

                            </div>


                            <div class="network-name">

                                <strong>
                                    {{ $reseau->nom ?? 'Réseau ' . $reseau->id }}
                                </strong>

                                <span>
                                    {{ $reseau->agences_count ?? 0 }} agence(s)
                                </span>

                            </div>

                        </div>


                        <span class="network-count">
                            {{ $reseau->agents_count ?? 0 }} agent(s)
                        </span>

                    </a>

                @empty

                    <div class="network-row" style="justify-content: center; color: var(--muted);">

                        Aucun réseau configuré.

                    </div>

                @endforelse

            </div>

        </section>



        <section class="admin-card">

            <div class="card-header">

                <div class="card-title">

                    <h3>
                        Actions rapides
                    </h3>

                    <span>
                        Accès directs à l'administration
                    </span>

                </div>

            </div>


            <div class="quick-actions">


                <a
                    href="{{ Route::has('admin.listes.index')
                        ? route('admin.listes.index')
                        : url('/admin/listes') }}"
                    class="quick-action"
                >

                    <i class="fa-solid fa-list-check"></i>

                    <span>
                        Listes
                    </span>

                </a>


                <a
                    href="{{ Route::has('admin.regles-detection.index')
                        ? route('admin.regles-detection.index')
                        : url('/admin/regles-detection') }}"
                    class="quick-action"
                >

                    <i class="fa-solid fa-sliders"></i>

                    <span>
                        Règles
                    </span>

                </a>


        {{--         <a
                    href="{{ Route::has('admin.import.creer')
                        ? route('admin.import.creer')
                        : url('/admin/import') }}"
                    class="quick-action"
                >

                    <i class="fa-solid fa-file-import"></i>

                    <span>
                        Import
                    </span>

                </a>  --}}


                <a
                    href="{{ Route::has('admin.journal-audit.index')
                        ? route('admin.journal-audit.index')
                        : url('/admin/journal-audit') }}"
                    class="quick-action"
                >

                    <i class="fa-solid fa-clipboard-list"></i>

                    <span>
                        Journal d'audit
                    </span>

                </a>


            </div>

        </section>

    </div>



    {{-- =================================================
         FOOTER
    ================================================== --}}

    <div class="admin-footer">

        <span>
            CIF-Empreinte — Console d'administration
        </span>


        <span class="system-status">

            <span class="system-status-dot"></span>

            Système opérationnel

        </span>

    </div>


</div>

@endsection
