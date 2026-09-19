@extends('layouts.responsable')


@section('sous-titre', 'Supervision des dossiers, alertes et conformité de votre agence.')


@section('contenu')

@php
    $nomResponsable = auth('agent')->user()->nom
        ?? auth('agent')->user()->name
        ?? 'Responsable';

    $dateJour = now()->translatedFormat('l');
    $numeroJour = now()->format('d');
    $moisAnnee = now()->translatedFormat('F Y');

    $alertesParGravite = $alertesDuJour->groupBy(fn ($a) => $a->gravite->value ?? 'info')
        ->map->count()
        ->all();

    $alertesSemaine = $alertesSemaine ?? [0, 0, 0, 0, 0, 0, 0];
@endphp

<style>

    /* =========================================================
       DASHBOARD RESPONSABLE — MÊMES BLEUS QUE LE LAYOUT
       --cif-dark  : #2C343D (bleu nuit, base identité)
       --cif-accent: #2563EB (bleu royal, accent)
    ========================================================= */

    .resp-dashboard {

        --dark: #2C343D;
        --dark-soft: #3C4650;

        --accent: #2563EB;
        --accent-dark: #1D4ED8;
        --accent-soft: rgba(37, 99, 235, 0.10);
        --accent-light: #EFF6FF;

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --text: #1E293B;
        --muted: #64748B;
        --muted-light: #94A3B8;

        --border: #E8EDF2;
        --white: #FFFFFF;
        --background: #F8FAFC;

        --danger: #DC2626;
        --danger-soft: rgba(220, 38, 38, 0.08);

        --amber: #B45309;
        --amber-soft: rgba(245, 158, 11, 0.10);

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

    .resp-topbar {

        min-height: 55px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;
    }


    .resp-greeting {

        display: flex;

        align-items: center;

        gap: 12px;
    }


    .resp-avatar {

        width: 46px;

        height: 46px;

        border-radius: 50%;

        background: var(--accent-soft);

        border: 3px solid #FFFFFF;

        color: var(--accent);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1rem;

        box-shadow:
            0 4px 15px rgba(44, 52, 61, 0.12);
    }


    .resp-greeting-text {

        display: flex;

        flex-direction: column;
    }


    .resp-greeting-text span {

        font-size: 0.68rem;

        color: var(--muted);

        font-weight: 600;

        margin-bottom: 2px;
    }


    .resp-greeting-text strong {

        font-size: 1rem;

        color: var(--dark);

        font-weight: 800;
    }


    .resp-topbar-actions {

        display: flex;

        align-items: center;

        gap: 10px;
    }


    .resp-search {

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


    .resp-search i {

        font-size: 0.78rem;
    }


    .resp-search input {

        border: none;

        outline: none;

        width: 100%;

        background: transparent;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.7rem;
    }


    .resp-search input::placeholder {

        color: var(--muted-light);
    }


    /* =========================================================
       HERO — BLEU NUIT COMME LE LAYOUT
    ========================================================= */

    .resp-hero {

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
                #1E3A8A 0%,
                #1D4ED8 60%,
                #2563EB 100%
            );

        box-shadow: var(--shadow-lg);
    }


    .resp-hero::before {

        content: "";

        position: absolute;

        width: 310px;

        height: 310px;

        right: -100px;

        top: -170px;

        border-radius: 50%;

        background: #60A5FA;

        opacity: 0.15;
    }


    .resp-hero::after {

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


    .resp-hero-content {

        position: relative;

        z-index: 3;

        align-self: center;
    }


    .resp-hero-tag {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 6px 10px;

        border-radius: 999px;

        background: rgba(255, 255, 255, 0.15);

        border: 1px solid rgba(255, 255, 255, 0.28);

        color: #FFFFFF;

        font-size: 0.61rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.7px;

        margin-bottom: 11px;
    }


    .resp-hero-tag i {

        color: #93C5FD;
    }


    .resp-hero-content h1 {

        color: #FFFFFF;

        font-size: clamp(1.3rem, 2.1vw, 1.85rem);

        font-weight: 800;

        line-height: 1.15;

        letter-spacing: -0.7px;

        margin-bottom: 8px;
    }


    .resp-hero-content h1 span {

        color: #93C5FD;
    }


    .resp-hero-content p {

        color: rgba(255,255,255,0.72);

        font-size: 0.73rem;

        line-height: 1.5;

        max-width: 500px;
    }


    /* DATE CARD */

    .resp-hero-date {

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

        color: #FFFFFF;

        background: rgba(255, 255, 255, 0.14);

        backdrop-filter: blur(10px);

        border: 1px solid rgba(255, 255, 255, 0.28);

        border-radius: 38% 62% 58% 42% / 45% 38% 62% 55%;

        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.18);
    }


    .resp-hero-date .day-name {

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        opacity: 0.75;

        margin-bottom: 2px;
    }


    .resp-hero-date .day-number {

        font-size: 2.35rem;

        font-weight: 900;

        line-height: 1;
    }


    .resp-hero-date .month {

        font-size: 0.61rem;

        font-weight: 700;

        margin-top: 4px;
    }


    .resp-hero-date .date-icon {

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

        color: var(--accent-dark);

        font-size: 0.7rem;

        box-shadow: 0 4px 10px rgba(44,52,61,0.20);
    }


    /* =========================================================
       STATS PANEL
    ========================================================= */

    .resp-stats-panel {

        background: #FFFFFF;

        border-radius: 20px;

        border: 1px solid var(--border);

        display: grid;

        grid-template-columns: repeat(4, minmax(0, 1fr));

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        margin-top: -4px;

        position: relative;

        z-index: 5;
    }


    .resp-stat-item {

        padding: 17px 18px;

        display: flex;

        align-items: center;

        gap: 12px;

        position: relative;
    }


    .resp-stat-item:not(:last-child)::after {

        content: "";

        position: absolute;

        width: 1px;

        height: 38px;

        right: 0;

        top: 50%;

        transform: translateY(-50%);

        background: var(--border);
    }


    .resp-stat-icon {

        width: 39px;

        height: 39px;

        flex-shrink: 0;

        border-radius: 12px;

        display: flex;

        align-items: center;

        justify-content: center;

        background: var(--accent-soft);

        color: var(--accent);

        font-size: 0.9rem;
    }


    .resp-stat-item.danger .resp-stat-icon {

        background: var(--danger-soft);

        color: var(--danger);
    }


    .resp-stat-item.success .resp-stat-icon {

        background: var(--green-soft);

        color: var(--green);
    }


    .resp-stat-item.amber .resp-stat-icon {

        background: var(--amber-soft);

        color: var(--amber);
    }


    .resp-stat-info {

        min-width: 0;
    }


    .resp-stat-info strong {

        display: block;

        color: var(--dark);

        font-size: 1.05rem;

        font-weight: 800;

        line-height: 1.1;

        margin-bottom: 3px;
    }


    .resp-stat-info span {

        display: block;

        color: var(--muted);

        font-size: 0.61rem;

        font-weight: 600;

        white-space: nowrap;
    }


    /* =========================================================
       GRILLE
    ========================================================= */

    .resp-grid {

        display: grid;

        grid-template-columns:
            minmax(0, 1.65fr)
            minmax(280px, 0.85fr);

        gap: 18px;
    }


    /* =========================================================
       CARTES
    ========================================================= */

    .resp-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        padding: 20px;

        box-shadow: var(--shadow-sm);

        min-width: 0;

        display: flex;

        flex-direction: column;

        gap: 14px;
    }


    .resp-card-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        padding-bottom: 12px;

        border-bottom: 1px solid var(--border);
    }


    .resp-card-title {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .resp-card-title h3 {

        color: var(--dark);

        font-size: 0.85rem;

        font-weight: 800;

        margin: 0;

        letter-spacing: -0.2px;

        display: flex;

        align-items: center;

        gap: 8px;
    }


    .resp-card-title h3 i {

        color: var(--accent);

        font-size: 0.82rem;
    }


    .resp-card-title span {

        color: var(--muted-light);

        font-size: 0.6rem;

        font-weight: 600;
    }


    .resp-card-count {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 11px;

        border-radius: 999px;

        background: var(--accent-soft);

        border: 1px solid rgba(37, 99, 235, 0.25);

        color: var(--accent-dark);

        font-size: 0.62rem;

        font-weight: 800;

        white-space: nowrap;
    }


    .resp-card-count.danger {

        background: var(--danger-soft);

        border-color: rgba(220, 38, 38, 0.18);

        color: var(--danger);
    }


    .resp-card-count.green {

        background: var(--green-soft);

        border-color: rgba(48, 195, 26, 0.18);

        color: #249C13;
    }


    /* =========================================================
       GRAPHIQUE
    ========================================================= */

    .resp-chart-area {

        width: 100%;

        height: 200px;

        position: relative;
    }


    .resp-chart-svg {

        width: 100%;

        height: 100%;

        display: block;

        overflow: visible;
    }


    .resp-chart-labels {

        display: flex;

        justify-content: space-between;

        padding: 7px 4px 0;

        color: var(--muted-light);

        font-size: 0.58rem;

        font-weight: 600;
    }


    /* =========================================================
       LISTE
    ========================================================= */

    .resp-list {

        display: flex;

        flex-direction: column;

        gap: 8px;
    }


    .resp-row {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 10px;

        min-height: 50px;

        padding: 9px 11px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 12px;

        text-decoration: none;

        color: inherit;

        transition:
            transform 0.18s ease,
            border-color 0.18s ease,
            background 0.18s ease,
            box-shadow 0.18s ease;
    }


    .resp-row:hover {

        transform: translateX(3px);

        background: var(--accent-light);

        border-color: rgba(37, 99, 235, 0.45);

        box-shadow: 0 5px 15px rgba(37, 99, 235, 0.08);
    }


    .resp-row-left {

        display: flex;

        align-items: center;

        gap: 10px;

        min-width: 0;

        flex: 1;
    }


    .resp-row-avatar {

        width: 34px;

        height: 34px;

        flex-shrink: 0;

        border-radius: 10px;

        background: var(--accent-soft);

        color: var(--accent);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.72rem;
    }


    .resp-row-body {

        display: flex;

        flex-direction: column;

        gap: 2px;

        min-width: 0;
    }


    .resp-row-body strong {

        color: var(--dark);

        font-size: 0.72rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .resp-row-body span {

        color: var(--muted-light);

        font-size: 0.58rem;

        font-weight: 600;
    }


    .resp-score {

        color: var(--accent);

        font-size: 0.72rem;

        font-weight: 800;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    /* =========================================================
       BLOC ALERTE
    ========================================================= */

    .resp-alert-block {

        padding: 12px 14px;

        border: 1px solid var(--border);

        border-radius: 12px;

        background: #FFFFFF;

        display: flex;

        flex-direction: column;

        gap: 9px;

        transition: border-color 0.2s ease, background 0.2s ease;
    }


    .resp-alert-block:hover {

        border-color: rgba(37, 99, 235, 0.45);

        background: var(--accent-light);
    }


    .resp-alert-top {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 10px;
    }


    .resp-alert-time {

        color: var(--muted-light);

        font-size: 0.58rem;

        font-weight: 600;
    }


    .resp-alert-text {

        margin: 0;

        color: var(--text);

        font-size: 0.72rem;

        line-height: 1.55;

        font-weight: 500;
    }


    .resp-alert-action {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        color: #FFFFFF;

        font-size: 0.65rem;

        font-weight: 800;

        text-decoration: none;

        background: var(--dark);

        border: 1px solid var(--dark);

        padding: 6px 11px;

        border-radius: 8px;

        font-family: inherit;

        cursor: pointer;

        transition: background 0.2s ease, transform 0.2s ease;

        align-self: flex-start;
    }


    .resp-alert-action i {

        color: #93C5FD;

        font-size: 0.6rem;
    }


    .resp-alert-action:hover {

        background: var(--dark-soft);

        border-color: var(--dark-soft);

        transform: translateY(-1px);
    }


    /* =========================================================
       PILL / BADGES
    ========================================================= */

    .resp-pill {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        height: 22px;

        padding: 0 9px;

        border-radius: 999px;

        font-size: 0.58rem;

        font-weight: 800;

        letter-spacing: 0.2px;

        border: 1px solid transparent;

        white-space: nowrap;
    }


    .resp-pill i {

        font-size: 0.58rem;
    }


    .resp-pill.blue {

        background: var(--accent-soft);

        color: var(--accent-dark);

        border-color: rgba(37, 99, 235, 0.22);
    }


    .resp-pill.amber {

        background: var(--amber-soft);

        color: var(--amber);

        border-color: rgba(245, 158, 11, 0.25);
    }


    .resp-pill.red {

        background: var(--danger-soft);

        color: var(--danger);

        border-color: rgba(220, 38, 38, 0.22);
    }


    .resp-pill.green {

        background: var(--green-soft);

        color: #249C13;

        border-color: rgba(48, 195, 26, 0.22);
    }


    .resp-pill.neutral {

        background: var(--background);

        color: var(--muted);

        border-color: var(--border);
    }


    /* =========================================================
       BOUTON PRIMAIRE — BLEU NUIT COMME LE LAYOUT
    ========================================================= */

    .resp-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 6px;

        height: 34px;

        padding: 0 14px;

        border-radius: 10px;

        background: var(--dark);

        color: #FFFFFF;

        font-family: inherit;

        font-size: 0.65rem;

        font-weight: 800;

        border: none;

        cursor: pointer;

        transition: background 0.2s ease, transform 0.2s ease;
        white-space: nowrap;
    }


    .resp-btn i {

        color: #93C5FD;

        font-size: 0.66rem;
    }


    .resp-btn:hover {

        background: var(--dark-soft);

        transform: translateY(-1px);
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .resp-empty {

        padding: 32px 20px;

        text-align: center;

        color: var(--muted);

        font-size: 0.7rem;

        font-weight: 600;

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 8px;
    }


    .resp-empty i {

        font-size: 1.5rem;

        color: var(--green);
    }


    /* =========================================================
       FOOTER
    ========================================================= */

    .resp-footer {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        padding: 4px 4px 0;

        color: var(--muted-light);

        font-size: 0.58rem;

        font-weight: 600;
    }


    .resp-system-status {

        display: inline-flex;

        align-items: center;

        gap: 6px;
    }


    .resp-system-status-dot {

        width: 7px;

        height: 7px;

        border-radius: 50%;

        background: var(--green);

        box-shadow: 0 0 0 3px rgba(48, 195, 26, 0.08);
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1180px) {

        .resp-grid {

            grid-template-columns: 1fr;
        }
    }


    @media (max-width: 850px) {

        .resp-topbar {

            flex-wrap: wrap;
        }


        .resp-search {

            width: min(230px, 60vw);
        }


        .resp-hero {

            grid-template-columns: 1fr;

            min-height: auto;

            padding: 23px;
        }


        .resp-hero-date {

            justify-self: start;

            width: 105px;

            height: 105px;
        }


        .resp-stats-panel {

            grid-template-columns: repeat(2, 1fr);
        }


        .resp-stat-item:nth-child(2)::after {

            display: none;
        }


        .resp-stat-item:nth-child(3) {

            border-top: 1px solid var(--border);
        }


        .resp-stat-item:nth-child(4) {

            border-top: 1px solid var(--border);
        }
    }


    @media (max-width: 560px) {

        .resp-greeting {

            width: 100%;
        }


        .resp-topbar-actions {

            width: 100%;
        }


        .resp-search {

            flex: 1;

            width: auto;
        }


        .resp-hero {

            border-radius: 18px;

            padding: 20px;
        }


        .resp-hero-content h1 {

            font-size: 1.35rem;
        }


        .resp-stats-panel {

            grid-template-columns: 1fr;
        }


        .resp-stat-item {

            border-bottom: 1px solid var(--border);
        }


        .resp-stat-item::after {

            display: none !important;
        }


        .resp-stat-item:last-child {

            border-bottom: none;
        }


        .resp-card {

            padding: 15px;

            border-radius: 17px;
        }


        .resp-chart-area {

            height: 170px;
        }


        .resp-footer {

            flex-direction: column;

            align-items: flex-start;
        }
    }

</style>


<div class="resp-dashboard">


    {{-- =====================================================
         TOPBAR
    ====================================================== --}}

    <div class="resp-topbar">

        <div class="resp-greeting">

            <div class="resp-avatar">

                <i class="fa-solid fa-user-tie"></i>

            </div>


            <div class="resp-greeting-text">

                <span>
                    Bonjour,
                </span>

                <strong>
                    {{ $nomResponsable }}
                </strong>

            </div>

        </div>


        <div class="resp-topbar-actions">

            <form method="GET" action="{{ route('responsable.clients.index') }}" class="resp-search">

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



    {{-- =====================================================
         HERO
    ====================================================== --}}

    <section class="resp-hero">

        <div class="resp-hero-content">

            <div class="resp-hero-tag">

                <i class="fa-solid fa-shield-halved"></i>

                Espace Responsable d'agence

            </div>


            <h1>
                Conformité de votre agence,
                <span>{{ $nomResponsable }}</span>
            </h1>


            <p>
                Supervisez les dossiers KYC, les alertes de filtrage
                et les déclarations CENTIF en un seul endroit.
            </p>

        </div>


        <div class="resp-hero-date">

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



    {{-- =====================================================
         STATISTIQUES GLOBALES
    ====================================================== --}}

    <section class="resp-stats-panel">


        <div class="resp-stat-item">

            <div class="resp-stat-icon">

                <i class="fa-solid fa-user-clock"></i>

            </div>

            <div class="resp-stat-info">

                <strong>
                    {{ $dossiersACompleter->count() }}
                </strong>

                <span>
                    Dossiers à compléter
                </span>

            </div>

        </div>


        <div class="resp-stat-item danger">

            <div class="resp-stat-icon">

                <i class="fa-solid fa-triangle-exclamation"></i>

            </div>

            <div class="resp-stat-info">

                <strong>
                    {{ $alertesDuJour->count() }}
                </strong>

                <span>
                    Alertes du jour
                </span>

            </div>

        </div>


        <div class="resp-stat-item amber">

            <div class="resp-stat-icon">

                <i class="fa-solid fa-scale-balanced"></i>

            </div>

            <div class="resp-stat-info">

                <strong>
                    {{ $seuilsEtFractionnements->count() }}
                </strong>

                <span>
                    Seuils / fractionnements
                </span>

            </div>

        </div>


        <div class="resp-stat-item success">

            <div class="resp-stat-icon">

                <i class="fa-solid fa-id-card"></i>

            </div>

            <div class="resp-stat-info">

                <strong>
                    {{ $npiEnAttente->count() }}
                </strong>

                <span>
                    NPI en attente
                </span>

            </div>

        </div>

    </section>



    {{-- =====================================================
         GRAPHIQUE + RÉPARTITION
    ====================================================== --}}

    <div class="resp-grid">


        <section class="resp-card">

            <div class="resp-card-header">

                <div class="resp-card-title">

                    <h3>

                        <i class="fa-solid fa-chart-line"></i>

                        Activité de la semaine

                    </h3>

                    <span>
                        Alertes et dossiers traités sur 7 jours
                    </span>

                </div>


                <span class="resp-card-count">

                    <i class="fa-solid fa-calendar-week"></i>

                    Cette semaine

                </span>

            </div>


            <div class="resp-chart-area">

                <svg
                    id="alertesChart"
                    class="resp-chart-svg"
                    viewBox="0 0 700 200"
                    preserveAspectRatio="none"
                ></svg>

            </div>


            <div
                id="alertesChartLabels"
                class="resp-chart-labels"
            ></div>

        </section>



        <section class="resp-card">

            <div class="resp-card-header">

                <div class="resp-card-title">

                    <h3>

                        <i class="fa-solid fa-circle-exclamation"></i>

                        Répartition des alertes

                    </h3>

                    <span>
                        Par gravité aujourd'hui
                    </span>

                </div>

            </div>


            <div style="display: flex; flex-direction: column; gap: 10px;">


                @foreach (['critique' => 'Critiques', 'attention' => 'Attention', 'info' => 'Info'] as $gravite => $libelle)

                    @php
                        $compte = $alertesParGravite[$gravite] ?? 0;
                        $total = max(array_sum($alertesParGravite), 1);
                        $pourcentage = round(($compte / $total) * 100);
                    @endphp


                    <div style="display: flex; flex-direction: column; gap: 5px;">

                        <div style="display: flex; align-items: center; justify-content: space-between;">

                            <span style="font-size: 0.68rem; font-weight: 700; color: var(--dark);">

                                {{ $libelle }}

                            </span>


                            <span class="resp-pill {{ $gravite === 'critique' ? 'red' : ($gravite === 'attention' ? 'amber' : 'neutral') }}">

                                {{ $compte }}

                            </span>

                        </div>


                        <div style="height: 6px; background: var(--background); border-radius: 999px; overflow: hidden;">

                            <div
                                style="height: 100%; width: {{ $pourcentage }}%; border-radius: 999px; transition: width 0.6s ease; background: {{ $gravite === 'critique' ? 'var(--danger)' : ($gravite === 'attention' ? '#F59E0B' : 'var(--muted-light)') }};"
                            ></div>

                        </div>

                    </div>

                @endforeach


            </div>

        </section>

    </div>



    {{-- =====================================================
         DOSSIERS À COMPLÉTER + ALERTES DU JOUR
    ====================================================== --}}

    <div class="resp-grid">


        {{-- DOSSIERS À COMPLÉTER --}}

        <section class="resp-card">

            <div class="resp-card-header">

                <div class="resp-card-title">

                    <h3>

                        <i class="fa-solid fa-folder-open"></i>

                        Dossiers à compléter

                    </h3>

                    <span>
                        KYC incomplets dans votre agence
                    </span>

                </div>


                <span class="resp-card-count">

                    {{ $dossiersACompleter->count() }}

                </span>

            </div>


            <div class="resp-list">

                @forelse ($dossiersACompleter as $client)

                    <a
                        href="{{ route('agent.clients.completer', $client) }}"
                        class="resp-row"
                    >

                        <div class="resp-row-left">

                            <div class="resp-row-avatar">

                                <i class="fa-solid fa-user"></i>

                            </div>


                            <div class="resp-row-body">

                                <strong>
                                    {{ $client->nomAffichage() }}
                                </strong>

                                <span>
                                    Dossier KYC à compléter
                                </span>

                            </div>

                        </div>


                        <span class="resp-score">

                            {{ $client->score_completude_kyc }} %

                        </span>

                    </a>

                @empty

                    <div class="resp-empty">

                        <i class="fa-solid fa-circle-check"></i>

                        Aucun dossier en attente.

                    </div>

                @endforelse

            </div>

        </section>



        {{-- ALERTES DU JOUR --}}

        <section class="resp-card">

            <div class="resp-card-header">

                <div class="resp-card-title">

                    <h3>

                        <i class="fa-solid fa-triangle-exclamation"></i>

                        Alertes du jour

                    </h3>

                    <span>
                        Filtrage et vérifications NPI
                    </span>

                </div>


                <span class="resp-card-count danger">

                    {{ $alertesDuJour->count() }}

                </span>

            </div>


            <div class="resp-list">

                @forelse ($alertesDuJour as $alerte)

                    <div class="resp-alert-block">

                        <div class="resp-alert-top">

                            <x-badge-gravite :gravite="$alerte->gravite" />

                            <span class="resp-alert-time">

                                {{ $alerte->created_at->diffForHumans() }}

                            </span>

                        </div>


                        <x-expliquer :texte="$alerte->explication_texte" apercu />


                        @if (in_array($alerte->type->value, ['filtrage_sanction', 'filtrage_ppe']))

                            <a
                                href="{{ route('responsable.filtrage.index') }}"
                                class="resp-alert-action"
                            >

                                Traiter dans Filtrage

                                <i class="fa-solid fa-arrow-right"></i>

                            </a>

                        @elseif ($alerte->type->value === 'npi_invalide_apres_verification')

                            <form
                                method="POST"
                                action="{{ route('responsable.conformite.alertes-npi.traiter', $alerte) }}"
                                style="margin: 0;"
                            >

                                @csrf

                                <button type="submit" class="resp-alert-action">

                                    Marquer comme traité

                                </button>

                            </form>

                        @endif

                    </div>

                @empty

                    <div class="resp-empty">

                        <i class="fa-solid fa-circle-check"></i>

                        Aucune alerte en attente.

                    </div>

                @endforelse

            </div>

        </section>

    </div>



    {{-- =====================================================
         SEUILS ET FRACTIONNEMENTS
    ====================================================== --}}

    <section class="resp-card">

        <div class="resp-card-header">

            <div class="resp-card-title">

                <h3>

                    <i class="fa-solid fa-scale-unbalanced"></i>

                    Seuils et fractionnements

                </h3>

                <span>
                    Dépassements et opérations fractionnées détectées
                </span>

            </div>


            <span class="resp-card-count">

                {{ $seuilsEtFractionnements->count() }}

            </span>

        </div>


        <div class="resp-list">

            @forelse ($seuilsEtFractionnements as $alerte)

                @php $critique = $alerte->type->value === 'fractionnement_multi_agences'; @endphp


                <div class="resp-alert-block">

                    <div class="resp-alert-top">

                        <span class="resp-pill {{ $critique ? 'red' : 'amber' }}">

                            <i class="fa-solid {{ $critique ? 'fa-circle-exclamation' : 'fa-triangle-exclamation' }}"></i>

                            {{ $alerte->type->libelle() }}

                        </span>

                    </div>


                    <x-expliquer :texte="$alerte->explication_texte" apercu />


                    @if (($alerte->faits['identite_id'] ?? null) !== null)

                        <a
                            href="{{ route('responsable.identites.afficher', $alerte->faits['identite_id']) }}"
                            class="resp-alert-action"
                        >

                            Voir la vue consolidée de la personne

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>

                    @endif

                </div>

            @empty

                <div class="resp-empty">

                    <i class="fa-solid fa-circle-check"></i>

                    Aucun seuil approché ni fractionnement détecté.

                </div>

            @endforelse

        </div>

    </section>



    {{-- =====================================================
         DÉCLARATIONS CENTIF + NPI EN ATTENTE
    ====================================================== --}}

    <div class="resp-grid">


        {{-- DÉCLARATIONS CENTIF --}}

        <section class="resp-card">

            <div class="resp-card-header">

                <div class="resp-card-title">

                    <h3>

                        <i class="fa-solid fa-file-pdf"></i>

                        Déclarations CENTIF à venir

                    </h3>

                    <span>
                        Périodes à générer pour transmission
                    </span>

                </div>


                <span class="resp-card-count">

                    {{ $declarationsAVenir->count() }}

                </span>

            </div>


            <div class="resp-list">

                @forelse ($declarationsAVenir as $declaration)

                    <div class="resp-row" style="cursor: default;">

                        <div class="resp-row-left">

                            <div class="resp-row-avatar">

                                <i class="fa-solid fa-file-invoice"></i>

                            </div>


                            <div class="resp-row-body">

                                <strong>
                                    {{ $declaration->periode }}
                                </strong>

                                <span>
                                    {{ number_format((float) $declaration->montant_cumule, 0, ',', ' ') }} XOF
                                </span>

                            </div>

                        </div>


                        <form
                            method="POST"
                            action="{{ route('responsable.conformite.declarations-centif.generer', $declaration) }}"
                            style="margin: 0;"
                        >

                            @csrf

                            <button type="submit" class="resp-btn">

                                <i class="fa-solid fa-file-pdf"></i>

                                Générer

                            </button>

                        </form>

                    </div>

                @empty

                    <div class="resp-empty">

                        <i class="fa-solid fa-circle-check"></i>

                        Aucune déclaration à préparer.

                    </div>

                @endforelse

            </div>

        </section>



        {{-- NPI EN ATTENTE --}}

        <section class="resp-card">

            <div class="resp-card-header">

                <div class="resp-card-title">

                    <h3>

                        <i class="fa-solid fa-id-card"></i>

                        NPI en attente

                    </h3>

                    <span>
                        Vérification ANIP non encore effectuée
                    </span>

                </div>


                <span class="resp-card-count">

                    {{ $npiEnAttente->count() }}

                </span>

            </div>


            <div class="resp-list">

                @forelse ($npiEnAttente as $client)

                    <a
                        href="{{ route('agent.clients.completer', $client) }}"
                        class="resp-row"
                    >

                        <div class="resp-row-left">

                            <div class="resp-row-avatar">

                                <i class="fa-solid fa-user"></i>

                            </div>


                            <div class="resp-row-body">

                                <strong>
                                    {{ $client->nomAffichage() }}
                                </strong>

                                <span>
                                    Vérification NPI à effectuer
                                </span>

                            </div>

                        </div>


                        <span class="resp-pill amber">

                            <i class="fa-solid fa-hourglass-half"></i>

                            En attente

                        </span>

                    </a>

                @empty

                    <div class="resp-empty">

                        <i class="fa-solid fa-circle-check"></i>

                        Aucun NPI en attente.

                    </div>

                @endforelse

            </div>

        </section>

    </div>



    {{-- =====================================================
         FOOTER
    ====================================================== --}}

    <div class="resp-footer">

        <span>
            {{ $identite['nom_systeme'] }} — Tableau de bord conformité
        </span>


        <span class="resp-system-status">

            <span class="resp-system-status-dot"></span>

            Système opérationnel

        </span>

    </div>


</div>



<script>

document.addEventListener('DOMContentLoaded', function () {

    /* =========================================================
       GRAPHIQUE ACTIVITÉ 7 JOURS — BLEU NUIT / BLEU ROYAL
    ========================================================= */

    const data = @json($alertesSemaine);

    const labels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];


    const svg = document.getElementById('alertesChart');

    const labelsContainer = document.getElementById('alertesChartLabels');


    if (!svg || !labelsContainer || !data.length) {
        return;
    }


    const width = 700;

    const height = 200;

    const paddingX = 12;

    const paddingY = 18;


    const maxValue = Math.max(...data, 1);

    const chartMax = maxValue * 1.20;

    const stepX = (width - (paddingX * 2)) / Math.max(data.length - 1, 1);


    const points = data.map(function(value, index) {

        const x = paddingX + (index * stepX);

        const y = height - paddingY - ((value / chartMax) * (height - paddingY * 2));


        return { x: x, y: y, value: value };

    });


    let linePath = '';


    points.forEach(function(point, index) {

        if (index === 0) {

            linePath = `M ${point.x} ${point.y}`;

            return;
        }


        const previous = points[index - 1];

        const controlX = (previous.x + point.x) / 2;


        linePath += ` C ${controlX} ${previous.y}, ${controlX} ${point.y}, ${point.x} ${point.y}`;

    });


    const firstPoint = points[0];

    const lastPoint = points[points.length - 1];


    const areaPath = linePath + ` L ${lastPoint.x} ${height} L ${firstPoint.x} ${height} Z`;


    let gridLines = '';


    [25, 50, 75, 100].forEach(function(percent) {

        const y = height - paddingY - ((percent / 100) * (height - paddingY * 2));


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
       SVG — ligne bleu nuit, points bleu royal
    ========================================================= */

    svg.innerHTML = `

        <defs>

            <linearGradient
                id="respChartGradient"
                x1="0"
                y1="0"
                x2="0"
                y2="1"
            >

                <stop
                    offset="0%"
                    stop-color="#2C343D"
                    stop-opacity="0.28"
                />

                <stop
                    offset="100%"
                    stop-color="#2C343D"
                    stop-opacity="0"
                />

            </linearGradient>

        </defs>


        ${gridLines}


        <path
            d="${areaPath}"
            fill="url(#respChartGradient)"
        />


        <path
            d="${linePath}"
            fill="none"
            stroke="#2C343D"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
        />


        ${points.map(function(point) {

            return `

                <circle
                    cx="${point.x}"
                    cy="${point.y}"
                    r="6"
                    fill="#FFFFFF"
                    stroke="#2563EB"
                    stroke-width="3"
                />

                <circle
                    cx="${point.x}"
                    cy="${point.y}"
                    r="2.5"
                    fill="#2C343D"
                />

            `;

        }).join('')}

    `;


    labelsContainer.innerHTML = labels.map(function(label) {

        return `<span>${label}</span>`;

    }).join('');

});

</script>

@endsection
