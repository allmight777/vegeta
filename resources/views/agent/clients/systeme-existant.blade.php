@extends('layouts.agent')



@section('sous-titre', 'Comparez et importez les données disponibles depuis le core banking ou l\'API.')


@section('contenu')

<style>

    /* =========================================================
       PAGE SYSTÈME EXISTANT
    ========================================================= */

    .sys-page {

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

    .sys-hero {

        display: flex;

        align-items: center;

        gap: 15px;

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


    .sys-hero::before {

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


    .sys-hero-icon {

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

        position: relative;

        z-index: 2;
    }


    .sys-hero-text {

        position: relative;

        z-index: 2;

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .sys-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .sys-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* =========================================================
       ÉTAT VIDE / INFO
    ========================================================= */

    .sys-empty {

        display: flex;

        flex-direction: column;

        align-items: center;

        text-align: center;

        gap: 12px;

        padding: 55px 25px;

        background: #FFFFFF;

        border: 1px dashed var(--border);

        border-radius: 18px;

        box-shadow: var(--shadow-sm);
    }


    .sys-empty-icon {

        width: 60px;

        height: 60px;

        border-radius: 50%;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.5rem;
    }


    .sys-empty strong {

        color: var(--dark);

        font-size: 0.9rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .sys-empty p {

        color: var(--muted);

        font-size: 0.72rem;

        font-weight: 500;

        line-height: 1.55;

        max-width: 420px;

        margin: 0;
    }


    .sys-empty.success .sys-empty-icon {

        background: var(--green-soft);

        border-color: rgba(48, 195, 26, 0.20);

        color: var(--green);
    }


    /* =========================================================
       BOUTON SECONDAIRE
    ========================================================= */

    .btn-secondary {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        height: 42px;

        padding: 0 18px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #FFFFFF;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.72rem;

        font-weight: 800;

        text-decoration: none;

        cursor: pointer;

        box-shadow: var(--shadow-sm);

        transition:
            background 0.2s ease,
            border-color 0.2s ease,
            transform 0.2s ease;
    }


    .btn-secondary i {

        font-size: 0.72rem;

        color: var(--muted);
    }


    .btn-secondary:hover {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.45);

        transform: translateY(-1px);
    }


    .btn-secondary:hover i {

        color: var(--dark);
    }


    /* =========================================================
       SECTION / DETAILS
    ========================================================= */

    .sys-section {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }


    .sys-section[open] {

        border-color: rgba(240, 229, 53, 0.45);

        box-shadow:
            0 10px 28px rgba(44, 52, 61, 0.06);
    }


    .sys-section > summary {

        display: flex;

        align-items: center;

        gap: 12px;

        padding: 16px 22px;

        cursor: pointer;

        list-style: none;

        color: var(--dark);

        font-size: 0.82rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        transition: background 0.2s ease;
    }


    .sys-section > summary::-webkit-details-marker {

        display: none;
    }


    .sys-section > summary::before {

        content: "\f078";

        font-family: "Font Awesome 6 Free";

        font-weight: 900;

        width: 26px;

        height: 26px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        border-radius: 8px;

        background: var(--yellow-soft);

        color: var(--dark);

        font-size: 0.62rem;

        transition: transform 0.25s ease;
    }


    .sys-section[open] > summary::before {

        transform: rotate(180deg);
    }


    .sys-section > summary:hover {

        background: var(--yellow-light);
    }


    .sys-section-corps {

        padding: 6px 22px 22px;

        border-top: 1px solid var(--border);

        display: flex;

        flex-direction: column;

        gap: 10px;
    }


    /* =========================================================
       LIGNE DE COMPARAISON
    ========================================================= */

    .compare-row {

        display: grid;

        grid-template-columns:
            1.3fr
            1fr
            1fr;

        gap: 14px;

        align-items: center;

        padding: 12px 14px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 13px;

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            transform 0.2s ease;
    }


    .compare-row:hover {

        border-color: rgba(240, 229, 53, 0.45);

        background: var(--yellow-light);

        transform: translateX(2px);
    }


    /* =========================================================
       CHECKBOX + LABEL
    ========================================================= */

    .compare-check {

        display: flex;

        align-items: center;

        gap: 10px;

        cursor: pointer;

        user-select: none;

        min-width: 0;
    }


    .compare-check input[type="checkbox"] {

        width: 18px;

        height: 18px;

        accent-color: var(--yellow);

        cursor: pointer;

        flex-shrink: 0;
    }


    .compare-check strong {

        color: var(--dark);

        font-size: 0.76rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;
    }


    /* =========================================================
       VALEURS
    ========================================================= */

    .compare-value {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .compare-value-label {

        font-size: 0.55rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.5px;

        color: var(--muted-light);
    }


    .compare-value-text {

        color: var(--muted);

        font-size: 0.74rem;

        font-weight: 600;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .compare-value-text.empty {

        color: var(--muted-light);

        font-style: italic;

        font-weight: 500;
    }


    .compare-value.new .compare-value-text {

        color: var(--dark);

        font-weight: 800;
    }


    .compare-value.new .compare-value-text::before {

        content: "";

        display: inline-block;

        width: 6px;

        height: 6px;

        border-radius: 50%;

        background: var(--green);

        margin-right: 7px;

        vertical-align: middle;

        box-shadow:
            0 0 0 3px rgba(48, 195, 26, 0.12);
    }


    /* =========================================================
       ACTIONS
    ========================================================= */

    .sys-actions {

        display: flex;

        justify-content: flex-end;

        gap: 10px;

        padding-top: 4px;
    }


    .btn-primary {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 9px;

        height: 48px;

        padding: 0 26px;

        border: none;

        border-radius: 12px;

        background: var(--yellow);

        color: var(--dark);

        font-family: inherit;

        font-size: 0.78rem;

        font-weight: 800;

        cursor: pointer;

        text-decoration: none;

        box-shadow:
            0 8px 20px rgba(240, 229, 53, 0.30);

        transition:
            background 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;

        width: 100%;
    }


    .btn-primary i {

        font-size: 0.78rem;

        transition: transform 0.2s ease;
    }


    .btn-primary:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow:
            0 12px 26px rgba(240, 229, 53, 0.36);
    }


    .btn-primary:hover i {

        transform: translateX(3px);
    }


    .btn-primary:active {

        transform: translateY(0);
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 850px) {

        .compare-row {

            grid-template-columns: 1fr;

            gap: 10px;

            padding: 12px;
        }


        .compare-check {

            padding-bottom: 8px;

            border-bottom: 1px dashed var(--border);
        }


        .sys-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .sys-hero-text strong {

            font-size: 0.9rem;
        }
    }


    @media (max-width: 450px) {

        .sys-hero-icon {

            width: 40px;

            height: 40px;
        }


        .sys-hero-text strong {

            font-size: 0.82rem;
        }


        .sys-hero-text span {

            font-size: 0.62rem;
        }
    }

</style>


<div class="sys-page">


    {{-- =====================================================
         HERO
    ====================================================== --}}

    <div class="sys-hero">

        <div class="sys-hero-icon">

            <i class="fa-solid fa-database"></i>

        </div>


        <div class="sys-hero-text">

            <strong>
                Synchronisation système existant
            </strong>

            <span>
                Importez les informations du core banking
                ou de l'API directement dans la fiche.
            </span>

        </div>

    </div>



    {{-- =====================================================
         CAS 1 : AUCUNE CORRESPONDANCE
    ====================================================== --}}

    @unless ($trouve)

        <div class="sys-empty">

            <div class="sys-empty-icon">

                <i class="fa-solid fa-magnifying-glass-minus"></i>

            </div>


            <strong>
                Aucune correspondance trouvée
            </strong>


            <p>
                Aucun autre enregistrement (import CSV core banking
                ou API) ne correspond à ce NPI ou à ce nom
                et cette date de naissance.
            </p>


            <a
                href="{{ route('agent.clients.completer', $client) }}"
                class="btn-secondary"
                style="margin-top: 6px;"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Retour à la fiche

            </a>

        </div>

    @else


        {{-- =====================================================
             CAS 2 : CORRESPONDANCE SANS NOUVEAUTÉ
        ====================================================== --}}

        @if ($comparaison === [])

            <div class="sys-empty success">

                <div class="sys-empty-icon">

                    <i class="fa-solid fa-circle-check"></i>

                </div>


                <strong>
                    Fiche déjà à jour
                </strong>


                <p>
                    Une correspondance a été trouvée dans le système
                    existant, mais tous les champs disponibles
                    sont déjà renseignés ici.
                </p>


                <a
                    href="{{ route('agent.clients.completer', $client) }}"
                    class="btn-secondary"
                    style="margin-top: 6px;"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    Retour à la fiche

                </a>

            </div>

        @else


            {{-- =====================================================
                 CAS 3 : COMPARAISON À APPLIQUER
            ====================================================== --}}

            <form
                method="POST"
                action="{{ route('agent.clients.systeme-existant.appliquer', $client) }}"
            >

                @csrf


                <details class="sys-section" open>

                    <summary>

                        Champs différents ou manquants
                        — cochez ceux à importer

                    </summary>


                    <div class="sys-section-corps">


                        @foreach ($comparaison as $code => $info)


                            <div class="compare-row">


                                {{-- CHECKBOX + LIBELLÉ --}}

                                <label class="compare-check">

                                    <input
                                        type="checkbox"
                                        name="champs[{{ $code }}]"
                                        value="1"
                                        @checked($info['vide'])
                                    >

                                    <input
                                        type="hidden"
                                        name="valeurs[{{ $code }}]"
                                        value="{{ $info['trouvee'] }}"
                                    >

                                    <strong>

                                        {{ $info['libelle'] }}

                                    </strong>

                                </label>



                                {{-- VALEUR ACTUELLE --}}

                                <div class="compare-value">

                                    <span class="compare-value-label">
                                        Actuel
                                    </span>

                                    <span class="compare-value-text {{ $info['actuelle'] ? '' : 'empty' }}">

                                        {{ $info['actuelle'] ?: 'Non renseigné' }}

                                    </span>

                                </div>



                                {{-- VALEUR SYSTÈME EXISTANT --}}

                                <div class="compare-value new">

                                    <span class="compare-value-label">
                                        Système existant
                                    </span>

                                    <span class="compare-value-text">

                                        {{ $info['trouvee'] }}

                                    </span>

                                </div>


                            </div>

                        @endforeach


                    </div>

                </details>



                {{-- ACTION --}}

                <div class="sys-actions" style="margin-top: 14px;">

                    <button
                        type="submit"
                        class="btn-primary"
                    >

                        <span>
                            Appliquer les champs cochés
                        </span>

                        <i class="fa-solid fa-check"></i>

                    </button>

                </div>


            </form>

        @endif

    @endunless


</div>

@endsection
