@extends('layouts.admin')


@section('sous-titre', 'Gérez les comptes agents, leurs rôles et leurs accès à la plateforme.')


@section('contenu')

<style>

    /* =========================================================
       PAGE COMPTES AGENTS
    ========================================================= */

    .agents-page {

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

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    /* =========================================================
       HERO
    ========================================================= */

    .agents-hero {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        flex-wrap: wrap;

        padding: 18px 22px;

        border-radius: 20px;

        background:
            linear-gradient(
                135deg,
                #2C343D 0%,
                #3A4650 65%,
                #303A43 100%
            );

        position: relative;

        overflow: hidden;

        box-shadow: var(--shadow);
    }


    .agents-hero::before {

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


    .agents-hero-left {

        display: flex;

        align-items: center;

        gap: 15px;

        position: relative;

        z-index: 2;

        min-width: 0;
    }


    .agents-hero-icon {

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


    .agents-hero-text {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .agents-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .agents-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* =========================================================
       BOUTON PRINCIPAL (nouveau compte, envoyer pdf)
    ========================================================= */

    .btn-nouveau {

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

        cursor: pointer;

        text-decoration: none;

        box-shadow:
            0 8px 20px rgba(240, 229, 53, 0.32);

        transition:
            background 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;

        position: relative;

        z-index: 2;

        white-space: nowrap;
    }


    .btn-nouveau i {

        font-size: 0.78rem;

        transition: transform 0.2s ease;
    }


    .btn-nouveau:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow:
            0 12px 26px rgba(240, 229, 53, 0.36);
    }


    .btn-nouveau:hover i {

        transform: translateY(-2px);
    }


    /* =========================================================
       ALERTE IDENTIFIANTS GÉNÉRÉS
    ========================================================= */

    .identifiants-alert {

        display: flex;

        align-items: flex-start;

        gap: 14px;

        padding: 16px 18px;

        border-radius: 14px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.35);

        box-shadow: var(--shadow-sm);

        animation: fadeIn 0.35s ease-out;
    }


    @keyframes fadeIn {

        from {
            opacity: 0;
            transform: translateY(-4px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    .identifiants-alert-icon {

        width: 40px;

        height: 40px;

        flex-shrink: 0;

        border-radius: 11px;

        background: #FFFFFF;

        border: 1px solid rgba(240, 229, 53, 0.40);

        color: #A08F00;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1rem;
    }


    .identifiants-alert-body {

        display: flex;

        flex-direction: column;

        gap: 10px;

        min-width: 0;

        flex: 1;
    }


    .identifiants-alert-title {

        color: var(--dark);

        font-size: 0.8rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .identifiants-alert-code {

        display: flex;

        flex-wrap: wrap;

        gap: 12px 22px;
    }


    .identifiant-item {

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .identifiant-item-label {

        font-size: 0.56rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: #8A7C00;
    }


    .identifiant-item-value {

        font-family: 'JetBrains Mono', ui-monospace, monospace;

        font-size: 0.84rem;

        font-weight: 800;

        color: var(--dark);

        background: #FFFFFF;

        border: 1px solid rgba(240, 229, 53, 0.40);

        padding: 5px 11px;

        border-radius: 8px;

        letter-spacing: 0.5px;
    }


    /* =========================================================
       BARRE FILTRES
    ========================================================= */

    .agents-toolbar {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        flex-wrap: wrap;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 16px;

        padding: 12px 16px;

        box-shadow: var(--shadow-sm);
    }


    .agents-filters {

        display: flex;

        align-items: center;

        gap: 10px;

        flex-wrap: wrap;

        flex: 1;

        min-width: 0;
    }


    .agents-filters i.filter-icon {

        color: var(--muted);

        font-size: 0.78rem;
    }


    .agents-filters select {

        height: 40px;

        padding: 0 14px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #F8FAFC;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.72rem;

        font-weight: 700;

        cursor: pointer;

        outline: none;

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            box-shadow 0.2s ease;

        min-width: 180px;
    }


    .agents-filters select:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .agents-filters select:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow:
            0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    .agents-filters input[type="text"] {

        height: 40px;

        padding: 0 14px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #F8FAFC;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.72rem;

        font-weight: 700;

        outline: none;

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            box-shadow 0.2s ease;

        min-width: 200px;

        flex: 1;
    }


    .agents-filters input[type="text"]:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .agents-filters input[type="text"]:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow:
            0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    .agents-filters-submit {

        width: 40px;

        height: 40px;

        flex-shrink: 0;

        border: none;

        border-radius: 11px;

        background: var(--dark);

        color: #FFFFFF;

        display: flex;

        align-items: center;

        justify-content: center;

        cursor: pointer;

        font-size: 0.78rem;

        transition: background 0.2s ease, transform 0.2s ease;
    }


    .agents-filters-submit:hover {

        background: #3A4650;

        transform: translateY(-1px);
    }


    /* =========================================================
       BARRE ENVOI PDF (filtre actif)
    ========================================================= */

    .agents-pdf-bar {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        flex-wrap: wrap;

        padding: 14px 18px;

        background:
            linear-gradient(
                135deg,
                #FFFFFF 0%,
                #FFFDF0 100%
            );

        border: 1px solid rgba(240, 229, 53, 0.45);

        border-radius: 16px;

        box-shadow:
            0 6px 20px rgba(240, 229, 53, 0.10);

        animation: fadeIn 0.35s ease-out;
    }


    .agents-pdf-info {

        display: inline-flex;

        align-items: center;

        gap: 10px;

        color: var(--dark);

        font-size: 0.72rem;

        font-weight: 600;

    }


    .agents-pdf-info i {

        width: 32px;

        height: 32px;

        flex-shrink: 0;

        border-radius: 10px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.35);

        color: #8A7C00;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.78rem;
    }


    .agents-pdf-info strong {

        color: var(--dark);

        font-weight: 800;

    }


    /* =========================================================
       CARTE TABLEAU
    ========================================================= */

    .agents-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        width: 100%;
    }


    /* =========================================================
       TABLEAU
    ========================================================= */

    .agents-table {

        width: 100%;

        border-collapse: collapse;

        font-size: 0.78rem;
    }


    .agents-table thead {

        background: var(--background);

        border-bottom: 1px solid var(--border);
    }


    .agents-table th {

        padding: 14px 16px;

        text-align: left;

        font-size: 0.6rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted);

        white-space: nowrap;
    }


    .agents-table td {

        padding: 13px 16px;

        border-bottom: 1px solid var(--border);

        color: var(--dark);

        vertical-align: middle;
    }


    .agents-table tbody tr {

        transition: background 0.2s ease;
    }


    .agents-table tbody tr:hover {

        background: var(--yellow-light);
    }


    .agents-table tbody tr:last-child td {

        border-bottom: none;
    }


    /* =========================================================
       CELLULE NOM
    ========================================================= */

    .agent-info {

        display: flex;

        align-items: center;

        gap: 10px;

    }


    .agent-avatar {

        width: 34px;

        height: 34px;

        flex-shrink: 0;

        border-radius: 10px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.25);

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.72rem;
    }


    .agent-info-text {

        display: flex;

        flex-direction: column;

        gap: 2px;

        min-width: 0;
    }


    .agent-info-text strong {

        color: var(--dark);

        font-size: 0.76rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .agent-info-text span {

        color: var(--muted-light);

        font-size: 0.6rem;

        font-weight: 600;
    }


    /* =========================================================
       CELLULE AGENCE
    ========================================================= */

    .agent-agence {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        font-size: 0.72rem;

        font-weight: 600;

        color: var(--muted);

    }


    .agent-agence i {

        color: var(--muted-light);

        font-size: 0.68rem;
    }


    .agent-agence-reseau {

        color: var(--dark);

        font-weight: 800;
    }


    /* =========================================================
       BADGE RÔLE
    ========================================================= */

    .badge-role {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 10px;

        border-radius: 999px;

        font-size: 0.62rem;

        font-weight: 800;

        white-space: nowrap;

        background: var(--background);

        border: 1px solid var(--border);

        color: var(--dark);
    }


    .badge-role i {

        font-size: 0.62rem;

    }


    .badge-role.caissier {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.28);

        color: #8A7C00;
    }


    .badge-role.caissier i {

        color: #8A7C00;
    }


    .badge-role.responsable {

        background: rgba(44, 52, 61, 0.06);

        border-color: rgba(44, 52, 61, 0.10);

        color: var(--dark);
    }


    .badge-role.responsable i {

        color: var(--dark);
    }


    /* =========================================================
       BADGE STATUT
    ========================================================= */

    .badge-statut {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 10px;

        border-radius: 999px;

        font-size: 0.62rem;

        font-weight: 800;

        white-space: nowrap;
    }


    .badge-statut i {

        font-size: 0.62rem;
    }


    .badge-statut.actif {

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.18);

        color: #249C13;
    }


    .badge-statut.desactive {

        background: rgba(148, 163, 184, 0.10);

        border: 1px solid rgba(148, 163, 184, 0.20);

        color: var(--muted);
    }


    /* =========================================================
       ACTIONS
    ========================================================= */

    .agents-actions {

        display: flex;

        align-items: center;

        gap: 8px;

        flex-wrap: wrap;
    }


    .btn-action {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 6px;

        height: 32px;

        padding: 0 12px;

        border-radius: 9px;

        font-family: inherit;

        font-size: 0.62rem;

        font-weight: 800;

        cursor: pointer;

        transition:
            background 0.2s ease,
            border-color 0.2s ease,
            transform 0.2s ease;
    }


    .btn-action i {

        font-size: 0.64rem;
    }


    .btn-action.desactiver {

        background: var(--danger-soft);

        border: 1px solid rgba(220, 38, 38, 0.20);

        color: var(--danger);
    }


    .btn-action.desactiver:hover {

        background: rgba(220, 38, 38, 0.16);

        border-color: rgba(220, 38, 38, 0.35);

        transform: translateY(-1px);
    }


    .btn-action.activer {

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: #238E15;
    }


    .btn-action.activer:hover {

        background: rgba(48, 195, 26, 0.16);

        border-color: rgba(48, 195, 26, 0.35);

        transform: translateY(-1px);
    }


    .btn-action.reset {

        background: var(--background);

        border: 1px solid var(--border);

        color: var(--dark);
    }


    .btn-action.reset:hover {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.45);

        transform: translateY(-1px);
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .agents-empty {

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 12px;

        padding: 55px 25px;

        text-align: center;

        color: var(--muted);
    }


    .agents-empty-icon {

        width: 58px;

        height: 58px;

        border-radius: 50%;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.4rem;
    }


    .agents-empty strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;
    }


    .agents-empty span {

        font-size: 0.68rem;

        color: var(--muted-light);

        max-width: 320px;

        line-height: 1.5;
    }


    /* =========================================================
       PAGINATION
    ========================================================= */

    .agents-pagination {

        display: flex;

        justify-content: center;

        padding: 6px 0;
    }


    .agents-pagination svg {

        width: 14px;

        height: 14px;
    }


    .agents-pagination a,
    .agents-pagination span[aria-current="page"] > span,
    .agents-pagination span[aria-disabled="true"] > span {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-width: 34px;

        height: 34px;

        padding: 0 10px;

        margin: 0 2px;

        border-radius: 10px;

        border: 1px solid var(--border);

        background: #FFFFFF;

        color: var(--muted);

        font-size: 0.68rem;

        font-weight: 700;

        text-decoration: none;

        box-shadow: var(--shadow-sm);

        transition:
            background 0.2s ease,
            border-color 0.2s ease,
            color 0.2s ease;
    }


    .agents-pagination a:hover {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.45);

        color: var(--dark);
    }


    .agents-pagination span[aria-current="page"] > span {

        background: var(--dark);

        border-color: var(--dark);

        color: #FFFFFF;
    }


    /* =========================================================
       MODALE ENVOI PDF
    ========================================================= */

    .modale-overlay {

        display: none;

        position: fixed;

        inset: 0;

        background: rgba(44, 52, 61, 0.55);

        backdrop-filter: blur(6px);

        -webkit-backdrop-filter: blur(6px);

        align-items: center;

        justify-content: center;

        z-index: 999;

        padding: 20px;

        animation: fadeIn 0.2s ease-out;
    }


    .modale-boite {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        padding: 26px 28px;

        width: 100%;

        max-width: 440px;

        box-shadow:
            0 28px 60px rgba(44, 52, 61, 0.28),
            0 8px 20px rgba(44, 52, 61, 0.12);

        position: relative;

        overflow: hidden;

        animation: modaleSlide 0.28s ease-out;
    }


    .modale-boite::before {

        content: "";

        position: absolute;

        top: 0;
        left: 0;
        right: 0;

        height: 4px;

        background:
            linear-gradient(
                90deg,
                var(--dark),
                var(--yellow)
            );
    }


    @keyframes modaleSlide {

        from {
            opacity: 0;
            transform: translateY(12px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }


    .modale-entete {

        display: flex;

        align-items: center;

        gap: 12px;

        margin-bottom: 20px;
    }


    .modale-entete-icon {

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

        font-size: 0.95rem;
    }


    .modale-entete-texte {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;

        flex: 1;
    }


    .modale-boite h3 {

        margin: 0;

        font-size: 0.92rem;

        font-weight: 800;

        color: var(--dark);

        letter-spacing: -0.3px;
    }


    .modale-entete-texte span {

        font-size: 0.68rem;

        color: var(--muted);

        font-weight: 500;

        line-height: 1.45;
    }


    .modale-champs {

        display: flex;

        flex-direction: column;

        gap: 16px;

    }


    .modale-actions {

        display: flex;

        justify-content: flex-end;

        gap: 10px;

        margin-top: 22px;

    }


    .modale-actions .btn-action.reset {

        height: 44px;

        padding: 0 20px;

        border-radius: 12px;

        font-size: 0.72rem;

    }


    .modale-actions .btn-nouveau {

        height: 44px;

    }


    /* =========================================================
       CHAMPS (modale)
    ========================================================= */

    .champ-agent {

        display: flex;

        flex-direction: column;

        gap: 7px;

        min-width: 0;
    }


    .champ-agent-label {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        font-size: 0.62rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--dark);
    }


    .champ-agent-label i {

        color: var(--muted);

        font-size: 0.72rem;
    }


    .champ-agent-label .obligatoire {

        color: var(--danger);

        margin-left: 2px;
    }


    .champ-agent-input {

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

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            box-shadow 0.2s ease;
    }


    .champ-agent-input:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .champ-agent-input:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow:
            0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    .champ-agent-input::placeholder {

        color: var(--muted-light);

        font-weight: 400;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 900px) {

        .agents-table th,
        .agents-table td {

            padding: 11px 12px;
        }
    }


    @media (max-width: 700px) {

        .agents-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .agents-hero-text strong {

            font-size: 0.9rem;
        }


        .btn-nouveau {

            width: 100%;

            justify-content: center;
        }


        .agents-toolbar {

            flex-direction: column;

            align-items: stretch;
        }


        .agents-filters {

            flex-direction: column;

            align-items: stretch;
        }


        .agents-filters select {

            width: 100%;

        }


        .agents-pdf-bar {

            flex-direction: column;

            align-items: stretch;

        }


        .agents-pdf-bar .btn-nouveau {

            width: 100%;

            justify-content: center;

        }


        /* Tableau en cartes empilées */

        .agents-table,
        .agents-table thead,
        .agents-table tbody,
        .agents-table tr,
        .agents-table td,
        .agents-table th {

            display: block;

            width: 100%;
        }


        .agents-table thead {

            display: none;
        }


        .agents-table tbody tr {

            padding: 14px;

            border-bottom: 1px solid var(--border);

            background: #FFFFFF;
        }


        .agents-table tbody tr:hover {

            background: var(--yellow-light);
        }


        .agents-table td {

            padding: 8px 0;

            border: none;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 12px;
        }


        .agents-table td::before {

            content: attr(data-label);

            font-size: 0.58rem;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            color: var(--muted);

            flex-shrink: 0;
        }


        .agent-info {

            justify-content: flex-end;

            text-align: right;
        }


        .agents-actions {

            justify-content: flex-end;
        }
    }

</style>


<div class="agents-page">


    {{-- =====================================================
         HERO
    ====================================================== --}}

    <div class="agents-hero">

        <div class="agents-hero-left">

            <div class="agents-hero-icon">

                <i class="fa-solid fa-users-gear"></i>

            </div>


            <div class="agents-hero-text">

                <strong>
                    Comptes agents
                </strong>

                <span>
                    Gérez les caissiers et responsables d'agence.
                </span>

            </div>

        </div>


        <a
            href="{{ route('admin.agents.creer') }}"
            class="btn-nouveau"
        >

            <i class="fa-solid fa-user-plus"></i>

            Nouveau compte

        </a>

    </div>



    {{-- =====================================================
         ERREURS
    ====================================================== --}}

    @if ($errors->any())

        <div class="identifiants-alert" style="background: #FEF2F2; border-color: #FECACA;">

            <div class="identifiants-alert-icon" style="background: rgba(220, 38, 38, 0.10); border-color: #FECACA; color: var(--danger);">

                <i class="fa-solid fa-triangle-exclamation"></i>

            </div>


            <div class="identifiants-alert-body">

                <div class="identifiants-alert-title" style="color: var(--danger);">

                    {{ $errors->first() }}

                </div>

            </div>

        </div>

    @endif



    {{-- =====================================================
         IDENTIFIANTS GÉNÉRÉS
    ====================================================== --}}

    @if (session('identifiants_generes'))

        @php $identifiants = session('identifiants_generes'); @endphp


        <div class="identifiants-alert">

            <div class="identifiants-alert-icon">

                <i class="fa-solid fa-key"></i>

            </div>


            <div class="identifiants-alert-body">

                <div class="identifiants-alert-title">

                    Identifiants générés — à noter maintenant, ils ne seront plus affichés.

                </div>


                <div class="identifiants-alert-code">

                    @if ($identifiants['matricule'])

                        <div class="identifiant-item">

                            <span class="identifiant-item-label">
                                Matricule
                            </span>

                            <span class="identifiant-item-value">
                                {{ $identifiants['matricule'] }}
                            </span>

                        </div>

                    @endif


                    <div class="identifiant-item">

                        <span class="identifiant-item-label">
                            Mot de passe
                        </span>

                        <span class="identifiant-item-value">
                            {{ $identifiants['mot_de_passe'] }}
                        </span>

                    </div>

                </div>

            </div>

        </div>

    @endif



    {{-- =====================================================
         BARRE FILTRES
    ====================================================== --}}

    <div class="agents-toolbar">

        <form method="GET" class="agents-filters">

            <i class="fa-solid fa-filter filter-icon"></i>


            <input
                type="text"
                name="q"
                value="{{ $recherche }}"
                placeholder="Nom, matricule ou e-mail..."
                aria-label="Rechercher un agent"
            >


            <select
                name="agence_id"
                onchange="this.form.submit()"
            >

                <option value="">
                    Toutes les agences
                </option>


                @foreach ($agences as $agence)

                    <option
                        value="{{ $agence->id }}"
                        @selected(request('agence_id') == $agence->id)
                    >
                        {{ $agence->reseau->nom }} — {{ $agence->nom }}
                    </option>

                @endforeach

            </select>


            <select
                name="role"
                onchange="this.form.submit()"
            >

                <option value="">
                    Tous les rôles
                </option>

                <option
                    value="caissier"
                    @selected(request('role') === 'caissier')
                >
                    Caissier
                </option>

                <option
                    value="responsable_agence"
                    @selected(request('role') === 'responsable_agence')
                >
                    Responsable d'agence
                </option>

            </select>


            <button type="submit" class="agents-filters-submit" aria-label="Lancer la recherche">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

        </form>

    </div>



    {{-- =====================================================
         BARRE ENVOI PDF (filtre actif uniquement)
    ====================================================== --}}

    @if (request()->filled('agence_id') || request()->filled('role'))

        <div class="agents-pdf-bar">

            <div class="agents-pdf-info">

                <i class="fa-solid fa-filter-circle-dollar"></i>

                <span>
                    Vue filtrée active —
                    <strong>{{ $agents->total() }}</strong>
                    agent(s) correspondant(s)
                </span>

            </div>


            <button
                type="button"
                class="btn-nouveau"
                onclick="document.getElementById('modale-envoi-pdf').style.display='flex'; document.getElementById('modale-email').focus();"
            >

                <i class="fa-solid fa-paper-plane"></i>

                Envoyer la liste par e-mail

            </button>

        </div>

    @endif



    {{-- =====================================================
         TABLEAU
    ====================================================== --}}

    <div class="agents-card">


        @if ($agents->count())


            <table class="agents-table">

                <thead>

                    <tr>

                        <th>Nom</th>

                        <th>Agence</th>

                        <th>Rôle</th>

                        <th>Statut</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($agents as $agent)

                        <tr>


                            {{-- NOM --}}

                            <td data-label="Nom">

                                <div class="agent-info">

                                    <div class="agent-avatar">

                                        <i class="fa-solid fa-user"></i>

                                    </div>


                                    <div class="agent-info-text">

                                        <strong>
                                            {{ $agent->nom }}
                                        </strong>

                                    </div>

                                </div>

                            </td>


                            {{-- AGENCE --}}

                            <td data-label="Agence">

                                <span class="agent-agence">

                                    <i class="fa-solid fa-building"></i>

                                    <span>

                                        <span class="agent-agence-reseau">
                                            {{ $agent->agence->reseau->nom }}
                                        </span>

                                        — {{ $agent->agence->nom }}

                                    </span>

                                </span>

                            </td>


                            {{-- RÔLE --}}

                            <td data-label="Rôle">

                                @php

                                    $role = $agent->role->value ?? null;
                                    $roleClass = $role === 'caissier' ? 'caissier' : 'responsable';
                                    $roleIcon = $role === 'caissier'
                                        ? 'fa-cash-register'
                                        : 'fa-user-tie';

                                @endphp


                                <span class="badge-role {{ $roleClass }}">

                                    <i class="fa-solid {{ $roleIcon }}"></i>

                                    {{ $agent->role->libelle($agent->civilite) }}

                                </span>

                            </td>


                            {{-- STATUT --}}

                            <td data-label="Statut">

                                @if ($agent->actif)

                                    <span class="badge-statut actif">

                                        <i class="fa-solid fa-circle-check"></i>

                                        Actif

                                    </span>

                                @else

                                    <span class="badge-statut desactive">

                                        <i class="fa-solid fa-circle-pause"></i>

                                        Désactivé

                                    </span>

                                @endif

                            </td>


                            {{-- ACTIONS --}}

                            <td data-label="Actions">

                                <div class="agents-actions">


                                    {{-- ACTIVER / DÉSACTIVER --}}

                                    <form
                                        method="POST"
                                        action="{{ route('admin.agents.activer-desactiver', $agent) }}"
                                    >

                                        @csrf
                                        @method('PUT')


                                        @if ($agent->actif)

                                            <button type="submit" class="btn-action desactiver">

                                                <i class="fa-solid fa-ban"></i>

                                                Désactiver

                                            </button>

                                        @else

                                            <button type="submit" class="btn-action activer">

                                                <i class="fa-solid fa-check"></i>

                                                Activer

                                            </button>

                                        @endif

                                    </form>


                                    {{-- RÉINITIALISER MOT DE PASSE --}}

                                    <form
                                        method="POST"
                                        action="{{ route('admin.agents.reinitialiser-mot-de-passe', $agent) }}"
                                    >

                                        @csrf
                                        @method('PUT')


                                        <button type="submit" class="btn-action reset">

                                            <i class="fa-solid fa-key"></i>

                                            Réinitialiser

                                        </button>

                                    </form>


                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>


        @else


            {{-- ÉTAT VIDE --}}

            <div class="agents-empty">

                <div class="agents-empty-icon">

                    <i class="fa-solid fa-users-slash"></i>

                </div>


                <strong>
                    @if ($recherche !== '')
                        Aucun agent ne correspond à « {{ $recherche }} »
                    @else
                        Aucun compte pour le moment
                    @endif
                </strong>


                <span>
                    @if ($recherche !== '')
                        Essayez avec un autre nom, matricule ou e-mail.
                    @else
                        Commencez par créer un nouveau compte caissier
                        ou responsable d'agence.
                    @endif
                </span>

            </div>

        @endif


    </div>



    {{-- =====================================================
         PAGINATION
    ====================================================== --}}

    @if ($agents->hasPages())

        <div class="agents-pagination">

            {{ $agents->links() }}

        </div>

    @endif



    {{-- =====================================================
         MODALE ENVOI PDF PAR EMAIL
    ====================================================== --}}

    <div
        id="modale-envoi-pdf"
        class="modale-overlay"
        onclick="if (event.target === this) this.style.display = 'none';"
    >

        <div class="modale-boite">

            <div class="modale-entete">

                <div class="modale-entete-icon">

                    <i class="fa-solid fa-file-pdf"></i>

                </div>


                <div class="modale-entete-texte">

                    <h3>
                        Envoyer la liste des identifiants
                    </h3>

                    <span>
                        Le PDF sera protégé par le mot de passe que vous choisirez.
                    </span>

                </div>

            </div>


            <form method="POST" action="{{ route('admin.agents.envoyer-pdf') }}">

                @csrf


                <input type="hidden" name="agence_id" value="{{ request('agence_id') }}">

                <input type="hidden" name="role" value="{{ request('role') }}">


                <div class="modale-champs">


                    {{-- EMAIL --}}

                    <div class="champ-agent">

                        <label class="champ-agent-label" for="modale-email">

                            <i class="fa-solid fa-envelope"></i>

                            Adresse e-mail destinataire

                            <span class="obligatoire">*</span>

                        </label>


                        <input
                            type="email"
                            name="email"
                            id="modale-email"
                            required
                            class="champ-agent-input"
                            placeholder="destinataire@exemple.com"
                            autocomplete="email"
                        >

                    </div>


                    {{-- MOT DE PASSE PDF --}}

                    <div class="champ-agent">

                        <label class="champ-agent-label" for="modale-mot-de-passe">

                            <i class="fa-solid fa-lock"></i>

                            Mot de passe du PDF

                            <span class="obligatoire">*</span>

                        </label>


                        <input
                            type="text"
                            name="mot_de_passe_pdf"
                            id="modale-mot-de-passe"
                            required
                            minlength="4"
                            class="champ-agent-input"
                            placeholder="Code pour ouvrir le PDF"
                        >

                    </div>


                </div>


                <div class="modale-actions">

                    <button
                        type="button"
                        onclick="document.getElementById('modale-envoi-pdf').style.display='none'"
                        class="btn-action reset"
                    >

                        Annuler

                    </button>


                    <button type="submit" class="btn-nouveau">

                        <i class="fa-solid fa-paper-plane"></i>

                        Envoyer

                    </button>

                </div>

            </form>

        </div>

    </div>


</div>

@endsection
