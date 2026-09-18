@extends('layouts.agent')


@section('sous-titre', 'Générez, téléchargez et partagez les rapports de votre agence.')

@section('contenu')

<style>

    /* =========================================================
       PAGE RAPPORTS
    ========================================================= */

    .reports {

        --yellow: #F0E535;
        --yellow-soft: rgba(240, 229, 53, 0.12);
        --yellow-light: #FFFDE7;

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --amber: #B45309;
        --amber-soft: rgba(245, 158, 11, 0.10);

        --danger: #DC2626;
        --danger-soft: rgba(220, 38, 38, 0.08);

        --dark: #2C343D;

        --text: #1E293B;
        --muted: #64748B;
        --muted-light: #94A3B8;

        --border: #E8EDF2;
        --white: #FFFFFF;
        --background: #F8FAFC;

        --shadow-sm: 0 4px 15px rgba(44, 52, 61, 0.04);
        --shadow: 0 12px 35px rgba(44, 52, 61, 0.07);

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    [x-cloak] { display: none; }


    /* =========================================================
       HERO
    ========================================================= */

    .reports-hero {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        flex-wrap: wrap;

        padding: 22px 26px;

        border-radius: 20px;

        background: linear-gradient(135deg, #2C343D 0%, #3A4650 65%, #303A43 100%);

        position: relative;

        overflow: hidden;

        box-shadow: var(--shadow);

        color: #FFFFFF;
    }


    .reports-hero::before {

        content: "";

        position: absolute;

        width: 220px;

        height: 220px;

        right: -80px;

        top: -130px;

        border-radius: 50%;

        background: var(--yellow);

        opacity: 0.10;
    }


    .reports-hero-left {

        display: flex;

        align-items: center;

        gap: 15px;

        position: relative;

        z-index: 2;

        min-width: 0;
    }


    .reports-hero-icon {

        width: 46px;

        height: 46px;

        flex-shrink: 0;

        border-radius: 13px;

        background: rgba(240, 229, 53, 0.16);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: var(--yellow);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.05rem;
    }


    .reports-hero-text {

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .reports-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .reports-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    .btn-hero {

        display: inline-flex;

        align-items: center;

        gap: 9px;

        height: 44px;

        padding: 0 20px;

        border: none;

        border-radius: 12px;

        background: var(--yellow);

        color: var(--dark);

        font-family: inherit;

        font-size: 0.76rem;

        font-weight: 800;

        text-decoration: none;

        box-shadow: 0 8px 20px rgba(240, 229, 53, 0.32);

        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;

        position: relative;

        z-index: 2;

        white-space: nowrap;
    }


    .btn-hero:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow: 0 12px 26px rgba(240, 229, 53, 0.36);
    }


    /* =========================================================
       ALERTE SUCCÈS
    ========================================================= */

    .reports-alert {

        display: flex;

        align-items: center;

        gap: 12px;

        padding: 14px 16px;

        border-radius: 14px;

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: #238E15;

        font-size: 0.75rem;

        font-weight: 700;

        box-shadow: var(--shadow-sm);
    }


    .reports-alert i {

        font-size: 0.9rem;
    }


    /* =========================================================
       GRILLE MÉTRIQUES
    ========================================================= */

    .reports-grid {

        display: grid;

        grid-template-columns: repeat(4, minmax(0, 1fr));

        gap: 14px;
    }


    .metric-card {

        display: flex;

        align-items: center;

        gap: 14px;

        padding: 18px 20px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        box-shadow: var(--shadow-sm);

        position: relative;

        overflow: hidden;

        transition: border-color 0.2s ease, transform 0.2s ease;
    }


    .metric-card:hover {

        border-color: rgba(240, 229, 53, 0.45);

        transform: translateY(-1px);
    }


    .metric-card::after {

        content: "";

        position: absolute;

        left: 0;

        top: 0;

        bottom: 0;

        width: 4px;

        background: linear-gradient(180deg, var(--yellow) 0%, #F7EF63 100%);

        border-radius: 18px 0 0 18px;
    }


    .metric-card.green::after {

        background: linear-gradient(180deg, var(--green) 0%, #6EE85A 100%);
    }


    .metric-icon {

        width: 42px;

        height: 42px;

        flex-shrink: 0;

        border-radius: 12px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: #A08F00;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.92rem;
    }


    .metric-card.green .metric-icon {

        background: var(--green-soft);

        border-color: rgba(48, 195, 26, 0.20);

        color: #238E15;
    }


    .metric-body {

        min-width: 0;
    }


    .metric-label {

        color: var(--muted);

        font-size: 0.6rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

    }


    .metric-value {

        display: block;

        margin-top: 4px;

        color: var(--dark);

        font-size: 1.15rem;

        font-weight: 900;

        letter-spacing: -0.4px;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    /* =========================================================
       PANEL
    ========================================================= */

    .panel {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        padding: 22px 24px 24px;

        box-shadow: var(--shadow-sm);

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    .panel-head {

        display: flex;

        align-items: center;

        gap: 11px;

        padding-bottom: 14px;

        border-bottom: 1px solid var(--border);
    }


    .panel-head-icon {

        width: 34px;

        height: 34px;

        flex-shrink: 0;

        border-radius: 10px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.30);

        color: #A08F00;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.85rem;
    }


    .panel-head-text {

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .panel-head-text strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .panel-head-text span {

        color: var(--muted);

        font-size: 0.62rem;

        font-weight: 500;
    }


    /* =========================================================
       FORMULAIRE PÉRIODE
    ========================================================= */

    .period-form {

        display: grid;

        grid-template-columns: repeat(3, minmax(0, 1fr));

        gap: 16px;

        align-items: end;
    }


    .field {

        display: flex;

        flex-direction: column;

        gap: 7px;

        min-width: 0;
    }


    .field-label {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        color: var(--dark);

        font-size: 0.62rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;
    }


    .field-label i {

        color: var(--muted);

        font-size: 0.7rem;
    }


    .field input,
    .field select {

        width: 100%;

        min-height: 46px;

        padding: 11px 14px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #F8FAFC;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.78rem;

        font-weight: 600;

        outline: none;

        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }


    .field input:hover,
    .field select:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .field input:focus,
    .field select:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow: 0 0 0 3px var(--yellow-soft);
    }


    /* =========================================================
       BOUTONS
    ========================================================= */

    .btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        height: 46px;

        padding: 0 20px;

        border-radius: 11px;

        font-family: inherit;

        font-size: 0.74rem;

        font-weight: 800;

        text-decoration: none;

        cursor: pointer;

        border: none;

        transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
        white-space: nowrap;
    }


    .btn i {

        font-size: 0.74rem;
    }


    .btn-primary {

        background: var(--yellow);

        color: var(--dark);

        box-shadow: 0 8px 20px rgba(240, 229, 53, 0.30);
    }


    .btn-primary:hover {

        background: #E6DC28;

        transform: translateY(-2px);
    }


    .btn-dark {

        background: var(--dark);

        color: #FFFFFF;

        box-shadow: 0 8px 20px rgba(44, 52, 61, 0.20);
    }


    .btn-dark i {

        color: #93C5FD;
    }


    .btn-dark:hover {

        background: #3A4650;

        transform: translateY(-2px);
    }


    .btn-outline {

        background: #FFFFFF;

        color: var(--dark);

        border: 1px solid var(--border);
    }


    .btn-outline:hover {

        background: var(--background);

        border-color: #CBD5E1;

        transform: translateY(-1px);
    }


    .btn-sm {

        height: 38px;

        padding: 0 14px;

        font-size: 0.68rem;

        border-radius: 10px;
    }


    .btn-sm i {

        font-size: 0.7rem;
    }


    /* =========================================================
       SECTION GÉNÉRATION
    ========================================================= */

    .generer-divider {

        padding-top: 14px;

        border-top: 1px dashed var(--border);

        display: flex;

        justify-content: flex-end;

    }


    /* =========================================================
       GRILLE GRAPHIQUE + CONTENU
    ========================================================= */

    .reports-split {

        display: grid;

        grid-template-columns: 1.3fr 1fr;

        gap: 16px;
    }


    .chart-wrapper {

        height: 230px;

        position: relative;
    }


    .content-list {

        list-style: none;

        margin: 0;

        padding: 0;

        display: flex;

        flex-direction: column;

        gap: 10px;
    }


    .content-list li {

        display: flex;

        align-items: flex-start;

        gap: 10px;

        font-size: 0.74rem;

        color: var(--text);

        font-weight: 600;

        line-height: 1.5;
    }


    .content-list li i {

        width: 22px;

        height: 22px;

        flex-shrink: 0;

        border-radius: 7px;

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: var(--green);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.6rem;

        margin-top: 1px;
    }


    .content-list li strong {

        color: var(--dark);

        font-weight: 800;
    }


    .help-text {

        padding: 11px 13px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        border-radius: 11px;

        font-size: 0.68rem;

        color: #665D00;

        line-height: 1.55;

        font-weight: 500;

        margin: 0;
    }


    /* =========================================================
       TABLEAU RAPPORTS
    ========================================================= */

    .reports-table-wrapper {

        overflow-x: auto;
    }


    .reports-table {

        width: 100%;

        border-collapse: collapse;

        font-size: 0.78rem;
    }


    .reports-table thead {

        border-bottom: 1px solid var(--border);
    }


    .reports-table th {

        padding: 13px 14px;

        text-align: left;

        color: var(--muted);

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        white-space: nowrap;
    }


    .reports-table td {

        padding: 15px 14px;

        border-bottom: 1px solid var(--border);

        color: var(--dark);

        vertical-align: middle;
    }


    .reports-table tbody tr {

        transition: background 0.2s ease;
    }


    .reports-table tbody tr:hover {

        background: var(--yellow-light);
    }


    .reports-table tbody tr:last-child td {

        border-bottom: none;
    }


    .reports-period {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        color: var(--dark);

        font-weight: 800;

        font-size: 0.74rem;
    }


    .reports-period i {

        color: var(--muted-light);

        font-size: 0.7rem;
    }


    .reports-date {

        color: var(--muted);

        font-size: 0.68rem;

        font-weight: 600;
    }


    /* =========================================================
       BADGES STATUT
    ========================================================= */

    .badge-statut {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 10px;

        border-radius: 999px;

        font-size: 0.6rem;

        font-weight: 800;

        white-space: nowrap;
    }


    .badge-statut i {

        font-size: 0.62rem;
    }


    .badge-statut.disponible {

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.30);

        color: #8A7C00;
    }


    .badge-statut.envoye {

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: #238E15;
    }


    /* =========================================================
       ACTIONS
    ========================================================= */

    .actions {

        display: flex;

        gap: 8px;

        flex-wrap: wrap;
    }


    /* =========================================================
       PANNEAU DE PARTAGE
    ========================================================= */

    .share-panel {

        margin-top: 12px;

        padding: 18px;

        background: var(--background);

        border: 1px solid var(--border);

        border-radius: 14px;

        animation: slideDown 0.2s ease-out;
    }


    @keyframes slideDown {

        from {

            opacity: 0;

            transform: translateY(-6px);
        }

        to {

            opacity: 1;

            transform: translateY(0);
        }
    }


    .share-head {

        display: flex;

        align-items: center;

        gap: 10px;

        padding-bottom: 12px;

        margin-bottom: 14px;

        border-bottom: 1px solid var(--border);
    }


    .share-head-icon {

        width: 32px;

        height: 32px;

        flex-shrink: 0;

        border-radius: 9px;

        background: var(--amber-soft);

        border: 1px solid rgba(245, 158, 11, 0.22);

        color: var(--amber);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.75rem;
    }


    .share-head-text {

        display: flex;

        flex-direction: column;

        gap: 2px;
    }


    .share-head-text strong {

        color: var(--dark);

        font-size: 0.75rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .share-head-text span {

        color: var(--muted);

        font-size: 0.6rem;

        font-weight: 500;
    }


    .share-form {

        display: grid;

        grid-template-columns: 2fr 1.5fr 1.5fr auto;

        gap: 12px;

        align-items: end;
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .empty-state {

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 12px;

        padding: 55px 25px;

        text-align: center;

        color: var(--muted);
    }


    .empty-state-icon {

        width: 62px;

        height: 62px;

        border-radius: 50%;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.30);

        color: #A08F00;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.4rem;
    }


    .empty-state strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .empty-state span {

        font-size: 0.68rem;

        color: var(--muted-light);

        max-width: 380px;

        line-height: 1.55;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1100px) {

        .reports-grid {

            grid-template-columns: repeat(2, minmax(0, 1fr));
        }


        .reports-split {

            grid-template-columns: 1fr;
        }


        .share-form {

            grid-template-columns: 1fr 1fr;
        }
    }


    @media (max-width: 700px) {

        .reports-hero {

            padding: 18px 20px;
        }


        .reports-hero-text strong {

            font-size: 0.9rem;
        }


        .reports-grid {

            grid-template-columns: 1fr;
        }


        .period-form {

            grid-template-columns: 1fr;
        }


        .share-form {

            grid-template-columns: 1fr;
        }


        .btn-hero {

            width: 100%;

            justify-content: center;
        }


        .panel {

            padding: 18px 16px;
        }


        .generer-divider {

            justify-content: stretch;
        }


        .generer-divider .btn {

            width: 100%;
        }
    }

</style>


<div class="reports">


    {{-- =====================================================
         MESSAGE SUCCÈS
    ====================================================== --}}

    @if (session('statut'))

        <div class="reports-alert">

            <i class="fa-solid fa-circle-check"></i>

            <span>{{ session('statut') }}</span>

        </div>

    @endif



    {{-- =====================================================
         HERO
    ====================================================== --}}

    <div class="reports-hero">

        <div class="reports-hero-left">

            <div class="reports-hero-icon">

                <i class="fa-solid fa-file-invoice"></i>

            </div>


            <div class="reports-hero-text">

                <strong>
                    Rapports d'activité sécurisés
                </strong>

                <span>
                    Format quotidien inspiré des bordereaux FÉCECAM — opérations, comptes réactivés et ouvertures.
                </span>

            </div>

        </div>


        <a
            class="btn-hero"
            href="#generation"
        >

            <i class="fa-solid fa-plus"></i>

            Générer un rapport

        </a>

    </div>



    {{-- =====================================================
         MÉTRIQUES
    ====================================================== --}}

    <div class="reports-grid">


        <div class="metric-card">

            <div class="metric-icon">

                <i class="fa-solid fa-arrow-right-arrow-left"></i>

            </div>

            <div class="metric-body">

                <span class="metric-label">Opérations</span>

                <span class="metric-value">{{ $donnees['statistiques']['operations'] }}</span>

            </div>

        </div>


        <div class="metric-card green">

            <div class="metric-icon">

                <i class="fa-solid fa-arrow-down"></i>

            </div>

            <div class="metric-body">

                <span class="metric-label">Dépôts</span>

                <span class="metric-value">{{ number_format((float) $donnees['statistiques']['depots'], 0, ',', ' ') }} XOF</span>

            </div>

        </div>


        <div class="metric-card">

            <div class="metric-icon">

                <i class="fa-solid fa-triangle-exclamation"></i>

            </div>

            <div class="metric-body">

                <span class="metric-label">Cumuls journaliers dépassés</span>

                <span class="metric-value">{{ $donnees['statistiques']['cumulsDepasses'] }}</span>

            </div>

        </div>


        <div class="metric-card green">

            <div class="metric-icon">

                <i class="fa-solid fa-user-plus"></i>

            </div>

            <div class="metric-body">

                <span class="metric-label">Nouveaux clients</span>

                <span class="metric-value">{{ $donnees['statistiques']['clients'] }}</span>

            </div>

        </div>


    </div>



    {{-- =====================================================
         GÉNÉRATION DE RAPPORT
    ====================================================== --}}

    <section id="generation" class="panel">


        <div class="panel-head">

            <div class="panel-head-icon">

                <i class="fa-solid fa-calendar-days"></i>

            </div>


            <div class="panel-head-text">

                <strong>
                    1. Choisir la période
                </strong>

                <span>
                    Filtrez puis générez le rapport PDF de la période sélectionnée.
                </span>

            </div>

        </div>



        <form class="period-form" method="GET">

            <div class="field">

                <label class="field-label" for="date_debut">

                    <i class="fa-solid fa-calendar-day"></i>

                    Date de début

                </label>

                <input id="date_debut" type="date" name="date_debut" value="{{ $debut->toDateString() }}">

            </div>


            <div class="field">

                <label class="field-label" for="date_fin">

                    <i class="fa-solid fa-calendar-day"></i>

                    Date de fin

                </label>

                <input id="date_fin" type="date" name="date_fin" value="{{ $fin->toDateString() }}">

            </div>


            <button class="btn btn-outline" type="submit">

                <i class="fa-solid fa-filter"></i>

                Appliquer le filtre

            </button>

        </form>



        <div class="generer-divider">

            <form method="POST" action="{{ route('agent.rapports.generer') }}">

                @csrf

                <input type="hidden" name="date_debut" value="{{ $debut->toDateString() }}">
                <input type="hidden" name="date_fin" value="{{ $fin->toDateString() }}">


                <button class="btn btn-dark" type="submit">

                    <i class="fa-solid fa-file-pdf"></i>

                    Générer le rapport de cette période

                </button>

            </form>

        </div>


    </section>



    {{-- =====================================================
         GRAPHIQUE + CONTENU PDF
    ====================================================== --}}

    <section class="reports-split">


        <div class="panel">

            <div class="panel-head">

                <div class="panel-head-icon">

                    <i class="fa-solid fa-chart-pie"></i>

                </div>


                <div class="panel-head-text">

                    <strong>
                        Activité de la période
                    </strong>

                    <span>
                        Répartition dépôts / retraits
                    </span>

                </div>

            </div>


            <div class="chart-wrapper">

                <canvas id="operationsChart"></canvas>

            </div>

        </div>



        <div class="panel">

            <div class="panel-head">

                <div class="panel-head-icon">

                    <i class="fa-solid fa-list-check"></i>

                </div>


                <div class="panel-head-text">

                    <strong>
                        Contenu du PDF
                    </strong>

                    <span>
                        Sections incluses dans le rapport généré
                    </span>

                </div>

            </div>


            <ul class="content-list">

                <li>

                    <i class="fa-solid fa-check"></i>

                    <span>

                        <strong>{{ $donnees['operationsInhabituelles']->count() }}</strong>

                        opération(s) inhabituelle(s)

                        <span style="color: var(--muted-light); font-weight: 500;">

                            (seuil ≥ {{ number_format($donnees['seuilInhabituel'], 0, ',', ' ') }} XOF)

                        </span>

                    </span>

                </li>


                <li>

                    <i class="fa-solid fa-check"></i>

                    <span>

                        <strong>{{ $donnees['depotParClient']->count() }}</strong>

                        client(s) avec dépôt(s) sur la période

                    </span>

                </li>


                <li>

                    <i class="fa-solid fa-check"></i>

                    <span>

                        <strong>{{ $donnees['comptesDormantsReactives']->count() }}</strong>

                        compte(s) dormant(s) réactivé(s)

                    </span>

                </li>


                <li>

                    <i class="fa-solid fa-check"></i>

                    <span>

                        <strong>{{ $donnees['clientsPhysiques']->count() }}</strong>

                        ouverture(s) personne physique

                        &middot;

                        <strong>{{ $donnees['clientsMoraux']->count() }}</strong>

                        ouverture(s) personne morale

                    </span>

                </li>

            </ul>


            <p class="help-text">

                Les données sont limitées à votre agence et restent stockées dans l'espace privé de l'application.

            </p>

        </div>


    </section>



    {{-- =====================================================
         RAPPORTS GÉNÉRÉS
    ====================================================== --}}

    <section class="panel" x-data="{ ouvert: null }">


        <div class="panel-head">

            <div class="panel-head-icon">

                <i class="fa-solid fa-clock-rotate-left"></i>

            </div>


            <div class="panel-head-text">

                <strong>
                    2. Rapports générés
                </strong>

                <span>
                    Téléchargez ou partagez en toute sécurité
                </span>

            </div>

        </div>



        @if ($rapports->isEmpty())


            <div class="empty-state">

                <div class="empty-state-icon">

                    <i class="fa-solid fa-inbox"></i>

                </div>


                <strong>
                    Aucun rapport généré
                </strong>


                <span>
                    Choisissez une période ci-dessus puis cliquez
                    sur « Générer le rapport de cette période ».
                </span>

            </div>


        @else


            <div class="reports-table-wrapper">

                <table class="reports-table">

                    <thead>

                        <tr>

                            <th>Période</th>

                            <th>Créé le</th>

                            <th>Statut</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach ($rapports as $rapport)


                            <tr>


                                {{-- PÉRIODE --}}

                                <td>

                                    <span class="reports-period">

                                        <i class="fa-solid fa-calendar-week"></i>

                                        {{ $rapport->date_debut->format('d/m/Y') }}

                                        <span style="color: var(--muted-light); font-weight: 600;">→</span>

                                        {{ $rapport->date_fin->format('d/m/Y') }}

                                    </span>

                                </td>



                                {{-- DATE CRÉATION --}}

                                <td>

                                    <span class="reports-date">

                                        {{ $rapport->created_at->format('d/m/Y H:i') }}

                                    </span>

                                </td>



                                {{-- STATUT --}}

                                <td>

                                    @if ($rapport->envoye_le)

                                        <span class="badge-statut envoye">

                                            <i class="fa-solid fa-paper-plane"></i>

                                            Envoyé

                                        </span>

                                    @else

                                        <span class="badge-statut disponible">

                                            <i class="fa-solid fa-circle"></i>

                                            Disponible

                                        </span>

                                    @endif

                                </td>



                                {{-- ACTIONS --}}

                                <td>

                                    <div class="actions">

                                        <a
                                            class="btn btn-outline btn-sm"
                                            href="{{ route('agent.rapports.telecharger', $rapport) }}"
                                        >

                                            <i class="fa-solid fa-download"></i>

                                            Télécharger

                                        </a>


                                        <button
                                            class="btn btn-dark btn-sm"
                                            type="button"
                                            @click="ouvert = ouvert === '{{ $rapport->id }}' ? null : '{{ $rapport->id }}'"
                                        >

                                            <i class="fa-solid fa-share-nodes"></i>

                                            Envoyer

                                        </button>

                                    </div>



                                    {{-- PANNEAU DE PARTAGE --}}

                                    <div
                                        class="share-panel"
                                        x-show="ouvert === '{{ $rapport->id }}'"
                                        x-cloak
                                    >


                                        <div class="share-head">

                                            <div class="share-head-icon">

                                                <i class="fa-solid fa-shield-halved"></i>

                                            </div>


                                            <div class="share-head-text">

                                                <strong>
                                                    Partage sécurisé par code d'accès
                                                </strong>

                                                <span>
                                                    Le destinataire devra saisir le code pour ouvrir le PDF.
                                                </span>

                                            </div>

                                        </div>



                                        <form
                                            class="share-form"
                                            method="POST"
                                            action="{{ route('agent.rapports.envoyer', $rapport) }}"
                                        >

                                            @csrf


                                            <div class="field">

                                                <label class="field-label">

                                                    <i class="fa-solid fa-envelope"></i>

                                                    E-mail du destinataire

                                                </label>

                                                <input name="email" type="email" required autocomplete="email">

                                            </div>


                                            <div class="field">

                                                <label class="field-label">

                                                    <i class="fa-solid fa-lock"></i>

                                                    Code d'accès fort

                                                </label>

                                                <input
                                                    name="code_acces"
                                                    type="password"
                                                    required
                                                    autocomplete="new-password"
                                                    placeholder="12 caractères minimum"
                                                >

                                            </div>


                                            <div class="field">

                                                <label class="field-label">

                                                    <i class="fa-solid fa-lock"></i>

                                                    Confirmer le code

                                                </label>

                                                <input
                                                    name="code_acces_confirmation"
                                                    type="password"
                                                    required
                                                    autocomplete="new-password"
                                                >

                                            </div>


                                            <button class="btn btn-dark" type="submit">

                                                <i class="fa-solid fa-paper-plane"></i>

                                                Envoyer

                                            </button>

                                        </form>



                                        <p class="help-text" style="margin-top: 12px;">

                                            Le destinataire reçoit un lien valable <strong>7 jours</strong>.
                                            Il doit saisir ce code pour télécharger le PDF.
                                            Ne demandez jamais le mot de passe de sa messagerie.

                                        </p>

                                    </div>

                                </td>


                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


        @endif


    </section>


</div>



<script>

    document.addEventListener('DOMContentLoaded', () => {

    new Chart(document.getElementById('operationsChart'), {

        type: 'doughnut',

        data: {

            labels: ['Dépôts', 'Retraits'],

            datasets: [{

                data: [
                    {{ (float) $donnees['statistiques']['depots'] }},
                    {{ (float) $donnees['statistiques']['retraits'] }}
                ],

                backgroundColor: ['#30C31A', '#F0E535'],

                borderWidth: 0,
                hoverOffset: 6,

            }],
        },

        options: {

            maintainAspectRatio: false,

            cutout: '65%',

            plugins: {

                legend: {

                    position: 'bottom',

                    labels: {
                        boxWidth: 12,
                        boxHeight: 12,
                        padding: 16,
                        font: { size: 11, weight: '700' },
                        color: '#64748B',
                    },
                },

                tooltip: {

                    backgroundColor: '#2C343D',

                    titleColor: '#FFFFFF',

                    bodyColor: '#FFFFFF',

                    padding: 12,

                    cornerRadius: 8,

                },

            },

        },

    });

    });

</script>

@endsection
