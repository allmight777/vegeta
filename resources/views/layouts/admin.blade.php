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
        {{ $identite['nom_systeme'] }}
        — Administration — @yield('titre', '')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <link rel="stylesheet" href="{{ asset('vendor/plus-jakarta-sans/font.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">




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

        .admin-header {

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


        .admin-header-inner {

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

        .admin-brand {

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


        .admin-brand-icon {

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


        .admin-brand-icon::after {

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


        .admin-brand-text {

            display:
                flex;

            flex-direction:
                column;

            line-height:
                1.1;
        }


        .admin-brand-title {

            color:
                var(--cif-dark);

            font-size:
                0.84rem;

            font-weight:
                900;

            letter-spacing:
                -0.2px;
        }


        .admin-brand-subtitle {

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

        .admin-header-right {

            display:
                flex;

            align-items:
                center;

            gap:
                15px;
        }


        /* =====================================================
           BADGE "ADMINISTRATION"
        ===================================================== */

        .admin-role-badge {

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
                var(--cif-yellow-soft);

            border:
                1px solid
                rgba(240, 229, 53, 0.28);

            color:
                var(--cif-dark);

            font-size:
                0.62rem;

            font-weight:
                800;

            white-space:
                nowrap;
        }


        .admin-role-badge i {

            color:
                #A08F00;

            font-size:
                0.7rem;
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

        .admin-profile {

            display:
                flex;

            align-items:
                center;

            gap:
                10px;
        }


        .admin-profile-avatar {

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


        .admin-profile-info {

            display:
                flex;

            flex-direction:
                column;

            line-height:
                1.15;
        }


        .admin-profile-name {

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


        .admin-profile-role {

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

        .admin-alert-conteneur {

            width:
                100%;

            padding:
                14px 24px 0;
        }


        .admin-alert {

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


        .admin-alert-icon {

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

        .admin-shell {

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

        .admin-sidebar {

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

        .admin-content {

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

        .admin-page-heading {

            display:
                flex;

            align-items:
                flex-end;

            justify-content:
                space-between;

            gap:
                15px;
        }


        .admin-page-heading-left {

            display:
                flex;

            flex-direction:
                column;
        }


        .admin-page-eyebrow {

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


        .admin-page-eyebrow::before {

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


        .admin-page-title {

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


        .admin-page-subtitle {

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

            .admin-shell {

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

            .admin-header-inner {

                min-height:
                    62px;

                padding:
                    0 16px;
            }

            .admin-header-right {

                gap:
                    9px;
            }

            .admin-role-badge {

                width:
                    32px;

                height:
                    32px;

                padding:
                    0;

                justify-content:
                    center;
            }

            .admin-role-badge span {

                display:
                    none;
            }

            .header-separator {

                display:
                    none;
            }

            .admin-profile-info {

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

            .admin-brand-icon {

                width:
                    36px;

                height:
                    36px;

                border-radius:
                    10px;
            }

            .admin-brand-title {

                font-size:
                    0.74rem;
            }

            .admin-brand-subtitle {

                font-size:
                    0.5rem;
            }

            .admin-alert-conteneur {

                padding:
                    10px 16px 0;
            }

            /* SHELL */

            .admin-shell {

                grid-template-columns:
                    1fr;

                padding:
                    14px 14px 35px;

                gap:
                    14px;
            }

            /* SIDEBAR MOBILE : barre horizontale */

            .admin-sidebar {

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
                    repeat(3, 1fr);

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

            .admin-header-inner {

                padding:
                    0 12px;
            }

            .admin-brand-text {

                display:
                    none;
            }

            .admin-header-right {

                margin-left:
                    auto;
            }

            .admin-alert-conteneur {

                padding-left:
                    12px;

                padding-right:
                    12px;
            }

            .admin-shell {

                padding-left:
                    10px;

                padding-right:
                    10px;
            }
        }

    </style>

    @include('partials.identite-style', ['espace' => 'admin'])

</head>


<body>


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <header class="admin-header">

        <div class="admin-header-inner">


            <a
                href="{{ Route::has('admin.tableau-de-bord.index')
                    ? route('admin.tableau-de-bord.index')
                    : url()->current() }}"
                class="admin-brand"
            >

                <div class="admin-brand-icon">
                    @if ($identite['logo_principal_url'])<img src="{{ $identite['logo_principal_url'] }}" alt="" class="marque-logo">@else CIF @endif
                </div>


                <div class="admin-brand-text">

                    <span class="admin-brand-title">
                        {{ $identite['nom_systeme'] }}
                    </span>

                    <span class="admin-brand-subtitle">
                        Console d'administration
                    </span>

                </div>

            </a>



            @auth('admin')

                <div class="admin-header-right">


                    <div
                        class="admin-role-badge"
                        title="Administration"
                    >

                        <i class="fa-solid fa-user-shield"></i>

                        <span>
                            Administration
                        </span>

                    </div>


                    <div class="header-separator"></div>


                    <div class="admin-profile">

                        <div class="admin-profile-avatar">

                            <i class="fa-solid fa-user-shield"></i>

                        </div>


                        <div class="admin-profile-info">

                            <span class="admin-profile-name">

                                {{ auth('admin')->user()->nom ?? auth('admin')->user()->name }}

                            </span>


                            <span class="admin-profile-role">

                                {{ auth('admin')->user()->estAdminPlateforme()
                                    ? 'Admin plateforme'
                                    : 'Admin réseau' }}

                            </span>

                        </div>

                    </div>


                    <form
                        method="POST"
                        action="{{ route('admin.connexion.detruire') }}"
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
         MESSAGE DE SESSION
    ====================================================== --}}




    {{-- =====================================================
         SHELL : SIDEBAR + CONTENU
    ====================================================== --}}

    <div class="admin-shell">


        {{-- =================================================
             SIDEBAR
        ================================================== --}}

        <aside class="admin-sidebar">

            <div class="sidebar-logo">

                <div class="sidebar-logo-mark">
                    CIF
                </div>

                <div class="sidebar-logo-text">
                    <strong>CIF-EMPREINTE</strong>
                    <span>Console d'administration</span>
                </div>

            </div>


            <nav class="sidebar-nav">

                <div class="sidebar-section-title">
                    Pilotage
                </div>


                <a
                    href="{{ Route::has('admin.tableau-de-bord.index')
                        ? route('admin.tableau-de-bord.index')
                        : url('/admin/tableau-de-bord') }}"
                    class="sidebar-link {{ request()->routeIs('admin.tableau-de-bord.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Tableau de bord</span>
                </a>


                <a
                    href="{{ Route::has('admin.journal-audit.index')
                        ? route('admin.journal-audit.index')
                        : url('/admin/journal-audit') }}"
                    class="sidebar-link {{ request()->routeIs('admin.journal-audit.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Journal d'audit</span>
                </a>


                <div class="sidebar-section-title">
                    Comptes
                </div>


                <a
                    href="{{ Route::has('admin.agents.index')
                        ? route('admin.agents.index')
                        : url('/admin/agents') }}"
                    class="sidebar-link {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Caissiers &amp; responsables</span>
                </a>


                <a
                    href="{{ Route::has('admin.documents-ia.index')
                        ? route('admin.documents-ia.index')
                        : url('/admin/documents-ia') }}"
                    class="sidebar-link {{ request()->routeIs('admin.documents-ia.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-file-lines"></i>
                    <span>Documents IA</span>
                </a>


                <div class="sidebar-section-title">
                    Conformité
                </div>


                <a
                    href="{{ Route::has('admin.listes.index')
                        ? route('admin.listes.index')
                        : url('/admin/listes') }}"
                    class="sidebar-link {{ request()->routeIs('admin.listes.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-list-check"></i>
                    <span>Listes</span>
                </a>


          {{--       <a
                    href="{{ Route::has('admin.ppe.signataires.index')
                        ? route('admin.ppe.signataires.index')
                        : url('/admin/ppe/signataires') }}"
                    class="sidebar-link {{ request()->routeIs('admin.ppe.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-user-tie"></i>
                    <span>PPE / Signataires</span>
                </a>
 --}}

                <a
                    href="{{ Route::has('admin.regles-detection.index')
                        ? route('admin.regles-detection.index')
                        : url('/admin/regles-detection') }}"
                    class="sidebar-link {{ request()->routeIs('admin.regles-detection.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-sliders"></i>
                    <span>Règles de détection</span>
                </a>


                <a
                    href="{{ route('admin.configuration.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.configuration.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-palette"></i>
                    <span>Configuration</span>
                </a>




          {{--
 <div class="sidebar-section-title">
                    Opérations
                </div>

          <a
                    href="{{ Route::has('admin.import.creer')
                        ? route('admin.import.creer')
                        : url('/admin/import') }}"
                    class="sidebar-link {{ request()->routeIs('admin.import.*') ? 'active' : '' }}"
                >
                    <i class="fa-solid fa-file-import"></i>
                    <span>Import</span>
                </a>
 --}}
            </nav>


            <div class="sidebar-bottom">

                <div class="sidebar-mini-card">

                    <div class="mini-card-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <strong>
                        Console d'administration sécurisée
                    </strong>

                    <span>
                        Accès restreint · Traçabilité active
                    </span>

                </div>

            </div>

        </aside>



        {{-- =================================================
             CONTENU
        ================================================== --}}

        <main class="admin-content">


            @hasSection('titre')

                <div class="admin-page-heading">

                    <div class="admin-page-heading-left">

                        <div class="admin-page-eyebrow">
                            Administration
                        </div>


                        <h1 class="admin-page-title">

                            @yield('titre')

                        </h1>


                        <div class="admin-page-subtitle">

                            @yield('sous-titre', 'Pilotage et supervision de la plateforme '.$identite['nom_systeme'])

                        </div>

                    </div>

                </div>

            @endif


            @yield('contenu')


        </main>

    </div>

    @auth('admin')
        @include('partials.assistant-ia')
    @endauth

</body>

</html>
