@extends('layouts.responsable')


@section('sous-titre', $client->nomAffichage().' — '.$client->statut_ppe->libelle())

@section('contenu')

<style>

    /* =========================================================
       PAGE OPÉRATIONS D'UNE PPE — RESPONSABLE
       Palette : jaune signature + bleu nuit + icônes bleu foncé
    ========================================================= */

    .ppe-ops-page {

        --yellow: #F0E535;
        --yellow-soft: rgba(240, 229, 53, 0.12);
        --yellow-light: #FFFDE7;

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --dark: #2C343D;
        --dark-soft: #3C4650;

        --accent: #2563EB;
        --accent-dark: #1D4ED8;
        --accent-soft: rgba(37, 99, 235, 0.10);

        --text: #1E293B;
        --muted: #64748B;
        --muted-light: #94A3B8;

        --border: #E8EDF2;
        --white: #FFFFFF;
        --background: #F8FAFC;

        --danger: #DC2626;
        --danger-soft: rgba(220, 38, 38, 0.08);

        --shadow-sm: 0 4px 15px rgba(44, 52, 61, 0.04);
        --shadow:    0 12px 35px rgba(44, 52, 61, 0.07);

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    /* =========================================================
       HERO — CLIENT PPE
    ========================================================= */

    .ppe-ops-hero {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        flex-wrap: wrap;

        padding: 20px 24px;

        border-radius: 20px;

        background: linear-gradient(135deg, #2C343D 0%, #3A4650 65%, #303A43 100%);

        position: relative;

        overflow: hidden;

        box-shadow: var(--shadow);
    }


    .ppe-ops-hero::before {

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


    .ppe-ops-hero-left {

        display: flex;

        align-items: center;

        gap: 15px;

        position: relative;

        z-index: 2;

        min-width: 0;
    }


    .ppe-ops-hero-icon {

        width: 52px;

        height: 52px;

        flex-shrink: 0;

        border-radius: 14px;

        background: rgba(240, 229, 53, 0.16);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: #1D4ED8;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.15rem;
    }


    .ppe-ops-hero-text {

        display: flex;

        flex-direction: column;

        gap: 4px;

        min-width: 0;
    }


    .ppe-ops-hero-text strong {

        color: #FFFFFF;

        font-size: 1.05rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .ppe-ops-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    .ppe-ops-hero-tag {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 11px;

        border-radius: 999px;

        background: rgba(240, 229, 53, 0.16);

        border: 1px solid rgba(240, 229, 53, 0.30);

        color: var(--yellow);

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.5px;

        align-self: flex-start;

        margin-top: 4px;
    }


    .ppe-ops-hero-tag i {

        font-size: 0.6rem;

    }


    /* =========================================================
       RETOUR
    ========================================================= */

    .ppe-ops-retour {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        height: 36px;

        padding: 0 14px;

        border-radius: 10px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        color: var(--dark);

        font-family: inherit;

        font-size: 0.66rem;

        font-weight: 800;

        text-decoration: none;

        white-space: nowrap;

        box-shadow: var(--shadow-sm);

        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;

        align-self: flex-start;

        width: max-content;
    }


    .ppe-ops-retour i {

        color: #1D4ED8;
        font-size: 0.66rem;
    }


    .ppe-ops-retour:hover {

        background: var(--yellow-light);

        border-color: rgba(240, 229, 53, 0.45);

        transform: translateY(-1px);
    }


    /* =========================================================
       BARRE DE FILTRES
    ========================================================= */

    .ppe-ops-filtres {

        display: flex;

        flex-wrap: wrap;

        gap: 10px;

        align-items: flex-end;

        padding: 16px 20px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        box-shadow: var(--shadow-sm);
    }


    .ppe-ops-filtre {

        display: flex;

        flex-direction: column;

        gap: 6px;

        min-width: 0;
    }


    .ppe-ops-filtre-label {

        font-size: 0.56rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted-light);

        display: flex;

        align-items: center;

        gap: 6px;
    }


    .ppe-ops-filtre-label i {

        font-size: 0.62rem;

        color: #1D4ED8;
    }


    .ppe-ops-filtre-input {

        height: 42px;

        padding: 0 13px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #F8FAFC;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.75rem;

        font-weight: 600;

        outline: none;

        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }


    .ppe-ops-filtre-input:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .ppe-ops-filtre-input:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow: 0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    /* =========================================================
       BOUTONS
    ========================================================= */

    .ppe-ops-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        height: 42px;

        padding: 0 16px;

        border-radius: 11px;

        font-family: inherit;

        font-size: 0.68rem;

        font-weight: 800;

        text-decoration: none;

        border: none;

        cursor: pointer;

        white-space: nowrap;

        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }


    .ppe-ops-btn i {

        font-size: 0.7rem;

        color: #1D4ED8;
    }


    .ppe-ops-btn.principal {

        background: var(--dark);

        color: #FFFFFF;

        box-shadow: 0 6px 16px rgba(44, 52, 61, 0.18);
    }


    .ppe-ops-btn.principal i {

        color: #93C5FD;
    }


    .ppe-ops-btn.principal:hover {

        background: var(--dark-soft);

        transform: translateY(-1px);

        box-shadow: 0 10px 22px rgba(44, 52, 61, 0.24);
    }


    .ppe-ops-btn.secondaire {

        background: #FFFFFF;

        color: var(--dark);

        border: 1px solid var(--border);
    }


    .ppe-ops-btn.secondaire:hover {

        background: var(--yellow-light);

        border-color: rgba(240, 229, 53, 0.45);

        transform: translateY(-1px);
    }


    /* =========================================================
       STATISTIQUES RÉSUMÉ
    ========================================================= */

    .ppe-ops-stats {

        display: grid;

        grid-template-columns: repeat(3, minmax(0, 1fr));

        gap: 12px;
    }


    .ppe-ops-stat {

        display: flex;

        align-items: center;

        gap: 14px;

        padding: 16px 18px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 16px;

        box-shadow: var(--shadow-sm);

        position: relative;

        overflow: hidden;

        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }


    .ppe-ops-stat:hover {

        border-color: rgba(240, 229, 53, 0.45);

        box-shadow: 0 10px 28px rgba(240, 229, 53, 0.10);

        transform: translateY(-1px);
    }


    .ppe-ops-stat::after {

        content: "";

        position: absolute;

        left: 0;

        top: 0;

        bottom: 0;

        width: 4px;

        background: linear-gradient(180deg, #F0E535 0%, #E6D91C 100%);
    }


    .ppe-ops-stat-icon {

        width: 42px;

        height: 42px;

        flex-shrink: 0;

        border-radius: 12px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.35);

        color: #1D4ED8;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.9rem;
    }


    .ppe-ops-stat-info {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .ppe-ops-stat-info strong {

        color: var(--dark);

        font-size: 1.15rem;

        font-weight: 900;

        letter-spacing: -0.4px;

        line-height: 1.1;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    .ppe-ops-stat-info span {

        color: var(--muted);

        font-size: 0.6rem;

        font-weight: 700;

        letter-spacing: 0.2px;

        text-transform: uppercase;
    }


    /* =========================================================
       CARTE TABLEAU
    ========================================================= */

    .ppe-ops-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        width: 100%;
    }


    .ppe-ops-card-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        padding: 16px 22px;

        border-bottom: 1px solid var(--border);

        background: linear-gradient(135deg, #FFFDF7 0%, #FFFCE8 100%);

        position: relative;

        overflow: hidden;
    }


    .ppe-ops-card-header::before {

        content: "";

        position: absolute;

        left: 0;

        top: 0;

        bottom: 0;

        width: 4px;

        background: linear-gradient(180deg, #F0E535 0%, #E6D91C 100%);
    }


    .ppe-ops-card-header-left {

        display: flex;

        align-items: center;

        gap: 12px;

        min-width: 0;
    }


    .ppe-ops-card-header-icon {

        width: 40px;

        height: 40px;

        flex-shrink: 0;

        border-radius: 11px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.35);

        color: #1D4ED8;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.95rem;
    }


    .ppe-ops-card-header-text {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .ppe-ops-card-header-text strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .ppe-ops-card-header-text span {

        color: var(--muted);

        font-size: 0.66rem;

        font-weight: 500;
    }


    .ppe-ops-card-compteur {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 6px 12px;

        border-radius: 999px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.35);

        color: #1D4ED8;

        font-size: 0.62rem;

        font-weight: 800;

        white-space: nowrap;
    }


    .ppe-ops-card-compteur i {

        font-size: 0.62rem;

        color: #1D4ED8;
    }


    /* =========================================================
       TABLEAU
    ========================================================= */

    .ppe-ops-table {

        width: 100%;

        border-collapse: collapse;

        font-size: 0.78rem;
    }


    .ppe-ops-table thead {

        background: #FFFFFF;

        border-bottom: 1px solid var(--border);
    }


    .ppe-ops-table th {

        padding: 13px 16px;

        text-align: left;

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted);

        white-space: nowrap;
    }


    .ppe-ops-table td {

        padding: 14px 16px;

        border-bottom: 1px solid var(--border);

        color: var(--dark);

        vertical-align: middle;
    }


    .ppe-ops-table tbody tr {

        transition: background 0.2s ease;
    }


    .ppe-ops-table tbody tr:hover {

        background: var(--yellow-light);
    }


    .ppe-ops-table tbody tr:last-child td {

        border-bottom: none;
    }


    /* =========================================================
       CELLULE DATE
    ========================================================= */

    .ppe-ops-date {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        font-size: 0.72rem;

        font-weight: 700;

        color: var(--dark);

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    .ppe-ops-date i {

        color: #1D4ED8;
        font-size: 0.68rem;
    }


    /* =========================================================
       BADGE TYPE OPÉRATION
    ========================================================= */

    .ppe-ops-badge {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 11px;

        border-radius: 999px;

        font-size: 0.6rem;

        font-weight: 800;

        white-space: nowrap;

        border: 1px solid transparent;

        background: var(--accent-soft);

        border-color: rgba(37, 99, 235, 0.22);

        color: #1D4ED8;
    }


    .ppe-ops-badge i {

        font-size: 0.6rem;

        color: #1D4ED8;
    }


    /* =========================================================
       MONTANT
    ========================================================= */

    .ppe-ops-montant {

        display: inline-flex;

        align-items: baseline;

        gap: 4px;

        font-family: 'JetBrains Mono', ui-monospace, monospace;

        font-size: 0.78rem;

        font-weight: 800;

        color: var(--dark);
    }


    .ppe-ops-montant-devise {

        font-size: 0.6rem;

        font-weight: 700;

        color: var(--muted-light);

        text-transform: uppercase;
    }


    /* =========================================================
       MODE
    ========================================================= */

    .ppe-ops-mode {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 4px 10px;

        border-radius: 8px;

        background: var(--background);

        border: 1px solid var(--border);

        color: var(--muted);

        font-size: 0.62rem;

        font-weight: 700;

        text-transform: capitalize;
    }


    .ppe-ops-mode i {

        font-size: 0.6rem;

        color: #1D4ED8;
    }


    /* =========================================================
       AGENCE
    ========================================================= */

    .ppe-ops-agence {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        color: var(--muted);

        font-size: 0.72rem;

        font-weight: 600;
    }


    .ppe-ops-agence i {

        font-size: 0.66rem;

        color: #1D4ED8;
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .ppe-ops-empty {

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 10px;

        padding: 55px 25px;

        text-align: center;

        color: var(--muted);
    }


    .ppe-ops-empty-icon {

        width: 58px;

        height: 58px;

        border-radius: 50%;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.35);

        color: #1D4ED8;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.4rem;
    }


    .ppe-ops-empty strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;
    }


    .ppe-ops-empty span {

        font-size: 0.68rem;

        color: var(--muted-light);

        max-width: 340px;

        line-height: 1.5;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 900px) {

        .ppe-ops-stats {

            grid-template-columns: 1fr;
        }
    }


    @media (max-width: 700px) {

        .ppe-ops-hero {

            padding: 16px 18px;

            border-radius: 16px;
        }


        .ppe-ops-hero-text strong {

            font-size: 0.9rem;
        }


        .ppe-ops-filtres {

            padding: 14px 16px;

            border-radius: 16px;
        }


        .ppe-ops-filtre {

            flex: 1;

            min-width: 130px;
        }


        .ppe-ops-btn {

            flex: 1;

            min-width: 130px;
        }


        .ppe-ops-table th,
        .ppe-ops-table td {

            padding: 10px 12px;
        }
    }

</style>


<div class="ppe-ops-page">


    {{-- =====================================================
         RETOUR
    ====================================================== --}}

    <a class="ppe-ops-retour" href="{{ route('responsable.ppe.index') }}">

        <i class="fa-solid fa-arrow-left"></i>

        Retour à la liste

    </a>



    {{-- =====================================================
         HERO CLIENT PPE
    ====================================================== --}}

    <div class="ppe-ops-hero">

        <div class="ppe-ops-hero-left">

            <div class="ppe-ops-hero-icon">

                <i class="fa-solid fa-landmark"></i>

            </div>


            <div class="ppe-ops-hero-text">

                <strong>
                    {{ $client->nomAffichage() }}
                </strong>

                <span>
                    Opérations enregistrées sur la période sélectionnée.
                </span>

                <span class="ppe-ops-hero-tag">

                    <i class="fa-solid fa-shield-halved"></i>

                    {{ $client->statut_ppe->libelle() }}

                </span>

            </div>

        </div>

    </div>



    {{-- =====================================================
         FILTRES
    ====================================================== --}}

    <form method="GET" class="ppe-ops-filtres">


        <div class="ppe-ops-filtre">

            <label class="ppe-ops-filtre-label" for="du">

                <i class="fa-solid fa-calendar"></i>

                Du

            </label>

            <input
                type="date"
                name="du"
                id="du"
                value="{{ $du }}"
                class="ppe-ops-filtre-input"
            >

        </div>


        <div class="ppe-ops-filtre">

            <label class="ppe-ops-filtre-label" for="au">

                <i class="fa-solid fa-calendar"></i>

                au

            </label>

            <input
                type="date"
                name="au"
                id="au"
                value="{{ $au }}"
                class="ppe-ops-filtre-input"
            >

        </div>


        <button type="submit" class="ppe-ops-btn principal">

            <i class="fa-solid fa-filter"></i>

            Filtrer par jours

        </button>


        <a
            class="ppe-ops-btn secondaire"
            href="{{ route('responsable.ppe.detail', $client) }}"
        >

            <i class="fa-solid fa-rotate-left"></i>

            Réinitialiser

        </a>


    </form>



    {{-- =====================================================
         STATISTIQUES RÉSUMÉ
    ====================================================== --}}

    <div class="ppe-ops-stats">


        <div class="ppe-ops-stat">

            <div class="ppe-ops-stat-icon">

                <i class="fa-solid fa-list-check"></i>

            </div>


            <div class="ppe-ops-stat-info">

                <strong>
                    {{ $operations->count() }}
                </strong>

                <span>
                    Opération(s)
                </span>

            </div>

        </div>


        <div class="ppe-ops-stat">

            <div class="ppe-ops-stat-icon">

                <i class="fa-solid fa-coins"></i>

            </div>


            <div class="ppe-ops-stat-info">

                <strong>
                    {{ number_format($operations->sum('montant'), 0, ',', ' ') }}
                </strong>

                <span>
                    Total en XOF
                </span>

            </div>

        </div>


        <div class="ppe-ops-stat">

            <div class="ppe-ops-stat-icon">

                <i class="fa-solid fa-paperclip"></i>

            </div>


            <div class="ppe-ops-stat-info">

                <strong>
                    {{ $client->documentsPpe->count() }}
                </strong>

                <span>
                    Pièce(s) justificative(s)
                </span>

            </div>

        </div>


    </div>



    {{-- =====================================================
         TABLEAU DES OPÉRATIONS
    ====================================================== --}}

    <div class="ppe-ops-card">


        <div class="ppe-ops-card-header">

            <div class="ppe-ops-card-header-left">

                <div class="ppe-ops-card-header-icon">

                    <i class="fa-solid fa-receipt"></i>

                </div>


                <div class="ppe-ops-card-header-text">

                    <strong>
                        Détail des opérations
                    </strong>

                    <span>
                        Historique filtré par période.
                    </span>

                </div>

            </div>


            <span class="ppe-ops-card-compteur">

                <i class="fa-solid fa-layer-group"></i>

                {{ $operations->count() }} opération(s)

            </span>

        </div>



        @if ($operations->count())


            <table class="ppe-ops-table">

                <thead>

                    <tr>

                        <th>Date</th>

                        <th>Type</th>

                        <th>Montant</th>

                        <th>Mode</th>

                        <th>Agence</th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($operations as $o)

                        <tr>

                            <td data-label="Date">

                                <span class="ppe-ops-date">

                                    <i class="fa-solid fa-clock"></i>

                                    {{ $o->effectuee_le->format('d/m/Y H:i') }}

                                </span>

                            </td>


                            <td data-label="Type">

                                <span class="ppe-ops-badge">

                                    <i class="fa-solid fa-arrow-right-arrow-left"></i>

                                    {{ $o->type->libelle() }}

                                </span>

                            </td>


                            <td data-label="Montant">

                                <span class="ppe-ops-montant">

                                    {{ number_format((float) $o->montant, 0, ',', ' ') }}

                                    <span class="ppe-ops-montant-devise">

                                        {{ $o->devise_code }}

                                    </span>

                                </span>

                            </td>


                            <td data-label="Mode">

                                <span class="ppe-ops-mode">

                                    <i class="fa-solid fa-credit-card"></i>

                                    {{ $o->mode_paiement->value }}

                                </span>

                            </td>


                            <td data-label="Agence">

                                <span class="ppe-ops-agence">

                                    <i class="fa-solid fa-building"></i>

                                    {{ $o->agence?->nom ?? '—' }}

                                </span>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>


        @else


            <div class="ppe-ops-empty">

                <div class="ppe-ops-empty-icon">

                    <i class="fa-solid fa-inbox"></i>

                </div>


                <strong>
                    Aucune opération sur cette période
                </strong>


                <span>
                    Ajustez les dates de filtrage pour visualiser
                    les opérations de cette PPE.
                </span>

            </div>

        @endif


    </div>


</div>

@endsection
