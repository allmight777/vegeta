@extends('layouts.admin')



@section('sous-titre', 'Importez les listes officielles au format Excel ou CSV.')


@section('contenu')

<style>

    /* =========================================================
       PAGE LISTES DE SANCTIONS
    ========================================================= */

    .listes-page {

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

    .listes-hero {

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


    .listes-hero::before {

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


    .listes-hero-left {

        display: flex;

        align-items: center;

        gap: 15px;

        position: relative;

        z-index: 2;

        min-width: 0;
    }


    .listes-hero-icon {

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


    .listes-hero-text {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .listes-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .listes-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* =========================================================
       BOUTON PRINCIPAL
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
    }


    .btn-nouveau:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow:
            0 12px 26px rgba(240, 229, 53, 0.36);
    }


    /* =========================================================
       FORMULAIRE D'IMPORT
    ========================================================= */

    .listes-import-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        padding: 24px 26px;

        box-shadow: var(--shadow-sm);

        width: 100%;
    }


    .listes-import-header {

        display: flex;

        align-items: center;

        gap: 12px;

        margin-bottom: 18px;

        padding-bottom: 18px;

        border-bottom: 1px solid var(--border);
    }


    .listes-import-header-icon {

        width: 40px;

        height: 40px;

        flex-shrink: 0;

        border-radius: 11px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: #A08F00;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.95rem;
    }


    .listes-import-header-text {

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .listes-import-header-text strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .listes-import-header-text span {

        color: var(--muted);

        font-size: 0.68rem;

        font-weight: 500;
    }


    .listes-import-grid {

        display: grid;

        grid-template-columns:
            repeat(4, minmax(0, 1fr));

        gap: 14px;

        align-items: end;
    }


    .champ-liste {

        display: flex;

        flex-direction: column;

        gap: 7px;

        min-width: 0;
    }


    .champ-liste-label {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        font-size: 0.6rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--dark);
    }


    .champ-liste-label i {

        color: var(--muted);

        font-size: 0.7rem;
    }


    .champ-liste-label .obligatoire {

        color: var(--danger);

        margin-left: 2px;
    }


    .champ-liste-input,
    .champ-liste select {

        width: 100%;

        min-height: 44px;

        padding: 10px 13px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #F8FAFC;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.75rem;

        font-weight: 600;

        outline: none;

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            box-shadow 0.2s ease;
    }


    .champ-liste-input:hover,
    .champ-liste select:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .champ-liste-input:focus,
    .champ-liste select:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow:
            0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    .champ-liste-input[type="file"] {

        padding: 8px;

        cursor: pointer;
    }


    .champ-liste-input[type="file"]::file-selector-button {

        height: 30px;

        padding: 0 12px;

        margin-right: 10px;

        border: none;

        border-radius: 8px;

        background: var(--dark);

        color: #FFFFFF;

        font-family: inherit;

        font-size: 0.62rem;

        font-weight: 800;

        cursor: pointer;
    }


    /* =========================================================
       AIDE IMPORT
    ========================================================= */

    .listes-import-aide {

        margin-top: 16px;

        padding: 12px 14px;

        background: var(--background);

        border: 1px solid var(--border);

        border-radius: 11px;

        font-size: 0.68rem;

        color: var(--muted);

        line-height: 1.6;

        display: flex;

        gap: 10px;

        align-items: flex-start;
    }


    .listes-import-aide i {

        color: var(--muted-light);

        font-size: 0.72rem;

        margin-top: 2px;

        flex-shrink: 0;
    }


    .listes-import-aide strong {

        color: var(--dark);

        font-weight: 800;
    }


    .listes-import-aide code {

        background: #FFFFFF;

        border: 1px solid var(--border);

        padding: 1px 6px;

        border-radius: 5px;

        font-size: 0.65rem;

        font-weight: 700;

        color: var(--dark);
    }


    /* =========================================================
       CARTE TABLEAU
    ========================================================= */

    .listes-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        width: 100%;
    }


    .listes-card-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        padding: 16px 22px;

        border-bottom: 1px solid var(--border);

        background: var(--background);
    }


    .listes-card-title {

        display: flex;

        align-items: center;

        gap: 10px;

        color: var(--dark);

        font-size: 0.82rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .listes-card-title i {

        color: var(--muted);

        font-size: 0.85rem;
    }


    .listes-card-count {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 11px;

        border-radius: 999px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: #8A7C00;

        font-size: 0.62rem;

        font-weight: 800;
    }


    /* =========================================================
       TABLEAU
    ========================================================= */

    .listes-table {

        width: 100%;

        border-collapse: collapse;

        font-size: 0.78rem;
    }


    .listes-table thead {

        background: #FFFFFF;

        border-bottom: 1px solid var(--border);
    }


    .listes-table th {

        padding: 13px 16px;

        text-align: left;

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted);

        white-space: nowrap;
    }


    .listes-table td {

        padding: 12px 16px;

        border-bottom: 1px solid var(--border);

        color: var(--dark);

        vertical-align: middle;
    }


    .listes-table tbody tr {

        transition: background 0.2s ease;
    }


    .listes-table tbody tr:hover {

        background: var(--yellow-light);
    }


    .listes-table tbody tr:last-child td {

        border-bottom: none;
    }


    /* =========================================================
       BADGE SOURCE
    ========================================================= */

    .badge-source {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 10px;

        border-radius: 999px;

        font-size: 0.6rem;

        font-weight: 800;

        white-space: nowrap;

        background: rgba(44, 52, 61, 0.06);

        border: 1px solid rgba(44, 52, 61, 0.10);

        color: var(--dark);
    }


    .badge-source i {

        font-size: 0.62rem;
    }


    .badge-source.onu {

        background: rgba(59, 130, 246, 0.10);

        border-color: rgba(59, 130, 246, 0.22);

        color: #1D4ED8;
    }


    .badge-source.ue {

        background: rgba(168, 85, 247, 0.10);

        border-color: rgba(168, 85, 247, 0.22);

        color: #7E22CE;
    }


    .badge-source.ppe {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.28);

        color: #8A7C00;
    }


    /* =========================================================
       CELLULE NOM MASQUÉ
    ========================================================= */

    .cell-nom-masque {

        font-family: 'JetBrains Mono', ui-monospace, monospace;

        font-size: 0.72rem;

        font-weight: 700;

        color: var(--muted);

        background: var(--background);

        border: 1px solid var(--border);

        padding: 3px 9px;

        border-radius: 8px;

        display: inline-block;
    }


    /* =========================================================
       CELLULE PAYS / NPI
    ========================================================= */

    .cell-detail {

        display: flex;

        flex-direction: column;

        gap: 2px;

    }


    .cell-detail strong {

        color: var(--dark);

        font-size: 0.72rem;

        font-weight: 700;
    }


    .cell-detail span {

        color: var(--muted-light);

        font-size: 0.62rem;

        font-weight: 600;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .listes-empty {

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 12px;

        padding: 55px 25px;

        text-align: center;

        color: var(--muted);
    }


    .listes-empty-icon {

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


    .listes-empty strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;
    }


    .listes-empty span {

        font-size: 0.68rem;

        color: var(--muted-light);

        max-width: 340px;

        line-height: 1.5;
    }


    /* =========================================================
       PAGINATION
    ========================================================= */

    .listes-pagination {

        display: flex;

        justify-content: center;

        padding: 6px 0;
    }


    .listes-pagination svg {

        width: 14px;

        height: 14px;
    }


    .listes-pagination a,
    .listes-pagination span[aria-current="page"] > span,
    .listes-pagination span[aria-disabled="true"] > span {

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
    }


    .listes-pagination a:hover {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.45);

        color: var(--dark);
    }


    .listes-pagination span[aria-current="page"] > span {

        background: var(--dark);

        border-color: var(--dark);

        color: #FFFFFF;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1000px) {

        .listes-import-grid {

            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }
    }


    @media (max-width: 700px) {

        .listes-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .listes-hero-text strong {

            font-size: 0.9rem;
        }


        .listes-import-grid {

            grid-template-columns: 1fr;
        }


        .listes-import-card {

            padding: 18px 16px;
        }


        .listes-table th,
        .listes-table td {

            padding: 10px 12px;
        }
    }

