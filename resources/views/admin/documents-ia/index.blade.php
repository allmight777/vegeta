@extends('layouts.admin')



@section('sous-titre', 'Gérez les documents de référence utilisés par l\'assistant IA.')


@section('contenu')

<style>

    /* =========================================================
       PAGE DOCUMENTS IA
    ========================================================= */

    .documents-page {

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

    .documents-hero {

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


    .documents-hero::before {

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


    .documents-hero-left {

        display: flex;

        align-items: center;

        gap: 15px;

        position: relative;

        z-index: 2;

        min-width: 0;
    }


    .documents-hero-icon {

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


    .documents-hero-text {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .documents-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .documents-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* =========================================================
       BOUTON TÉLÉVERSER
    ========================================================= */

    .btn-upload {

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


    .btn-upload i {

        font-size: 0.78rem;

        transition: transform 0.2s ease;
    }


    .btn-upload:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow:
            0 12px 26px rgba(240, 229, 53, 0.36);
    }


    .btn-upload:hover i {

        transform: translateY(-2px);
    }


    /* =========================================================
       CARTE TABLEAU
    ========================================================= */

    .documents-card {

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

    .documents-table {

        width: 100%;

        border-collapse: collapse;

        font-size: 0.78rem;
    }


    .documents-table thead {

        background: var(--background);

        border-bottom: 1px solid var(--border);
    }


    .documents-table th {

        padding: 14px 16px;

        text-align: left;

        font-size: 0.6rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted);

        white-space: nowrap;
    }


    .documents-table td {

        padding: 14px 16px;

        border-bottom: 1px solid var(--border);

        color: var(--dark);

        vertical-align: middle;
    }


    .documents-table tbody tr {

        transition: background 0.2s ease;
    }


    .documents-table tbody tr:hover {

        background: var(--yellow-light);
    }


    .documents-table tbody tr:last-child td {

        border-bottom: none;
    }


    /* =========================================================
       CELLULE TITRE
    ========================================================= */

    .doc-title {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;

        max-width: 320px;
    }


    .doc-title strong {

        color: var(--dark);

        font-size: 0.78rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .doc-title span {

        color: var(--muted-light);

        font-size: 0.62rem;

        font-weight: 500;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    /* =========================================================
       BADGE STATUT EXTRACTION
    ========================================================= */

    .badge-statut {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 5px 10px;

        border-radius: 999px;

        font-size: 0.6rem;

        font-weight: 800;

        white-space: nowrap;
    }


    .badge-statut i {

        font-size: 0.62rem;
    }


    .badge-statut.reussie {

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.18);

        color: #249C13;
    }


    .badge-statut.echouee {

        background: var(--danger-soft);

        border: 1px solid rgba(220, 38, 38, 0.16);

        color: var(--danger);
    }


    .badge-statut.attente {

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: #8A7C00;
    }


    /* =========================================================
       VISIBILITÉ (CHECKBOXES)
    ========================================================= */

    .visibility-form {

        display: flex;

        flex-direction: column;

        gap: 6px;
    }


    .visibility-check {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        font-size: 0.68rem;

        font-weight: 700;

        color: var(--dark);

        cursor: pointer;

        user-select: none;

        transition: color 0.2s ease;
    }


    .visibility-check input[type="checkbox"] {

        width: 16px;

        height: 16px;

        accent-color: var(--yellow);

        cursor: pointer;

        flex-shrink: 0;
    }


    .visibility-check:hover {

        color: var(--dark);
    }


    /* =========================================================
       BOUTON SUPPRIMER
    ========================================================= */

    .btn-delete {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 6px;

        height: 32px;

        padding: 0 12px;

        border: 1px solid rgba(220, 38, 38, 0.20);

        border-radius: 9px;

        background: var(--danger-soft);

        color: var(--danger);

        font-family: inherit;

        font-size: 0.62rem;

        font-weight: 800;

        cursor: pointer;

        transition:
            background 0.2s ease,
            border-color 0.2s ease,
            transform 0.2s ease;
    }


    .btn-delete i {

        font-size: 0.64rem;
    }


    .btn-delete:hover {

        background: rgba(220, 38, 38, 0.16);

        border-color: rgba(220, 38, 38, 0.35);

        transform: translateY(-1px);
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .documents-empty {

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 12px;

        padding: 55px 25px;

        text-align: center;

        color: var(--muted);
    }


    .documents-empty-icon {

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


    .documents-empty strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;
    }


    .documents-empty span {

        font-size: 0.68rem;

        color: var(--muted-light);

        max-width: 320px;

        line-height: 1.5;
    }


    /* =========================================================
       PAGINATION
    ========================================================= */

    .documents-pagination {

        display: flex;

        justify-content: center;

        padding: 6px 0;
    }


    .documents-pagination svg {

        width: 14px;

        height: 14px;
    }


    .documents-pagination a,
    .documents-pagination span[aria-current="page"] > span,
    .documents-pagination span[aria-disabled="true"] > span {

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


    .documents-pagination a:hover {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.45);

        color: var(--dark);
    }


    .documents-pagination span[aria-current="page"] > span {

        background: var(--dark);

        border-color: var(--dark);

        color: #FFFFFF;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 900px) {

        .documents-table th,
        .documents-table td {

            padding: 11px 12px;
        }


        .doc-title {

            max-width: 180px;
        }
    }


    @media (max-width: 700px) {

        .documents-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .documents-hero-text strong {

            font-size: 0.9rem;
        }


        .btn-upload {

            width: 100%;

            justify-content: center;
        }


        /* Tableau en cartes empilées */

        .documents-table,
        .documents-table thead,
        .documents-table tbody,
        .documents-table tr,
        .documents-table td,
        .documents-table th {

            display: block;

            width: 100%;
        }


        .documents-table thead {

            display: none;
        }


        .documents-table tbody tr {

            padding: 14px;

            border-bottom: 1px solid var(--border);

            background: #FFFFFF;
        }


        .documents-table tbody tr:hover {

            background: var(--yellow-light);
        }


        .documents-table td {

            padding: 8px 0;

            border: none;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 12px;
        }


        .documents-table td::before {

            content: attr(data-label);

            font-size: 0.58rem;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            color: var(--muted);

            flex-shrink: 0;
        }


        .doc-title {

            max-width: 100%;

            align-items: flex-end;

            text-align: right;
        }


        .visibility-form {

            align-items: flex-end;
        }


        .documents-table td[data-label="Actions"] {

            justify-content: flex-end;

            padding-top: 12px;
        }
    }

</style>


<div class="documents-page">


    {{-- =====================================================
         HERO + BOUTON TÉLÉVERSER
    ====================================================== --}}

    <div class="documents-hero">

        <div class="documents-hero-left">

            <div class="documents-hero-icon">

                <i class="fa-solid fa-robot"></i>

            </div>


            <div class="documents-hero-text">

                <strong>
                    Documents IA
                </strong>

                <span>
                    Gérez les documents de référence utilisés
                    par l'assistant IA.
                </span>

            </div>

        </div>


        <a
            href="{{ route('admin.documents-ia.creer') }}"
            class="btn-upload"
        >

            <i class="fa-solid fa-cloud-arrow-up"></i>

            Téléverser des documents

        </a>

    </div>



    {{-- =====================================================
         TABLEAU DOCUMENTS
    ====================================================== --}}

    <div class="documents-card">


        @if ($documents->count())


            <table class="documents-table">

                <thead>

                    <tr>

                        <th>Titre</th>

                        <th>Portée</th>

                        <th>Extraction</th>

                        <th>Visibilité</th>

                        <th>Utilisation</th>

                        <th>Date</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($documents as $document)

                        <tr>


                            {{-- TITRE --}}

                            <td data-label="Titre">

                                <div class="doc-title">

                                    <strong>
                                        {{ $document->titre }}
                                    </strong>

                                    <span>
                                        {{ $document->nom_fichier_original }}
                                    </span>

                                </div>

                            </td>


                            {{-- PORTÉE --}}

                            <td data-label="Portée">

                                <span style="font-size: 0.72rem; font-weight: 700;">

                                    @if ($document->agence)

                                        {{ $document->agence->reseau->nom }}
                                        —
                                        {{ $document->agence->nom }}

                                    @elseif ($document->reseau)

                                        {{ $document->reseau->nom }}
                                        <span style="color: var(--muted-light); font-weight: 500;">
                                            (tout le réseau)
                                        </span>

                                    @else

                                        <span style="color: var(--muted); font-weight: 500;">
                                            Toutes agences
                                        </span>

                                    @endif

                                </span>

                            </td>


                            {{-- EXTRACTION --}}

                            <td data-label="Extraction">

                                @php

                                    $statut = $document->statut_extraction->value;

                                @endphp


                                @if ($statut === 'reussie')

                                    <span class="badge-statut reussie">

                                        <i class="fa-solid fa-circle-check"></i>

                                        Réussie

                                    </span>

                                @elseif ($statut === 'echouee')

                                    <span
                                        class="badge-statut echouee"
                                        title="{{ $document->erreur_message }}"
                                    >

                                        <i class="fa-solid fa-circle-xmark"></i>

                                        Échouée

                                    </span>

                                @else

                                    <span class="badge-statut attente">

                                        <i class="fa-solid fa-hourglass-half"></i>

                                        {{ $document->statut_extraction->libelle() }}

                                    </span>

                                @endif

                            </td>


                            {{-- VISIBILITÉ --}}

                            <td data-label="Visibilité">

                                <form
                                    method="POST"
                                    action="{{ route('admin.documents-ia.mettre-a-jour-visibilite', $document) }}"
                                    class="visibility-form"
                                >

                                    @csrf
                                    @method('PUT')


                                    <label class="visibility-check">

                                        <input
                                            type="checkbox"
                                            name="visible_caissier"
                                            value="1"
                                            @checked($document->visible_caissier)
                                            onchange="this.form.requestSubmit()"
                                        >

                                        Caissiers

                                    </label>


                                    <label class="visibility-check">

                                        <input
                                            type="checkbox"
                                            name="visible_responsable_agence"
                                            value="1"
                                            @checked($document->visible_responsable_agence)
                                            onchange="this.form.requestSubmit()"
                                        >

                                        Responsables d'agence

                                    </label>

                                </form>

                            </td>


                            {{-- UTILISATION (16_PROMPT §4.2) --}}

                            <td data-label="Utilisation">

                                <span style="font-size: 0.7rem; font-weight: 700; color: {{ $document->nombre_utilisations > 0 ? '#1B7A0F' : 'var(--muted)' }};">

                                    @if ($document->nombre_utilisations > 0)
                                        Utilisé {{ $document->nombre_utilisations }} fois
                                    @else
                                        Pas encore utilisé
                                    @endif

                                </span>

                            </td>


                            {{-- DATE --}}

                            <td data-label="Date">

                                <span style="color: var(--muted); font-size: 0.7rem; font-weight: 600;">

                                    {{ $document->created_at->format('d/m/Y H:i') }}

                                </span>

                            </td>


                            {{-- ACTIONS --}}

                            <td data-label="Actions">

                                <form
                                    method="POST"
                                    action="{{ route('admin.documents-ia.supprimer', $document) }}"
                                    onsubmit="return confirm('Supprimer ce document ?');"
                                >

                                    @csrf
                                    @method('DELETE')


                                    <button type="submit" class="btn-delete">

                                        <i class="fa-solid fa-trash"></i>

                                        Supprimer

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>


        @else


            {{-- ÉTAT VIDE --}}

            <div class="documents-empty">

                <div class="documents-empty-icon">

                    <i class="fa-solid fa-file-circle-plus"></i>

                </div>


                <strong>
                    Aucun document pour le moment
                </strong>


                <span>
                    Commencez par téléverser des documents de référence
                    pour alimenter l'assistant IA.
                </span>

            </div>

        @endif


    </div>



    {{-- =====================================================
         PAGINATION
    ====================================================== --}}

    @if ($documents->hasPages())

        <div class="documents-pagination">

            {{ $documents->links() }}

        </div>

    @endif


</div>

@endsection
