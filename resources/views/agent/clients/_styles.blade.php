<style>

    [x-cloak] {
        display: none !important;
    }


    /* =========================================================
       PAGE CLIENTS
    ========================================================= */

    .clients-page {

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

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    /* =========================================================
       BARRE D'ACTIONS
    ========================================================= */

    .clients-toolbar {

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


    .clients-toolbar-left {

        display: flex;

        align-items: center;

        gap: 9px;

        flex-wrap: wrap;
    }


    .clients-toolbar-search {

        display: inline-flex;

        align-items: center;

        gap: 9px;

        padding: 8px 14px;

        border-radius: 10px;

        background: var(--background);

        border: 1px solid var(--border);

        min-width: 260px;
    }


    .clients-toolbar-search i {

        font-size: 0.72rem;

        color: var(--muted);
    }


    .clients-toolbar-search input {

        flex: 1;

        border: none;

        background: transparent;

        outline: none;

        font-family: inherit;

        font-size: 0.72rem;

        font-weight: 600;

        color: var(--dark);
    }


    .clients-toolbar-search input::placeholder {

        color: var(--muted-light);

        font-weight: 500;
    }


    .filter-link {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 8px 12px;

        border-radius: 10px;

        background: var(--background);

        border: 1px solid var(--border);

        color: var(--dark);

        font-size: 0.68rem;

        font-weight: 700;

        text-decoration: none;

        transition:
            background 0.2s ease,
            border-color 0.2s ease,
            transform 0.2s ease;
    }


    .filter-link i {

        font-size: 0.72rem;

        color: var(--muted);
    }


    .filter-link:hover {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.45);

        transform: translateY(-1px);
    }


    .filter-link.active {

        background: var(--dark);

        border-color: var(--dark);

        color: #FFFFFF;
    }


    .filter-link.active i {

        color: var(--yellow);
    }


    .btn-primary {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        height: 38px;

        padding: 0 15px;

        border: none;

        border-radius: 10px;

        background: var(--yellow);

        color: var(--dark);

        font-family: inherit;

        font-size: 0.68rem;

        font-weight: 800;

        cursor: pointer;

        text-decoration: none;

        box-shadow:
            0 6px 14px rgba(240, 229, 53, 0.28);

        transition:
            background 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }


    .btn-primary i {

        font-size: 0.72rem;
    }


    .btn-primary:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow:
            0 10px 22px rgba(240, 229, 53, 0.34);
    }


    .btn-secondary {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        height: 38px;

        padding: 0 15px;

        border: 1.5px solid var(--dark);

        border-radius: 10px;

        background: var(--white);

        color: var(--dark);

        font-family: inherit;

        font-size: 0.68rem;

        font-weight: 800;

        cursor: pointer;

        text-decoration: none;

        transition:
            background 0.2s ease,
            transform 0.2s ease;
    }


    .btn-secondary i {

        font-size: 0.72rem;
    }


    .btn-secondary:hover {

        background: var(--background);

        transform: translateY(-2px);
    }


    /* =========================================================
       COMPTEUR
    ========================================================= */

    .clients-count {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 7px 11px;

        border-radius: 999px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.25);

        color: var(--dark);

        font-size: 0.62rem;

        font-weight: 800;
    }


    .clients-count i {

        color: #A08F00;

        font-size: 0.68rem;
    }


    /* =========================================================
       LISTE
    ========================================================= */

    .clients-list {

        display: flex;

        flex-direction: column;

        gap: 9px;
    }


    /* =========================================================
       CARTE CLIENT
    ========================================================= */

    .client-card {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        padding: 13px 15px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 16px;

        text-decoration: none;

        box-shadow: var(--shadow-sm);

        transition:
            transform 0.2s ease,
            border-color 0.2s ease,
            background 0.2s ease,
            box-shadow 0.2s ease;
    }


    .client-card:hover {

        transform: translateX(3px);

        border-color: rgba(240, 229, 53, 0.55);

        background: var(--yellow-light);

        box-shadow:
            0 8px 22px rgba(44, 52, 61, 0.06);
    }


    .client-card-left {

        display: flex;

        align-items: center;

        gap: 13px;

        min-width: 0;

        flex: 1;
    }


    .client-avatar {

        width: 42px;

        height: 42px;

        flex-shrink: 0;

        border-radius: 12px;

        background: var(--yellow-soft);

        color: var(--dark);

        border: 1px solid rgba(240, 229, 53, 0.28);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.85rem;
    }


    .client-card.morale .client-avatar {

        background: rgba(44, 52, 61, 0.06);

        border-color: rgba(44, 52, 61, 0.10);

        color: var(--dark);
    }


    .client-card-body {

        min-width: 0;

        display: flex;

        flex-direction: column;

        gap: 5px;
    }


    .client-name {

        color: var(--dark);

        font-size: 0.78rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .client-meta {

        display: flex;

        align-items: center;

        flex-wrap: wrap;

        gap: 6px;
    }


    .meta-chip {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 3px 8px;

        border-radius: 999px;

        background: var(--background);

        border: 1px solid var(--border);

        color: var(--muted);

        font-size: 0.56rem;

        font-weight: 700;

        white-space: nowrap;
    }


    .meta-chip i {

        font-size: 0.58rem;

        color: var(--muted-light);
    }


    .meta-chip.nature {

        background: var(--green-soft);

        border-color: rgba(48, 195, 26, 0.16);

        color: #249C13;
    }


    .meta-chip.nature i {

        color: var(--green);
    }


    /* =========================================================
       SCORE
    ========================================================= */

    .client-card-right {

        display: flex;

        align-items: center;

        gap: 12px;

        flex-shrink: 0;
    }


    .score-badge {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 6px 11px;

        border-radius: 999px;

        font-size: 0.62rem;

        font-weight: 800;

        white-space: nowrap;
    }


    .score-badge i {

        font-size: 0.66rem;
    }


    .score-badge.high {

        color: var(--green);

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.16);
    }


    .score-badge.mid {

        color: #9B8D00;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);
    }


    .score-badge.low {

        color: var(--danger);

        background: var(--danger-soft);

        border: 1px solid rgba(220, 38, 38, 0.14);
    }


    .client-arrow {

        width: 32px;

        height: 32px;

        border-radius: 10px;

        background: var(--background);

        border: 1px solid var(--border);

        color: var(--muted);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.68rem;

        transition:
            background 0.2s ease,
            color 0.2s ease,
            transform 0.2s ease;
    }


    .client-card:hover .client-arrow {

        background: var(--yellow);

        border-color: var(--yellow);

        color: var(--dark);

        transform: translateX(2px);
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .clients-empty {

        padding: 55px 20px;

        text-align: center;

        background: #FFFFFF;

        border: 1px dashed var(--border);

        border-radius: 18px;

        color: var(--muted);

        font-size: 0.72rem;

        font-weight: 600;

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 10px;
    }


    .clients-empty-icon {

        width: 58px;

        height: 58px;

        border-radius: 50%;

        background: var(--yellow-soft);

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.4rem;

        border: 1px solid rgba(240, 229, 53, 0.28);
    }


    .clients-empty strong {

        color: var(--dark);

        font-size: 0.85rem;

        font-weight: 800;
    }


    .clients-empty span {

        font-size: 0.66rem;

        color: var(--muted-light);

        max-width: 280px;

        line-height: 1.5;
    }


    /* =========================================================
       PAGINATION
    ========================================================= */

    .clients-pagination {

        display: flex;

        justify-content: center;

        padding-top: 6px;
    }


    .clients-pagination :deep(.pagination),
    .clients-pagination nav {

        width: 100%;
    }


    .clients-pagination svg {

        width: 14px;

        height: 14px;
    }


    /* Laravel pagination tailwind overrides */

    .clients-pagination a,
    .clients-pagination span[aria-current="page"] > span,
    .clients-pagination span[aria-disabled="true"] > span {

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


    .clients-pagination a:hover {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.45);

        color: var(--dark);
    }


    .clients-pagination span[aria-current="page"] > span {

        background: var(--dark);

        border-color: var(--dark);

        color: #FFFFFF;
    }


    .clients-pagination span[aria-disabled="true"] > span {

        opacity: 0.5;

        cursor: not-allowed;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 700px) {

        .clients-toolbar {

            flex-direction: column;

            align-items: stretch;

            gap: 10px;
        }


        .clients-toolbar-left {

            justify-content: space-between;
        }


        .btn-primary {

            justify-content: center;

            width: 100%;
        }


        .client-card {

            align-items: flex-start;

            padding: 12px;
        }


        .client-card-right {

            flex-direction: column;

            align-items: flex-end;

            gap: 8px;
        }


        .client-arrow {

            display: none;
        }
    }


    @media (max-width: 480px) {

        .client-avatar {

            width: 36px;

            height: 36px;

            border-radius: 10px;

            font-size: 0.75rem;
        }


        .client-name {

            font-size: 0.72rem;
        }


        .meta-chip {

            font-size: 0.53rem;

            padding: 3px 7px;
        }


        .score-badge {

            font-size: 0.58rem;

            padding: 5px 9px;
        }
    }


    /* =========================================================
       FORMULAIRE FICHE D'ADHÉSION (creer / completer / import)
    ========================================================= */

    .fiche-groupe {

        background: var(--white);

        border: 1px solid var(--border);

        border-radius: 12px;

        box-shadow: var(--shadow-sm);

        margin-bottom: 12px;

        overflow: hidden;
    }


    .fiche-groupe > summary {

        cursor: pointer;

        list-style: none;

        padding: 14px 18px;

        font-weight: 700;

        font-size: 0.85rem;

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: space-between;

        background: var(--yellow-light);
    }


    .fiche-groupe > summary::-webkit-details-marker {

        display: none;
    }


    .fiche-groupe-corps {

        padding: 16px 18px;

        display: grid;

        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));

        gap: 14px;
    }


    .champ-fiche {

        display: flex;

        flex-direction: column;

        gap: 5px;
    }


    .champ-fiche-label {

        font-size: 0.68rem;

        font-weight: 600;

        color: var(--muted);

        display: flex;

        align-items: center;

        gap: 6px;

        flex-wrap: wrap;
    }


    .champ-fiche-input {

        height: 36px;

        border-radius: 8px;

        border: 1px solid var(--border);

        padding: 0 10px;

        font-family: inherit;

        font-size: 0.78rem;

        color: var(--text);

        background: var(--white);
    }


    .champ-manquant .champ-fiche-input {

        border-color: var(--yellow);

        background: var(--yellow-soft);
    }



    /* Champ obligatoire manquant ou invalide : cercle rouge */
    .champ-erreur .champ-fiche-input,
    .champ-erreur select,
    .champ-erreur input[type="text"] {
        border: 2px solid #DC2626 !important;
        background: rgba(220, 38, 38, 0.06) !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.18);
        border-radius: 10px;
    }

    .champ-erreur .champ-fiche-label {
        color: #DC2626;
    }

    .champ-erreur-message {
        margin: 4px 0 0;
        font-size: 0.72rem;
        font-weight: 600;
        color: #DC2626;
    }

    .champ-obligatoire {

        color: var(--danger);
    }


    .champ-confiance {

        border-radius: 6px;

        padding: 1px 6px;

        font-size: 0.6rem;

        font-weight: 700;
    }


    .champ-confiance-haute {

        background: var(--green-soft);

        color: var(--green);
    }


    .champ-confiance-basse {

        background: var(--yellow-soft);

        color: #8A7C00;
    }


    .champ-npi {

        display: flex;

        align-items: center;

        gap: 8px;
    }


    .champ-npi-statut {

        font-size: 0.85rem;

        min-width: 18px;
    }


    .fiche-repetable-item {

        border: 1px dashed var(--border);

        border-radius: 10px;

        padding: 14px;

        margin-bottom: 10px;

        display: grid;

        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));

        gap: 12px;

        position: relative;
    }


    .fiche-repetable-retirer {

        position: absolute;

        top: 8px;

        right: 8px;

        border: none;

        background: var(--danger-soft);

        color: var(--danger);

        border-radius: 6px;

        font-size: 0.65rem;

        font-weight: 700;

        padding: 3px 8px;

        cursor: pointer;
    }


    .fiche-ajouter {

        border: 1.5px dashed var(--dark);

        background: transparent;

        color: var(--dark);

        border-radius: 8px;

        padding: 7px 12px;

        font-size: 0.68rem;

        font-weight: 700;

        cursor: pointer;
    }


    .fiche-total-versements {

        background: var(--green-soft);

        border-radius: 8px;

        padding: 8px 12px;

        font-weight: 700;

        color: var(--dark);

        font-size: 0.8rem;
    }

</style>
