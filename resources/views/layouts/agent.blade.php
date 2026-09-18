<!doctype html>
<html lang="fr">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'CIF-Empreinte') }}
        — @yield('titre', 'Espace caissier')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

      <link rel="shortcut icon" href="{{ asset('images/fececam.jpg') }}" type="image/x-icon">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        :root {

            --cif-yellow: #F0E535;
            --cif-green: #30C31A;
            --cif-dark: #2C343D;

            --cif-bg: #F8FAFC;
            --cif-white: #FFFFFF;

            --cif-text: #1E293B;
            --cif-muted: #64748B;
            --cif-border: #E7EBEF;

            --cif-green-soft:
                rgba(48, 195, 26, 0.09);

            --cif-yellow-soft:
                rgba(240, 229, 53, 0.12);

            --cif-shadow:
                0 8px 25px rgba(44, 52, 61, 0.06);
        }


        /* =====================================================
           BASE
        ===================================================== */

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {

            margin: 0;

            min-height: 100vh;

            background:
                var(--cif-bg);

            color:
                var(--cif-text);

            font-family:
                'Plus Jakarta Sans',
                sans-serif;

            -webkit-font-smoothing:
                antialiased;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .agent-header {

            position:
                sticky;

            top:
                0;

            z-index:
                100;

            width:
                100%;

            background:
                rgba(255, 255, 255, 0.96);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border-bottom:
                1px solid
                var(--cif-border);

            box-shadow:
                0 2px 15px
                rgba(44, 52, 61, 0.035);
        }


        .agent-header-inner {

            width:
                100%;

            min-height:
                68px;

            padding:
                0 24px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                20px;
        }


        /* =====================================================
           LOGO
        ===================================================== */

        .agent-brand {

            display:
                flex;

            align-items:
                center;

            gap:
                11px;

            text-decoration:
                none;

            min-width:
                0;
        }


        .agent-brand-icon {

            width:
                40px;

            height:
                40px;

            flex-shrink:
                0;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                12px;

            background:
                var(--cif-dark);

            color:
                var(--cif-yellow);

            font-size:
                0.67rem;

            font-weight:
                900;

            letter-spacing:
                -0.3px;

            box-shadow:
                0 5px 14px
                rgba(44, 52, 61, 0.14);

            position:
                relative;

            overflow:
                hidden;
        }


        .agent-brand-icon::after {

            content:
                "";

            position:
                absolute;

            width:
                12px;

            height:
                12px;

            right:
                -3px;

            bottom:
                -3px;

            border-radius:
                50%;

            background:
                var(--cif-yellow);
        }


        .agent-brand-text {

            display:
                flex;

            flex-direction:
                column;

            line-height:
                1.1;
        }


        .agent-brand-title {

            color:
                var(--cif-dark);

            font-size:
                0.84rem;

            font-weight:
                900;

            letter-spacing:
                -0.2px;
        }


        .agent-brand-subtitle {

            color:
                var(--cif-muted);

            font-size:
                0.56rem;

            font-weight:
                600;

            margin-top:
                4px;
        }


        /* =====================================================
           PARTIE DROITE DU HEADER
        ===================================================== */

        .agent-header-right {

            display:
                flex;

            align-items:
                center;

            gap:
                15px;
        }


        /* =====================================================
           GUICHET ACTIF
        ===================================================== */

        .guichet-status {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            height:
                34px;

            padding:
                0 12px;

            border-radius:
                999px;

            background:
                var(--cif-green-soft);

            border:
                1px solid
                rgba(48, 195, 26, 0.15);

            color:
                #249C13;

            font-size:
                0.62rem;

            font-weight:
                800;

            white-space:
                nowrap;
        }


        .guichet-status-dot {

            width:
                7px;

            height:
                7px;

            flex-shrink:
                0;

            border-radius:
                50%;

            background:
                var(--cif-green);

            box-shadow:
                0 0 0 3px
                rgba(48, 195, 26, 0.10);
        }


        /* =====================================================
           SEPARATEUR
        ===================================================== */

        .header-separator {

            width:
                1px;

            height:
                30px;

            background:
                var(--cif-border);
        }


        /* =====================================================
           PROFIL
        ===================================================== */

        .agent-profile {

            display:
                flex;

            align-items:
                center;

            gap:
                10px;
        }


        .agent-profile-avatar {

            width:
                38px;

            height:
                38px;

            flex-shrink:
                0;

            border-radius:
                11px;

            background:
                var(--cif-yellow-soft);

            color:
                var(--cif-dark);

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                0.75rem;

            border:
                1px solid
                rgba(240, 229, 53, 0.25);
        }


        .agent-profile-info {

            display:
                flex;

            flex-direction:
                column;

            line-height:
                1.15;
        }


        .agent-profile-name {

            color:
                var(--cif-dark);

            font-size:
                0.69rem;

            font-weight:
                800;

            max-width:
                180px;

            overflow:
                hidden;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;
        }


        .agent-profile-role {

            color:
                var(--cif-muted);

            font-size:
                0.54rem;

            font-weight:
                600;

            margin-top:
                4px;
        }


        /* =====================================================
           DECONNEXION
        ===================================================== */

        .logout-button {

            height:
                36px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                7px;

            padding:
                0 12px;

            border:
                1px solid
                var(--cif-border);

            border-radius:
                10px;

            background:
                var(--cif-white);

            color:
                var(--cif-muted);

            font-family:
                inherit;

            font-size:
                0.61rem;

            font-weight:
                700;

            cursor:
                pointer;

            transition:
                all 0.2s ease;
        }


        .logout-button:hover {

            background:
                var(--cif-dark);

            color:
                #FFFFFF;

            border-color:
                var(--cif-dark);

            transform:
                translateY(-1px);

            box-shadow:
                0 5px 14px
                rgba(44, 52, 61, 0.12);
        }


        .logout-button i {

            font-size:
                0.72rem;
        }


        /* =====================================================
           MESSAGE DE SESSION
        ===================================================== */

        .agent-alert-conteneur {

            width:
                100%;

            padding:
                14px 24px 0;
        }


        .agent-alert {

            width:
                100%;

            min-height:
                44px;

            display:
                flex;

            align-items:
                center;

            gap:
                10px;

            padding:
                10px 14px;

            border-radius:
                12px;

            background:
                var(--cif-green-soft);

            border:
                1px solid
                rgba(48, 195, 26, 0.14);

            color:
                #238E15;

            font-size:
                0.67rem;

            font-weight:
                700;

            box-shadow:
                var(--cif-shadow);
        }


        .agent-alert-icon {

            width:
                26px;

            height:
                26px;

            flex-shrink:
                0;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                8px;

            background:
                rgba(48, 195, 26, 0.12);

            color:
                var(--cif-green);

            font-size:
                0.68rem;
        }


        /* =====================================================
           LAYOUT PRINCIPAL : SIDEBAR + CONTENU
        ===================================================== */

        .agent-shell {

            width:
                100%;

            max-width:
                1800px;

            padding:
                16px 20px 50px;

            display:
                grid;

            grid-template-columns:
                260px minmax(0, 1fr);

            gap:
                20px;

            align-items:
                start;
        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .agent-sidebar {

            background:
                linear-gradient(
                    180deg,
                    #FFFFFF 0%,
                    #FFFDF0 100%
                );

            border:
                1px solid
                var(--cif-border);

            border-radius:
                24px;

            padding:
                22px 16px;

            display:
                flex;

            flex-direction:
                column;

            min-height:
                calc(100vh - 108px);

            box-shadow:
                var(--cif-shadow);

            position:
                sticky;

            top:
                84px;

            align-self:
                start;

            overflow:
                hidden;
        }


        /* =====================================================
           LOGO SIDEBAR
        ===================================================== */

        .sidebar-logo {

            padding:
                4px 8px 25px;

            display:
                flex;

            align-items:
                center;

            gap:
                12px;
        }


        .sidebar-logo-mark {

            width:
                48px;

            height:
                48px;

            border-radius:
                14px;

            background:
                var(--cif-yellow);

            color:
                var(--cif-dark);

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-weight:
                900;

            font-size:
                1.05rem;

            letter-spacing:
                -0.5px;

            box-shadow:
                0 8px 18px
                rgba(240, 229, 53, 0.32);

            flex-shrink:
                0;
        }


        .sidebar-logo-text {

            display:
                flex;

            flex-direction:
                column;

            line-height:
                1.05;

            min-width:
                0;
        }


        .sidebar-logo-text strong {

            color:
                var(--cif-dark);

            font-size:
                0.95rem;

            font-weight:
                900;

            letter-spacing:
                -0.3px;

            white-space:
                nowrap;
        }


        .sidebar-logo-text span {

            color:
                var(--cif-muted);

            font-size:
                0.64rem;

            margin-top:
                5px;

            font-weight:
                600;

            white-space:
                nowrap;
        }


        /* =====================================================
           NAVIGATION
        ===================================================== */

        .sidebar-nav {

            display:
                flex;

            flex-direction:
                column;

            gap:
                5px;
        }


        .sidebar-section-title {

            font-size:
                0.58rem;

            text-transform:
                uppercase;

            letter-spacing:
                1px;

            color:
                #94A3B8;

            font-weight:
                800;

            padding:
                12px 12px 8px;
        }


        .sidebar-link {

            width:
                100%;

            min-height:
                45px;

            display:
                flex;

            align-items:
                center;

            gap:
                12px;

            padding:
                10px 13px;

            border-radius:
                12px;

            text-decoration:
                none;

            color:
                var(--cif-muted);

            font-size:
                0.78rem;

            font-weight:
                700;

            transition:
                background 0.2s ease,
                color 0.2s ease,
                transform 0.2s ease;
        }


        .sidebar-link i {

            width:
                20px;

            text-align:
                center;

            font-size:
                0.94rem;

            flex-shrink:
                0;
        }


        .sidebar-link:hover {

            background:
                var(--cif-yellow-soft);

            color:
                var(--cif-dark);

            transform:
                translateX(2px);
        }


        .sidebar-link.active {

            background:
                var(--cif-dark);

            color:
                #FFFFFF;

            box-shadow:
                0 7px 18px
                rgba(44, 52, 61, 0.16);
        }


        .sidebar-link.active i {

            color:
                var(--cif-yellow);
        }


        /* =====================================================
           SIDEBAR BOTTOM CARD
        ===================================================== */

        .sidebar-bottom {

            margin-top:
                auto;

            padding-top:
                20px;
        }


        .sidebar-mini-card {

            min-height:
                155px;

            border-radius:
                18px;

            background:
                linear-gradient(
                    145deg,
                    var(--cif-dark) 0%,
                    #39444F 100%
                );

            padding:
                18px;

            position:
                relative;

            overflow:
                hidden;

            color:
                #FFFFFF;
        }


        .sidebar-mini-card::before {

            content:
                "";

            position:
                absolute;

            width:
                120px;

            height:
                120px;

            right:
                -50px;

            bottom:
                -45px;

            background:
                var(--cif-yellow);

            border-radius:
                50%;

            opacity:
                0.18;
        }


        .sidebar-mini-card::after {

            content:
                "";

            position:
                absolute;

            width:
                65px;

            height:
                65px;

            right:
                10px;

            bottom:
                10px;

            border-radius:
                50%;

            background:
                var(--cif-yellow);

            opacity:
                0.12;
        }


        .mini-card-icon {

            width:
                30px;

            height:
                30px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                9px;

            background:
                rgba(240, 229, 53, 0.16);

            color:
                var(--cif-yellow);

            margin-bottom:
                14px;
        }


        .sidebar-mini-card strong {

            display:
                block;

            font-size:
                0.78rem;

            line-height:
                1.45;

            max-width:
                130px;

            position:
                relative;

            z-index:
                2;
        }


        .sidebar-mini-card span {

            display:
                block;

            font-size:
                0.63rem;

            color:
                rgba(255,255,255,0.55);

            margin-top:
                8px;

            position:
                relative;

            z-index:
                2;
        }


        /* =====================================================
           CONTENU PRINCIPAL
        ===================================================== */

        .agent-content {

            min-width:
                0;

            display:
                flex;

            flex-direction:
                column;

            gap:
                18px;
        }


        /* =====================================================
           TITRE DE PAGE
        ===================================================== */

        .agent-page-heading {

            display:
                flex;

            align-items:
                flex-end;

            justify-content:
                space-between;

            gap:
                15px;
        }


        .agent-page-heading-left {

            display:
                flex;

            flex-direction:
                column;
        }


        .agent-page-eyebrow {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                6px;

            color:
                var(--cif-green);

            font-size:
                0.57rem;

            font-weight:
                800;

            text-transform:
                uppercase;

            letter-spacing:
                0.8px;

            margin-bottom:
                5px;
        }


        .agent-page-eyebrow::before {

            content:
                "";

            width:
                6px;

            height:
                6px;

            border-radius:
                50%;

            background:
                var(--cif-green);
        }


        .agent-page-title {

            margin:
                0;

            color:
                var(--cif-dark);

            font-size:
                clamp(1.15rem, 2vw, 1.5rem);

            line-height:
                1.2;

            font-weight:
                800;

            letter-spacing:
                -0.5px;
        }


        .agent-page-subtitle {

            color:
                var(--cif-muted);

            font-size:
                0.65rem;

            font-weight:
                500;

            margin-top:
                5px;
        }


        /* =====================================================
           RESPONSIVE TABLETTE
        ===================================================== */

        @media (max-width: 1180px) {

            .agent-shell {

                grid-template-columns:
                    220px minmax(0, 1fr);

                padding:
                    16px 16px 45px;
            }
        }


        /* =====================================================
           RESPONSIVE MOBILE
        ===================================================== */

        @media (max-width: 850px) {

            .agent-header-inner {

                min-height:
                    62px;

                padding:
                    0 16px;
            }

            .agent-header-right {

                gap:
                    9px;
            }

            .guichet-status {

                width:
                    32px;

                height:
                    32px;

                padding:
                    0;

                justify-content:
                    center;
            }

            .guichet-status span:last-child {

                display:
                    none;
            }

            .header-separator {

                display:
                    none;
            }

            .agent-profile-info {

                display:
                    none;
            }

            .logout-button {

                width:
                    34px;

                height:
                    34px;

                padding:
                    0;
            }

            .logout-button span {

                display:
                    none;
            }

            .agent-brand-icon {

                width:
                    36px;

                height:
                    36px;

                border-radius:
                    10px;
            }

            .agent-brand-title {

                font-size:
                    0.74rem;
            }

            .agent-brand-subtitle {

                font-size:
                    0.5rem;
            }

            .agent-alert-conteneur {

                padding:
                    10px 16px 0;
            }

            /* SHELL */

            .agent-shell {

                grid-template-columns:
                    1fr;

                padding:
                    14px 14px 35px;

                gap:
                    14px;
            }

            /* SIDEBAR MOBILE : barre horizontale */

            .agent-sidebar {

                position:
                    relative;

                top:
                    auto;

                min-height:
                    auto;

                padding:
                    12px;

                border-radius:
                    18px;
            }

            .sidebar-logo {

                padding:
                    4px 8px 12px;
            }

            .sidebar-logo-mark {

                width:
                    40px;

                height:
                    40px;

                border-radius:
                    12px;

                font-size:
                    0.9rem;
            }

            .sidebar-logo-text strong {

                font-size:
                    0.85rem;
            }

            .sidebar-logo-text span {

                font-size:
                    0.58rem;
            }

            .sidebar-nav {

                display:
                    grid;

                grid-template-columns:
                    repeat(4, 1fr);

                gap:
                    5px;
            }

            .sidebar-section-title {

                display:
                    none;
            }

            .sidebar-link {

                justify-content:
                    center;

                padding:
                    10px 5px;

                min-height:
                    40px;
            }

            .sidebar-link span {

                display:
                    none;
            }

            .sidebar-link i {

                width:
                    auto;
            }

            .sidebar-bottom {

                display:
                    none;
            }
        }


        /* =====================================================
           PETIT MOBILE
        ===================================================== */

        @media (max-width: 450px) {

            .agent-header-inner {

                padding:
                    0 12px;
            }

            .agent-brand-text {

                display:
                    none;
            }

            .agent-header-right {

                margin-left:
                    auto;
            }

            .agent-alert-conteneur {

                padding-left:
                    12px;

                padding-right:
                    12px;
            }

            .agent-shell {

                padding-left:
                    10px;

                padding-right:
                    10px;
            }
        }

    </style>

</head>


<body>


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <header class="agent-header">

        <div class="agent-header-inner">


            <a
                href="{{ Route::has('agent.tableau-de-bord.index')
                    ? route('agent.tableau-de-bord.index')
                    : url()->current() }}"
                class="agent-brand"
            >

                <div class="agent-brand-icon">
                    CIF
                </div>


                <div class="agent-brand-text">

                    <span class="agent-brand-title">
                        CIF-Empreinte
                    </span>

                    <span class="agent-brand-subtitle">
                        Système d'Empreinte Biométrique
                    </span>

                </div>

            </a>



            @auth('agent')

                <div class="agent-header-right">


                    <div
                        class="guichet-status"
                        title="Caisse active"
                    >

                        <span class="guichet-status-dot"></span>

                        <span>
                            Caisse active
                        </span>

                    </div>


                    <div class="header-separator"></div>


                    <div class="agent-profile">

                        <div class="agent-profile-avatar">

                            <i class="fa-solid fa-user"></i>

                        </div>


                        <div class="agent-profile-info">

                            <span class="agent-profile-name">

                                {{ auth('agent')->user()->nom ?? auth('agent')->user()->name }}

                            </span>


                            <span class="agent-profile-role">

                                {{ auth('agent')->user()->role->libelle(auth('agent')->user()->civilite) }}
                                — {{ auth('agent')->user()->agence->nom }}

                            </span>

                        </div>

                    </div>


                    <form
                        method="POST"
                        action="{{ route('agent.connexion.detruire') }}"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="logout-button"
                            title="Déconnexion"
                        >

                            <i class="fa-solid fa-right-from-bracket"></i>

                            <span>
                                Déconnexion
                            </span>

                        </button>

                    </form>

                </div>

            @endauth

        </div>

    </header>



    {{-- =====================================================
         MESSAGE SESSION
    ====================================================== --}}





    {{-- =====================================================
         SHELL : SIDEBAR + CONTENU
    ====================================================== --}}

    <div class="agent-shell">


        {{-- =================================================
             SIDEBAR
        ================================================== --}}

        <aside class="agent-sidebar">

            <div class="sidebar-logo">

                <div class="sidebar-logo-mark">
                    CIF
                </div>

                <div class="sidebar-logo-text">
                    <strong>CIF-EMPREINTE</strong>
                    <span>Système biométrique</span>
                </div>

            </div>


            <nav class="sidebar-nav">

                <div class="sidebar-section-title">
                    Navigation
                </div>


                <a
                    href="{{ Route::has('agent.tableau-de-bord.index')
                        ? route('agent.tableau-de-bord.index')
                        : url('/agent/tableau-de-bord') }}"
                    class="sidebar-link {{ request()->routeIs('agent.tableau-de-bord.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-house"></i>
                    <span>Tableau de bord</span>
                </a>


                <a
                    href="{{ Route::has('agent.clients.index')
                        ? route('agent.clients.index')
                        : url('/agent/clients') }}"
                    class="sidebar-link {{ request()->routeIs('agent.clients.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-users"></i>
                    <span>Clients</span>
                </a>





                <div class="sidebar-section-title">
                    Gestion
                </div>


                <a
                    href="{{ route('agent.operations.historique') }}"
                    class="sidebar-link {{ request()->routeIs('agent.operations.historique') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Historique</span>
                </a>


                <a
                    href="{{ route('agent.rapports.index') }}"
                    class="sidebar-link {{ request()->routeIs('agent.rapports.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-file-export"></i>
                    <span>Rapports</span>
                </a>


            
            </nav>


            <div class="sidebar-bottom">

                <div class="sidebar-mini-card">

                    <div class="mini-card-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <strong>
                        Sécurité biométrique active
                    </strong>

                    <span>
                        Terminal CIF opérationnel
                    </span>

                </div>

            </div>

        </aside>



        {{-- =================================================
             CONTENU
        ================================================== --}}

        <main class="agent-content">


            @hasSection('titre')

                <div class="agent-page-heading">

                    <div class="agent-page-heading-left">

                        <div class="agent-page-eyebrow">
                            Espace caissier
                        </div>


                        <h1 class="agent-page-title">

                            @yield('titre')

                        </h1>


                        <div class="agent-page-subtitle">

                            @yield('sous-titre', 'Gestion sécurisée des opérations biométriques')

                        </div>

                    </div>

                </div>

            @endif


            @yield('contenu')


        </main>

    </div>

    @auth('agent')
        @include('partials.assistant-ia')
    @endauth

</body>

</html>
