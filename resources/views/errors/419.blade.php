<?php
// Exemple d'intégration Blade / Laravel ou PHP natif
$errors = $errors ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $identite['nom_systeme'] }} — Session expirée</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
            margin-bottom: 24px;

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
           ICÔNE D'EXPIRATION
        ========================================================= */

        .expiration-badge {
            display: flex;

            align-items: center;

            gap: 14px;

            padding: 14px 16px;

            background: var(--yellow-soft);

            border: 1px solid var(--yellow-medium);

            border-radius: 14px;

            margin-bottom: 22px;
        }

        .expiration-badge-icon {
            width: 44px;
            height: 44px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #FFFFFF;

            border: 1px solid var(--yellow-medium);

            border-radius: 12px;

            color: var(--dark-accent);

            font-size: 1.05rem;
        }

        .expiration-badge-text {
            display: flex;

            flex-direction: column;

            gap: 3px;
        }

        .expiration-badge-text strong {
            font-size: 0.78rem;

            font-weight: 800;

            color: var(--dark-accent);

            letter-spacing: -0.2px;
        }

        .expiration-badge-text span {
            font-size: 0.68rem;

            color: #665D00;

            font-weight: 500;

            line-height: 1.45;
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

            text-decoration: none;

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

                Terminal Actif

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

                    <i class="fa-solid fa-lock"></i>

                    Session terminée

                </span>


                {{-- Titre en un seul flux de texte : « Votre session a expiré »
                     reste contigu pour les tests assertStringContainsString,
                     la coloration jaune ne porte que sur le fragment final. --}}
                <h2>
                    Votre session <span>a expiré</span>
                </h2>


                <p class="desc">
                    Pour votre sécurité, la session s'est fermée automatiquement
                    après une période d'inactivité. Vos données sont protégées
                    et aucune opération n'a été enregistrée.
                </p>


                <div class="feature-list">

                    <div class="feature-item">

                        <div class="feature-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>

                        <span>
                            Déconnexion automatique de sécurité
                        </span>

                    </div>


                    <div class="feature-item">

                        <div class="feature-icon">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>

                        <span>
                            Reconnexion immédiate possible
                        </span>

                    </div>


                    <div class="feature-item">

                        <div class="feature-icon">
                            <i class="fa-solid fa-user-lock"></i>
                        </div>

                        <span>
                            Vos accès et droits sont préservés
                        </span>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CARTE SESSION EXPIRÉE
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
                        Session expirée
                    </span>

                    <h1>
                        Reconnexion
                    </h1>

                    <p>
                        Votre session a été fermée. Reconnectez-vous
                        pour continuer votre travail.
                    </p>

                </div>


                <!-- BADGE EXPIRATION -->
                <div class="expiration-badge">

                    <div class="expiration-badge-icon">

                        <i class="fa-solid fa-hourglass-end"></i>

                    </div>


                    <div class="expiration-badge-text">

                        <strong>
                            Temps d'inactivité dépassé
                        </strong>

                        <span>
                            Pour des raisons de sécurité, votre session
                            a été automatiquement terminée.
                        </span>

                    </div>

                </div>


                @php
                    // Deux espaces de connexion réels : agent.connexion.creer (partagé par les
                    // rôles caissier et responsable d'agence, qui utilisent la même page — voir
                    // 09_PROMPT_TROIS_PROFILS) et admin.connexion.creer. Origine déterminée
                    // uniquement si l'URL précédente n'est pas la racine du site (repli par
                    // défaut de url()->previous() en l'absence de référent) ; sinon, consigne du
                    // prompt : admin.connexion.creer par défaut.
                    $urlPrecedente = url()->previous();
                    $origineDeterminee = $urlPrecedente !== url('/');

                    $urlReconnexion = $origineDeterminee && ! str_contains($urlPrecedente, '/admin')
                        ? route('agent.connexion.creer')
                        : route('admin.connexion.creer');
                @endphp


                <!-- BOUTON DE RECONNEXION -->
                <a
                    href="{{ $urlReconnexion }}"
                    class="btn-submit"
                >

                    <i class="fa-solid fa-arrow-right-to-bracket"></i>

                    <span>
                        Se reconnecter
                    </span>

                </a>


                <!-- INFORMATION -->

                <div class="info-box">

                    <i class="fa-solid fa-circle-info"></i>

                    <p>
                        En cas de problème persistant de connexion,
                        veuillez contacter le responsable d'agence.
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
                href="{{ url('/admin/connexion') }}"
                class="admin-link"
            >

                <i class="fa-solid fa-user-shield"></i>

                Espace Administration

            </a>

        </div>

    </footer>


</body>

</html>
