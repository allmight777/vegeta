<?php
// Exemple d'intégration Blade / Laravel ou PHP natif
$errors = $errors ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $identite['nom_systeme'] }} — Connexion administration</title>

    <link rel="stylesheet" href="{{ asset('vendor/plus-jakarta-sans/font.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">


    <style>
        /* =========================================================
           RESET
        ========================================================= */

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* =========================================================
           COULEURS D'ORIGINE
        ========================================================= */

        :root {
            --brand-yellow: #F0E535;
            --dark-accent: #2C343D;

            --bg-light: #F8FAFC;
            --card-white: #FFFFFF;

            --text-dark: #1E293B;
            --text-muted: #64748B;

            --border-color: #E2E8F0;

            --yellow-soft: rgba(240, 229, 53, 0.12);
            --yellow-medium: rgba(240, 229, 53, 0.22);

            --shadow-soft: 0 8px 25px rgba(44, 52, 61, 0.06);
            --shadow-card: 0 20px 50px rgba(44, 52, 61, 0.10);
        }

        /* =========================================================
           BASE
        ========================================================= */

        html,
        body {
            width: 100%;
            min-height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        button,
        input {
            font-family: inherit;
        }

        /* =========================================================
           HEADER
        ========================================================= */

        header {
            height: 68px;

            background: rgba(255, 255, 255, 0.97);

            border-bottom: 1px solid var(--border-color);

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 5%;

            position: sticky;
            top: 0;
            z-index: 50;

            box-shadow: 0 2px 12px rgba(44, 52, 61, 0.04);

            backdrop-filter: blur(12px);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-badge {
            min-width: 48px;
            height: 38px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 0 14px;

            background: var(--brand-yellow);

            color: var(--dark-accent);

            border-radius: 10px;

            font-size: 0.94rem;
            font-weight: 800;

            letter-spacing: 0.5px;

            box-shadow:
                0 5px 14px rgba(240, 229, 53, 0.20);
        }

        .header-title {
            font-size: 0.90rem;
            font-weight: 700;
            color: var(--dark-accent);
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .status-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            padding: 7px 13px;

            background: var(--yellow-soft);

            color: var(--dark-accent);

            border: 1px solid var(--yellow-medium);

            border-radius: 999px;

            font-size: 0.73rem;
            font-weight: 700;
        }

        .status-dot {
            width: 7px;
            height: 7px;

            background: var(--brand-yellow);

            border-radius: 50%;

            box-shadow:
                0 0 0 4px rgba(240, 229, 53, 0.14);
        }

        /* =========================================================
           MAIN
        ========================================================= */

        .app-body {
            flex: 1;

            display: flex;

            width: 100%;

            min-height: calc(100vh - 128px);
        }

        /* =========================================================
           SECTION GAUCHE
        ========================================================= */

        .banner-section {
            flex: 1.08;

            position: relative;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 60px 6%;

            overflow: hidden;

            background: var(--dark-accent);
        }

        .banner-bg-img {
            position: absolute;
            inset: 0;

            width: 100%;
            height: 100%;

            object-fit: cover;

            opacity: 0.82;

            filter:
                contrast(104%)
                brightness(88%)
                saturate(88%);

            transition: transform 0.8s ease;
        }

        .banner-section:hover .banner-bg-img {
            transform: scale(1.025);
        }

        .banner-overlay {
            position: absolute;
            inset: 0;

            background:
                linear-gradient(
                    135deg,
                    rgba(44, 52, 61, 0.90) 0%,
                    rgba(44, 52, 61, 0.72) 55%,
                    rgba(240, 229, 53, 0.24) 100%
                );

            z-index: 1;
        }

        .banner-overlay::after {
            content: "";

            position: absolute;

            width: 430px;
            height: 430px;

            right: -220px;
            bottom: -230px;

            border-radius: 50%;

            background: rgba(240, 229, 53, 0.10);

            filter: blur(8px);
        }

        .banner-content {
            width: 100%;
            max-width: 520px;

            position: relative;
            z-index: 2;
        }

        /* =========================================================
           TAG
        ========================================================= */

        .banner-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            padding: 8px 13px;

            background: rgba(240, 229, 53, 0.15);

            border: 1px solid rgba(240, 229, 53, 0.30);

            color: #FFFBE0;

            border-radius: 999px;

            font-size: 0.71rem;
            font-weight: 800;

            text-transform: uppercase;
            letter-spacing: 0.8px;

            margin-bottom: 23px;

            backdrop-filter: blur(8px);
        }

        .banner-tag i {
            color: var(--brand-yellow);
        }

        /* =========================================================
           TITRE
        ========================================================= */

        .banner-content h2 {
            font-size: clamp(2.15rem, 3.2vw, 3rem);

            font-weight: 800;

            line-height: 1.15;

            letter-spacing: -1.5px;

            color: #FFFFFF;

            margin-bottom: 18px;
        }

        .banner-content h2 span {
            color: var(--brand-yellow);

            background: transparent;

            padding: 0;

            border-radius: 0;

            box-shadow: none;
        }

        .banner-content p.desc {
            max-width: 500px;

            font-size: 0.91rem;

            color: rgba(255, 255, 255, 0.78);

            line-height: 1.75;

            margin-bottom: 31px;

            font-weight: 400;
        }

        /* =========================================================
           FEATURES
        ========================================================= */

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .feature-item {
            display: flex;
            align-items: center;

            gap: 13px;

            padding: 11px 14px;

            background: rgba(255, 255, 255, 0.075);

            border: 1px solid rgba(255, 255, 255, 0.13);

            border-radius: 12px;

            color: rgba(255, 255, 255, 0.92);

            font-size: 0.80rem;

            font-weight: 600;

            backdrop-filter: blur(8px);

            transition:
                transform 0.25s ease,
                background 0.25s ease,
                border-color 0.25s ease;
        }

        .feature-item:hover {
            transform: translateX(4px);

            background: rgba(255, 255, 255, 0.10);

            border-color: rgba(240, 229, 53, 0.30);
        }

        .feature-icon {
            width: 37px;
            height: 37px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            background: rgba(240, 229, 53, 0.15);

            border: 1px solid rgba(240, 229, 53, 0.22);

            color: var(--brand-yellow);

            border-radius: 10px;

            font-size: 0.92rem;
        }

        /* =========================================================
           SECTION FORMULAIRE
        ========================================================= */

        .form-section {
            flex: 0.92;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 50px 35px;

            background:
                radial-gradient(
                    circle at 95% 5%,
                    rgba(240, 229, 53, 0.08),
                    transparent 27%
                ),
                var(--bg-light);

            position: relative;
        }

        /* =========================================================
           CARTE
        ========================================================= */

        .form-card {
            width: 100%;
            max-width: 430px;

            background: var(--card-white);

            border: 1px solid var(--border-color);

            border-radius: 22px;

            padding: 39px 34px 34px;

            box-shadow: var(--shadow-card);

            position: relative;

            overflow: hidden;

            animation: fadeIn 0.45s ease-out;
        }

        .form-card::before {
            content: "";

            position: absolute;

            top: 0;
            left: 0;
            right: 0;

            height: 4px;

            background:
                linear-gradient(
                    90deg,
                    var(--dark-accent),
                    var(--brand-yellow)
                );
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =========================================================
           IMAGE
        ========================================================= */

        .form-blob-img {
            position: absolute;

            top: 21px;
            right: 21px;

            width: 63px;
            height: 63px;

            object-fit: cover;

            clip-path: polygon(
                30% 0%,
                75% 0%,
                100% 30%,
                92% 78%,
                68% 100%,
                25% 90%,
                0% 65%,
                5% 20%
            );

            border-radius:
                30% 70% 70% 30% /
                30% 30% 70% 70%;

            border: 3px solid #FFFFFF;

            box-shadow:
                0 6px 18px rgba(44, 52, 61, 0.14),
                0 0 0 1px var(--border-color);

            opacity: 1;

            z-index: 3;

            transition:
                transform 0.3s ease,
                box-shadow 0.3s ease;
        }

        .form-blob-img:hover {
            transform: scale(1.07) rotate(3deg);

            box-shadow:
                0 10px 23px rgba(44, 52, 61, 0.18),
                0 0 0 1px #CBD5E1;
        }

        /* =========================================================
           HEADER FORMULAIRE
        ========================================================= */

        .form-header {
            margin-bottom: 27px;

            padding-right: 70px;
        }

        .step-indicator {
            display: inline-flex;
            align-items: center;

            padding: 5px 9px;

            background: var(--yellow-soft);

            border: 1px solid var(--yellow-medium);

            color: var(--dark-accent);

            border-radius: 7px;

            font-size: 0.68rem;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.75px;

            margin-bottom: 11px;
        }

        .form-header h1 {
            font-size: 1.88rem;

            font-weight: 800;

            color: var(--dark-accent);

            line-height: 1.2;

            letter-spacing: -0.8px;

            margin-bottom: 7px;
        }

        .form-header p {
            font-size: 0.81rem;

            color: var(--text-muted);

            line-height: 1.6;
        }

        /* =========================================================
           ERREUR
        ========================================================= */

        .err-message {
            background: #FEF2F2;

            border: 1px solid #FECACA;

            color: #DC2626;

            padding: 12px 13px;

            border-radius: 10px;

            font-size: 0.77rem;

            margin-bottom: 20px;

            display: flex;

            align-items: flex-start;

            gap: 9px;

            font-weight: 600;

            line-height: 1.45;
        }

        .err-message i {
            margin-top: 2px;
        }

        /* =========================================================
           FORM GROUP
        ========================================================= */

        .form-group {
            margin-bottom: 19px;
        }

        .form-group label {
            display: block;

            font-size: 0.70rem;

            font-weight: 800;

            margin-bottom: 8px;

            text-transform: uppercase;

            letter-spacing: 0.65px;

            color: var(--dark-accent);
        }

        /* =========================================================
           INPUT
        ========================================================= */

        .input-wrapper {
            position: relative;

            display: flex;

            align-items: center;
        }

        .input-wrapper i.icon-left {
            position: absolute;

            left: 15px;

            color: #94A3B8;

            font-size: 0.92rem;

            pointer-events: none;

            z-index: 2;

            transition: color 0.2s ease;
        }

        .input-wrapper input {
            width: 100%;

            height: 51px;

            padding: 13px 45px 13px 43px;

            border: 1px solid var(--border-color);

            border-radius: 11px;

            font-size: 0.88rem;

            color: var(--dark-accent);

            background: #F8FAFC;

            outline: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;

            font-weight: 600;
        }

        .input-wrapper input:hover {
            border-color: #CBD5E1;

            background: #FFFFFF;
        }

        .input-wrapper input::placeholder {
            color: #94A3B8;

            font-weight: 400;
        }

        .input-wrapper input:focus {
            border-color: var(--brand-yellow);

            background: #FFFFFF;

            box-shadow:
                0 0 0 3px rgba(240, 229, 53, 0.16);
        }

        .input-wrapper:focus-within i.icon-left {
            color: var(--dark-accent);
        }

        /* =========================================================
           PASSWORD
        ========================================================= */

        .toggle-pwd {
            position: absolute;

            right: 12px;

            width: 32px;
            height: 32px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: transparent;

            border: none;

            border-radius: 8px;

            color: var(--text-muted);

            cursor: pointer;

            padding: 0;

            font-size: 0.85rem;

            transition:
                color 0.2s ease,
                background 0.2s ease;
        }

        .toggle-pwd:hover {
            color: var(--dark-accent);

            background: var(--yellow-soft);
        }

        /* =========================================================
           BOUTON
        ========================================================= */

        .btn-submit {
            width: 100%;

            height: 51px;

            padding: 0 18px;

            background: var(--brand-yellow);

            color: var(--dark-accent);

            border: none;

            border-radius: 11px;

            font-size: 0.86rem;

            font-weight: 800;

            cursor: pointer;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 10px;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;

            box-shadow:
                0 8px 18px rgba(240, 229, 53, 0.28);

            font-family: inherit;
        }

        .btn-submit:hover {
            background: #E6DC28;

            transform: translateY(-2px);

            box-shadow:
                0 12px 25px rgba(240, 229, 53, 0.34);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit i {
            font-size: 0.81rem;

            transition: transform 0.2s ease;
        }

        .btn-submit:hover i {
            transform: translateX(3px);
        }

        /* =========================================================
           INFO
        ========================================================= */

        .info-box {
            margin-top: 21px;

            padding: 12px 13px;

            background: var(--yellow-soft);

            border: 1px solid var(--yellow-medium);

            border-radius: 10px;

            display: flex;

            gap: 10px;

            align-items: flex-start;
        }

        .info-box i {
            color: #A08F00;

            font-size: 0.86rem;

            margin-top: 2px;
        }

        .info-box p {
            font-size: 0.72rem;

            color: #665D00;

            line-height: 1.5;

            font-weight: 500;
        }

        /* =========================================================
           FOOTER
        ========================================================= */

        footer {
            min-height: 60px;

            background: #FFFFFF;

            padding: 13px 5%;

            display: flex;

            align-items: center;

            justify-content: space-between;

            flex-wrap: wrap;

            gap: 12px;

            color: var(--text-muted);

            font-size: 0.72rem;

            border-top: 1px solid var(--border-color);
        }

        footer strong {
            color: var(--dark-accent);

            font-weight: 800;
        }

        .admin-link {
            color: var(--dark-accent);

            text-decoration: none;

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 7px 12px;

            border-radius: 8px;

            background: #F8FAFC;

            border: 1px solid var(--border-color);

            transition:
                all 0.2s ease;

            font-weight: 700;
        }

        .admin-link i {
            color: var(--dark-accent);
        }

        .admin-link:hover {
            color: var(--dark-accent);

            background: var(--yellow-soft);

            border-color: var(--yellow-medium);

            transform: translateY(-1px);
        }

        /* =========================================================
           TABLET
        ========================================================= */

        @media (max-width: 1000px) {

            .banner-section {
                padding: 55px 5%;
            }

            .form-section {
                padding: 45px 25px;
            }

            .banner-content h2 {
                font-size: 2.25rem;
            }
        }

        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 860px) {

            header {
                height: 64px;

                padding: 0 18px;
            }

            .app-body {
                flex-direction: column;
            }

            .banner-section {
                min-height: auto;

                padding: 48px 22px 42px;
            }

            .banner-content {
                max-width: 600px;
            }

            .banner-content h2 {
                font-size: 2rem;

                letter-spacing: -1px;
            }

            .banner-content p.desc {
                font-size: 0.85rem;
            }

            .feature-list {
                gap: 9px;
            }

            .feature-item {
                font-size: 0.77rem;
            }

            .form-section {
                padding: 38px 18px 48px;

                border-left: none;
            }

            .form-card {
                max-width: 470px;

                padding: 35px 25px 28px;

                border-radius: 19px;
            }
        }

        @media (max-width: 560px) {

            .header-title {
                display: none;
            }

            .status-tag {
                padding: 7px 10px;

                font-size: 0.68rem;
            }

            .banner-section {
                padding: 42px 18px 37px;
            }

            .banner-tag {
                margin-bottom: 19px;
            }

            .banner-content h2 {
                font-size: 1.78rem;
            }

            .banner-content p.desc {
                margin-bottom: 25px;
            }

            .feature-item {
                padding: 10px 11px;
            }

            .feature-icon {
                width: 34px;
                height: 34px;
            }

            .form-section {
                padding: 30px 14px 40px;
            }

            .form-card {
                padding: 32px 20px 24px;
            }

            .form-header h1 {
                font-size: 1.7rem;
            }

            footer {
                flex-direction: column;

                justify-content: center;

                text-align: center;

                padding: 16px;
            }
        }

        @media (max-width: 380px) {

            .status-tag {
                display: none;
            }

            .banner-content h2 {
                font-size: 1.6rem;
            }

            .form-card {
                padding: 30px 17px 22px;
            }

            .form-blob-img {
                width: 56px;
                height: 56px;

                top: 18px;
                right: 18px;
            }

            .form-header {
                padding-right: 60px;
            }
        }

        /* =========================================================
           ACCESSIBILITÉ
        ========================================================= */

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
    @include('partials.identite-style', ['espace' => 'connexion'])
</head>

<body>

    <!-- =========================================================
         HEADER
    ========================================================== -->

    <header>

        <div class="header-left">

            <span class="brand-badge">
                CIF
            </span>

            <span class="header-title">
                Système d'Empreinte Biométrique
            </span>

        </div>

        <div class="header-right">

            <span class="status-tag">

                <span class="status-dot"></span>

                Administration

            </span>

        </div>

    </header>


    <!-- =========================================================
         CONTENU PRINCIPAL
    ========================================================== -->

    <div class="app-body">

        <!-- =====================================================
             BANNIÈRE GAUCHE
        ====================================================== -->

        <section class="banner-section">

            <img
                src="{{ $identite['logo_connexion_url'] }}"
                alt="Fececam"
                class="banner-bg-img"
            />

            <div class="banner-overlay"></div>

            <div class="banner-content">

                <span class="banner-tag">

                    <i class="fa-solid fa-shield-halved"></i>

                    Espace sécurisé

                </span>


                <h2>
                    Authentification
                    <span>Administrateur</span>
                </h2>


                <p class="desc">
                    Connectez-vous pour gérer les agents, superviser
                    les enregistrements biométriques et administrer
                    la plateforme {{ $identite['nom_systeme'] }}.
                </p>


                <div class="feature-list">

                    <div class="feature-item">

                        <div class="feature-icon">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>

                        <span>
                            Gestion complète des agents et des guichets
                        </span>

                    </div>


                    <div class="feature-item">

                        <div class="feature-icon">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>

                        <span>
                            Supervision et rapports d'activité en temps réel
                        </span>

                    </div>


                    <div class="feature-item">

                        <div class="feature-icon">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>

                        <span>
                            Accès hautement sécurisé réservé à l'administration
                        </span>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FORMULAIRE DROIT
        ====================================================== -->

        <section class="form-section">

            <div class="form-card">

                <!-- IMAGE -->
                <img
                    src="{{ $identite['logo_connexion_url'] }}"
                    alt="Biométrie"
                    class="form-blob-img"
                />


                <!-- EN-TÊTE -->
                <div class="form-header">

                    <span class="step-indicator">
                        Session Admin
                    </span>

                    <h1>
                        Administration
                    </h1>

                    <p>
                        Saisissez vos identifiants pour accéder
                        au tableau de bord.
                    </p>

                </div>


                <!-- ERREUR LARAVEL -->

                @if (isset($errors) && $errors->any())

                    <div class="err-message">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                        <span>
                            {{ $errors->first() }}
                        </span>

                    </div>

                @endif


                <!-- FORMULAIRE -->

                <form
                    method="POST"
                    action="{{ route('admin.connexion.stocker') }}"
                    id="loginForm"
                >

                    @csrf


                    <!-- EMAIL -->

                    <div class="form-group">

                        <label for="email">
                            Adresse e-mail
                        </label>

                        <div class="input-wrapper">

                            <i class="fa-solid fa-envelope icon-left"></i>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="admin@cif-empreinte.com"
                                value="{{ old('email') }}"
                                required
                                autofocus
                            />

                        </div>

                    </div>


                    <!-- MOT DE PASSE -->

                    <div class="form-group">

                        <label for="mot_de_passe">
                            Mot de passe
                        </label>

                        <div class="input-wrapper">

                            <i class="fa-solid fa-lock icon-left"></i>

                            <input
                                type="password"
                                id="mot_de_passe"
                                name="mot_de_passe"
                                placeholder="••••••••••••"
                                required
                            />

                            <button
                                type="button"
                                class="toggle-pwd"
                                id="togglePassword"
                                aria-label="Afficher ou masquer le mot de passe"
                            >

                                <i
                                    class="fa-solid fa-eye"
                                    id="eyeIcon"
                                ></i>

                            </button>

                        </div>

                    </div>


                    <!-- BOUTON -->

                    <button
                        type="submit"
                        class="btn-submit"
                        id="loginBtn"
                    >

                        <span id="loginBtnLabel">
                            Accéder au tableau de bord
                        </span>

                        <i class="fa-solid fa-arrow-right-to-bracket"></i>

                    </button>

                </form>


                <!-- INFORMATION -->

                <div class="info-box">

                    <i class="fa-solid fa-circle-info"></i>

                    <p>
                        En cas de problème d'accès ou de compte bloqué,
                        veuillez contacter le super-administrateur.
                    </p>

                </div>

            </div>

        </section>

    </div>


    <!-- =========================================================
         FOOTER
    ========================================================== -->

    <footer>

        <div>

            <strong>{{ $identite['nom_systeme'] }}</strong>
            — Plateforme de Gestion des Accès

        </div>


        <div>

            <a
                href="{{ url('/connexion') }}"
                class="admin-link"
            >

                <i class="fa-solid fa-user-tie"></i>

                Espace Agent

            </a>

        </div>

    </footer>


    <!-- =========================================================
         JAVASCRIPT
    ========================================================== -->

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const togglePassword =
                document.getElementById('togglePassword');

            const passwordInput =
                document.getElementById('mot_de_passe');

            const eyeIcon =
                document.getElementById('eyeIcon');

            const form =
                document.getElementById('loginForm');

            const btn =
                document.getElementById('loginBtn');

            const btnLabel =
                document.getElementById('loginBtnLabel');


            /* =====================================================
               AFFICHER / MASQUER LE MOT DE PASSE
            ====================================================== */

            if (
                togglePassword &&
                passwordInput &&
                eyeIcon
            ) {

                togglePassword.addEventListener(
                    'click',
                    function () {

                        const isPassword =
                            passwordInput.getAttribute('type') === 'password';


                        passwordInput.setAttribute(
                            'type',
                            isPassword ? 'text' : 'password'
                        );


                        eyeIcon.classList.toggle(
                            'fa-eye',
                            !isPassword
                        );


                        eyeIcon.classList.toggle(
                            'fa-eye-slash',
                            isPassword
                        );

                    }
                );

            }


            /* =====================================================
               ANIMATION DE CONNEXION
            ====================================================== */

            if (
                form &&
                btn &&
                btnLabel
            ) {

                form.addEventListener(
                    'submit',
                    function () {

                        btn.style.opacity = '0.75';

                        btn.style.pointerEvents = 'none';

                        btnLabel.textContent =
                            'Authentification...';

                    }
                );

            }

        });

    </script>

</body>
</html>
