@extends('layouts.admin')



@section('sous-titre', 'Traçabilité de toutes les actions effectuées sur la plateforme '.$identite['nom_systeme'].'.')


@section('contenu')

<style>

    /* =========================================================
       PAGE JOURNAL D'AUDIT
    ========================================================= */

    .journal-page {

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
       HERO + BOUTON VÉRIFIER
    ========================================================= */

    .journal-hero {

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


    .journal-hero::before {

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


    .journal-hero-left {

        display: flex;

        align-items: center;

        gap: 15px;

        position: relative;

        z-index: 2;

        min-width: 0;
    }


    .journal-hero-icon {

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


    .journal-hero-text {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .journal-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .journal-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* =========================================================
       BOUTON VÉRIFIER LA CHAÎNE
    ========================================================= */

    .btn-verifier {

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


    .btn-verifier i {

        font-size: 0.78rem;

        transition: transform 0.2s ease;
    }


    .btn-verifier:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow:
            0 12px 26px rgba(240, 229, 53, 0.36);
    }


    .btn-verifier:hover i {

        transform: translateY(-2px);
    }


    /* =========================================================
       RÉSULTAT DE VÉRIFICATION
    ========================================================= */

    .verification-result {

        display: flex;

        align-items: flex-start;

        gap: 12px;

        padding: 14px 16px;

        border-radius: 14px;

        font-size: 0.78rem;

        font-weight: 600;

        line-height: 1.5;

        box-shadow: var(--shadow-sm);

        animation: fadeIn 0.3s ease-out;
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


    .verification-result.ok {

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: #238E15;
    }


    .verification-result.rupture {

        background: #FEF2F2;

        border: 1px solid #FECACA;

        color: var(--danger);
    }


    .verification-result-icon {

        width: 34px;

        height: 34px;

        flex-shrink: 0;

        border-radius: 10px;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.9rem;
    }


    .verification-result.ok .verification-result-icon {

        background: rgba(48, 195, 26, 0.14);

        color: var(--green);
    }


    .verification-result.rupture .verification-result-icon {

        background: rgba(220, 38, 38, 0.10);

        color: var(--danger);
    }


    .verification-result-body {

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .verification-result-body strong {

        font-size: 0.8rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .verification-result-body span {

        font-size: 0.72rem;

        font-weight: 500;

        opacity: 0.85;
    }


    /* =========================================================
       CARTE TABLEAU
    ========================================================= */

    .journal-card {

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

    .journal-table {

        width: 100%;

        border-collapse: collapse;

        font-size: 0.78rem;
    }


    .journal-table thead {

        background: var(--background);

        border-bottom: 1px solid var(--border);
    }


    .journal-table th {

        padding: 14px 16px;

        text-align: left;

        font-size: 0.6rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted);

        white-space: nowrap;
    }


    .journal-table td {

        padding: 13px 16px;

        border-bottom: 1px solid var(--border);

        color: var(--dark);

        vertical-align: middle;
    }


    .journal-table tbody tr {

        transition: background 0.2s ease;
    }


    .journal-table tbody tr:hover {

        background: var(--yellow-light);
    }


    .journal-table tbody tr:last-child td {

        border-bottom: none;
    }


    /* =========================================================
       CELLULE ID
    ========================================================= */

    .journal-id {

        font-family: 'JetBrains Mono', ui-monospace, monospace;

        font-size: 0.72rem;

        font-weight: 800;

        color: var(--dark);

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        padding: 3px 9px;

        border-radius: 8px;

        display: inline-block;
    }


    /* =========================================================
       ACTEUR
    ========================================================= */

    .journal-acteur {

        display: flex;

        align-items: center;

        gap: 8px;

    }


    .journal-acteur-icon {

        width: 28px;

        height: 28px;

        flex-shrink: 0;

        border-radius: 8px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.25);

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.65rem;
    }


    .journal-acteur-text {

        display: flex;

        flex-direction: column;

        gap: 1px;

        min-width: 0;
    }


    .journal-acteur-text strong {

        font-size: 0.72rem;

        font-weight: 800;

        color: var(--dark);

        letter-spacing: -0.2px;
    }


    .journal-acteur-text span {

        font-size: 0.6rem;

        color: var(--muted-light);

        font-weight: 600;
    }


    /* =========================================================
       ACTION (monospace)
    ========================================================= */

    .journal-action {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 10px;

        border-radius: 8px;

        background: var(--dark);

        color: #FFFFFF;

        font-family: 'JetBrains Mono', ui-monospace, monospace;

        font-size: 0.64rem;

        font-weight: 700;

        letter-spacing: -0.2px;

        white-space: nowrap;

        max-width: 260px;

        overflow: hidden;

        text-overflow: ellipsis;
    }


    .journal-action i {

        color: var(--yellow);

        font-size: 0.6rem;

        flex-shrink: 0;
    }


    /* =========================================================
       CIBLE
    ========================================================= */

    .journal-cible {

        display: flex;

        flex-direction: column;

        gap: 2px;

        min-width: 0;
    }


    .journal-cible strong {

        font-size: 0.7rem;

        font-weight: 800;

        color: var(--dark);

    }


    .journal-cible span {

        font-size: 0.58rem;

        color: var(--muted-light);

        font-weight: 600;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    /* =========================================================
       DATE
    ========================================================= */

    .journal-date {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        color: var(--muted);

        font-size: 0.7rem;

        font-weight: 600;

        white-space: nowrap;
    }


    .journal-date i {

        color: var(--muted-light);

        font-size: 0.65rem;
    }


    /* =========================================================
       HASH
    ========================================================= */

    .journal-hash {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        font-family: 'JetBrains Mono', ui-monospace, monospace;

        font-size: 0.62rem;

        font-weight: 700;

        color: var(--muted);

        background: var(--background);

        border: 1px solid var(--border);

        padding: 4px 9px;

        border-radius: 8px;

        cursor: help;
    }


    .journal-hash i {

        color: var(--muted-light);

        font-size: 0.58rem;
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .journal-empty {

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 12px;

        padding: 55px 25px;

        text-align: center;

        color: var(--muted);
    }


    .journal-empty-icon {

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


    .journal-empty strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;
    }


    .journal-empty span {

        font-size: 0.68rem;

        color: var(--muted-light);

        max-width: 320px;

        line-height: 1.5;
    }


    /* =========================================================
       PAGINATION
    ========================================================= */

    .journal-pagination {

        display: flex;

        justify-content: center;

        padding: 6px 0;
    }


    .journal-pagination svg {

        width: 14px;

        height: 14px;
    }


    .journal-pagination a,
    .journal-pagination span[aria-current="page"] > span,
    .journal-pagination span[aria-disabled="true"] > span {

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


    .journal-pagination a:hover {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.45);

        color: var(--dark);
    }


    .journal-pagination span[aria-current="page"] > span {

        background: var(--dark);

        border-color: var(--dark);

        color: #FFFFFF;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 900px) {

        .journal-table th,
        .journal-table td {

            padding: 11px 12px;
        }
    }


    @media (max-width: 700px) {

        .journal-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .journal-hero-text strong {

            font-size: 0.9rem;
        }


        .btn-verifier {

            width: 100%;

            justify-content: center;
        }


        /* Tableau en cartes empilées */

        .journal-table,
        .journal-table thead,
        .journal-table tbody,
        .journal-table tr,
        .journal-table td,
        .journal-table th {

            display: block;

            width: 100%;
        }


        .journal-table thead {

            display: none;
        }


        .journal-table tbody tr {

            padding: 14px;

            border-bottom: 1px solid var(--border);

            background: #FFFFFF;
        }


        .journal-table tbody tr:hover {

            background: var(--yellow-light);
        }


        .journal-table td {

            padding: 8px 0;

            border: none;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 12px;
        }


        .journal-table td::before {

            content: attr(data-label);

            font-size: 0.58rem;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            color: var(--muted);

            flex-shrink: 0;
        }


        .journal-acteur {

            justify-content: flex-end;

            text-align: right;
        }


        .journal-cible {

            align-items: flex-end;

            text-align: right;
        }


        .journal-action {

            max-width: 100%;
        }
    }

</style>


<div class="journal-page">


    {{-- =====================================================
         HERO + BOUTON VÉRIFIER
    ====================================================== --}}

    <div class="journal-hero">

        <div class="journal-hero-left">

            <div class="journal-hero-icon">

                <i class="fa-solid fa-clipboard-list"></i>

            </div>


            <div class="journal-hero-text">

                <strong>
                    Journal d'audit
                </strong>

                <span>
                    Traçabilité de toutes les actions effectuées sur la plateforme.
                </span>

            </div>

        </div>


        <form
            method="POST"
            action="{{ route('admin.journal-audit.verifier-chaine') }}"
            style="position: relative; z-index: 2;"
        >

            @csrf


            <button
                type="submit"
                class="btn-verifier"
            >

                <i class="fa-solid fa-shield-halved"></i>

                Vérifier la chaîne

            </button>

        </form>

    </div>



    {{-- =====================================================
         RÉSULTAT DE VÉRIFICATION
    ====================================================== --}}

    @if ($ruptureId !== null)

        <div class="verification-result {{ $ruptureId === 'aucune' ? 'ok' : 'rupture' }}">

            <div class="verification-result-icon">

                <i class="fa-solid {{ $ruptureId === 'aucune' ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>

            </div>


            <div class="verification-result-body">

                @if ($ruptureId === 'aucune')

                    <strong>
                        Chaîne intègre
                    </strong>

                    <span>
                        Aucune rupture détectée dans la chaîne d'audit.
                    </span>

                @else

                    <strong>
                        Rupture détectée
                    </strong>

                    <span>
                        La chaîne d'audit présente une anomalie à la ligne <strong>#{{ $ruptureId }}</strong>.

                    </span>

                @endif

            </div>

        </div>

    @endif



    {{-- =====================================================
         TABLEAU
    ====================================================== --}}

    <div class="journal-card">


        @if ($lignes->count())


            <table class="journal-table">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Acteur</th>

                        <th>Action</th>

                        <th>Cible</th>

                        <th>Date</th>

                        <th>Hash</th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($lignes as $ligne)

                        <tr>


                            {{-- ID --}}

                            <td data-label="#">

                                <span class="journal-id">
                                    #{{ $ligne->id }}
                                </span>

                            </td>


                            {{-- ACTEUR --}}

                            <td data-label="Acteur">

                                <div class="journal-acteur">

                                    <div class="journal-acteur-icon">

                                        <i class="fa-solid fa-user-shield"></i>

                                    </div>


                                    <div class="journal-acteur-text">

                                        <strong>
                                            {{ $ligne->acteur_type }}
                                        </strong>

                                        @if ($ligne->acteur_id)

                                            <span>
                                                #{{ $ligne->acteur_id }}
                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- ACTION --}}

                            <td data-label="Action">

                                <span class="journal-action">

                                    <i class="fa-solid fa-bolt"></i>

                                    {{ $ligne->action }}

                                </span>

                            </td>


                            {{-- CIBLE --}}

                            <td data-label="Cible">

                                <div class="journal-cible">

                                    <strong>
                                        {{ $ligne->cible_type }}
                                    </strong>

                                    @if ($ligne->cible_id)

                                        <span>
                                            #{{ $ligne->cible_id }}
                                        </span>

                                    @endif

                                </div>

                            </td>


                            {{-- DATE --}}

                            <td data-label="Date">

                                <span class="journal-date">

                                    <i class="fa-regular fa-clock"></i>

                                    {{ $ligne->cree_le->format('d/m/Y H:i:s') }}

                                </span>

                            </td>


                            {{-- HASH --}}

                            <td data-label="Hash">

                                <span
                                    class="journal-hash"
                                    title="{{ $ligne->hash_courant }}"
                                >

                                    <i class="fa-solid fa-fingerprint"></i>

                                    {{ substr($ligne->hash_courant, 0, 12) }}…

                                </span>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>


        @else


            {{-- ÉTAT VIDE --}}

            <div class="journal-empty">

                <div class="journal-empty-icon">

                    <i class="fa-solid fa-clipboard"></i>

                </div>


                <strong>
                    Aucune entrée d'audit
                </strong>


                <span>
                    Le journal est vide pour le moment.
                    Les actions effectuées apparaîtront ici automatiquement.
                </span>

            </div>

        @endif


    </div>



    {{-- =====================================================
         PAGINATION
    ====================================================== --}}

    @if ($lignes->hasPages())

        <div class="journal-pagination">

            {{ $lignes->links() }}

        </div>

    @endif


</div>

@endsection