</style>


<div class="listes-page">


    {{-- =====================================================
         HERO
    ====================================================== --}}

    <div class="listes-hero">

        <div class="listes-hero-left">

            <div class="listes-hero-icon">

                <i class="fa-solid fa-list-check"></i>

            </div>


            <div class="listes-hero-text">

                <strong>
                    Listes de sanctions et PPE
                </strong>

                <span>
                    Importez et consultez les listes officielles de filtrage.
                </span>

            </div>

        </div>

    </div>



    {{-- =====================================================
         MESSAGE DE SESSION
    ====================================================== --}}

    @if (session('statut'))

        <div style="padding: 14px 16px; border-radius: 14px; background: var(--green-soft); border: 1px solid rgba(48, 195, 26, 0.20); color: #238E15; font-size: 0.75rem; font-weight: 700; display: flex; gap: 10px; align-items: center;">

            <i class="fa-solid fa-circle-check" style="font-size: 0.9rem;"></i>

            {{ session('statut') }}

        </div>

    @endif



    {{-- =====================================================
         ERREURS
    ====================================================== --}}

    @if ($errors->any())

        <div style="padding: 14px 16px; border-radius: 14px; background: #FEF2F2; border: 1px solid #FECACA; color: var(--danger); font-size: 0.75rem; font-weight: 700; display: flex; gap: 10px; align-items: center;">

            <i class="fa-solid fa-triangle-exclamation" style="font-size: 0.9rem;"></i>

            {{ $errors->first() }}

        </div>

    @endif



    {{-- =====================================================
         FORMULAIRE D'IMPORT
    ====================================================== --}}

    <div class="listes-import-card">

        <div class="listes-import-header">

            <div class="listes-import-header-icon">

                <i class="fa-solid fa-file-excel"></i>

            </div>


            <div class="listes-import-header-text">

                <strong>
                    Importer un fichier
                </strong>

                <span>
                    Formats acceptés : .xlsx, .xls, .csv — 10 Mo maximum.
                </span>

            </div>

        </div>


        <form
            method="POST"
            action="{{ route('admin.listes.importer') }}"
            enctype="multipart/form-data"
        >

            @csrf


            <div class="listes-import-grid">


                {{-- FICHIER --}}

                <div class="champ-liste" style="grid-column: 1 / -1;">

                    <label class="champ-liste-label" for="fichier">

                        <i class="fa-solid fa-paperclip"></i>

                        Fichier

                        <span class="obligatoire">*</span>

                    </label>


                    <input
                        type="file"
                        name="fichier"
                        id="fichier"
                        accept=".xlsx,.xls,.csv"
                        required
                        class="champ-liste-input"
                    >

                </div>


                {{-- SOURCE --}}

                <div class="champ-liste">

                    <label class="champ-liste-label" for="source">

                        <i class="fa-solid fa-globe"></i>

                        Source

                        <span class="obligatoire">*</span>

                    </label>


                    <select name="source" id="source" required class="champ-liste-input">

                        @foreach ($sources as $source)

                            <option value="{{ $source->value }}">
                                {{ $source->libelle() }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- VERSION --}}

                <div class="champ-liste">

                    <label class="champ-liste-label" for="version">

                        <i class="fa-solid fa-code-branch"></i>

                        Version

                        <span class="obligatoire">*</span>

                    </label>


                    <input
                        type="text"
                        name="version"
                        id="version"
                        required
                        maxlength="32"
                        placeholder="Ex : 2026-09"
                        value="{{ old('version', now()->format('Y-m')) }}"
                        class="champ-liste-input"
                    >

                </div>


                {{-- CATÉGORIE --}}

                <div class="champ-liste">

                    <label class="champ-liste-label" for="categorie">

                        <i class="fa-solid fa-tag"></i>

                        Catégorie

                    </label>


                    <input
                        type="text"
                        name="categorie"
                        id="categorie"
                        maxlength="64"
                        placeholder="Ex : PPE, Sanctions"
                        class="champ-liste-input"
                    >

                </div>


                {{-- BOUTON --}}

                <div class="champ-liste">

                    <button type="submit" class="btn-nouveau" style="width: 100%; justify-content: center;">

                        <i class="fa-solid fa-cloud-arrow-up"></i>

                        Importer

                    </button>

                </div>


            </div>


            {{-- AIDE --}}

            <div class="listes-import-aide">

                <i class="fa-solid fa-circle-info"></i>

                <div>

                    Colonnes attendues dans le fichier (première ligne = en-tête) :
                    <strong><code>nom</code>, <code>prenom</code>, <code>npi</code>, <code>pays</code>, <code>telephone</code>, <code>categorie</code></strong>.
                    Les lignes sans nom et sans prénom sont ignorées. Le nom complet
                    est ensuite chiffré avant stockage, jamais conservé en clair.

                </div>

            </div>


        </form>

    </div>



    {{-- =====================================================
         TABLEAU DES ENTRÉES
    ====================================================== --}}

    <div class="listes-card">

        <div class="listes-card-header">

            <div class="listes-card-title">

                <i class="fa-solid fa-database"></i>

                Entrées importées

            </div>


            <span class="listes-card-count">

                <i class="fa-solid fa-layer-group"></i>

                {{ $entrees->total() }} entrée(s)

            </span>

        </div>


        @if ($entrees->count())


            <table class="listes-table">

                <thead>

                    <tr>

                        <th>Source</th>

                        <th>Nom (masqué)</th>

                        <th>Catégorie</th>

                        <th>Pays</th>

                        <th>NPI</th>

                        <th>Version</th>

                        <th>Importée le</th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($entrees as $entree)

                        @php

                            $sourceValue = $entree->source->value;
                            $sourceClass = str_contains($sourceValue, 'onu')
                                ? 'onu'
                                : (str_contains($sourceValue, 'ue') ? 'ue' : 'ppe');
                            $sourceIcon = match (true) {
                                str_contains($sourceValue, 'onu') => 'fa-globe',
                                str_contains($sourceValue, 'ue') => 'fa-flag',
                                default => 'fa-user-tie',
                            };

                        @endphp


                        <tr>

                            <td data-label="Source">

                                <span class="badge-source {{ $sourceClass }}">

                                    <i class="fa-solid {{ $sourceIcon }}"></i>

                                    {{ $entree->source->libelle() }}

                                </span>

                            </td>


                            <td data-label="Nom">

                                <span class="cell-nom-masque">

                                    {{ $entree->nomMasque() }}

                                </span>

                            </td>


                            <td data-label="Catégorie" style="color: var(--muted); font-size: 0.72rem; font-weight: 600;">

                                {{ $entree->categorie }}

                            </td>


                            <td data-label="Pays">

                                <span style="font-size: 0.72rem; font-weight: 600;">

                                    {{ $entree->pays ?? '—' }}

                                </span>

                            </td>


                            <td data-label="NPI">

                                <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.68rem; font-weight: 700; color: var(--muted);">

                                    {{ $entree->npi ?? '—' }}

                                </span>

                            </td>


                            <td data-label="Version" style="color: var(--muted); font-size: 0.68rem; font-weight: 700;">

                                {{ $entree->version_liste }}

                            </td>


                            <td data-label="Date" style="color: var(--muted-light); font-size: 0.68rem; font-weight: 600;">

                                {{ $entree->importee_le?->format('d/m/Y H:i') }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>


        @else


            <div class="listes-empty">

                <div class="listes-empty-icon">

                    <i class="fa-solid fa-inbox"></i>

                </div>


                <strong>
                    Aucune liste importée
                </strong>


                <span>
                    Commencez par importer un fichier Excel ou CSV
                    contenant les entrées de sanctions ou PPE
                    via le formulaire ci-dessus.
                </span>

            </div>

        @endif


    </div>



    {{-- =====================================================
         PAGINATION
    ====================================================== --}}

    @if ($entrees->hasPages())

        <div class="listes-pagination">

            {{ $entrees->links() }}

        </div>

    @endif


</div>

@endsection
