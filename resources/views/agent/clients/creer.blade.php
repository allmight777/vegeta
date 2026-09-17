@extends('layouts.agent')



@section('sous-titre', 'Créez une nouvelle fiche client et complétez les informations KYC.')


@section('contenu')

<style>

    /* =========================================================
       PAGE FICHE CLIENT
    ========================================================= */

    .fiche-page {

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

        max-width: 100%;

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    /* =========================================================
       EN-TÊTE DE PAGE
    ========================================================= */

    .fiche-hero {

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


    .fiche-hero::before {

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


    .fiche-hero-icon {

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


    .fiche-hero-text {

        position: relative;

        z-index: 2;

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .fiche-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .fiche-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* =========================================================
       ALERTE ERREURS
    ========================================================= */

    .fiche-alert {

        display: flex;

        align-items: flex-start;

        gap: 12px;

        padding: 14px 16px;

        border-radius: 14px;

        background: #FEF2F2;

        border: 1px solid #FECACA;

        color: var(--danger);

        font-size: 0.75rem;

        font-weight: 600;

        line-height: 1.5;

        box-shadow: var(--shadow-sm);
    }


    .fiche-alert-icon {

        width: 32px;

        height: 32px;

        flex-shrink: 0;

        border-radius: 10px;

        background: rgba(220, 38, 38, 0.10);

        color: var(--danger);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.85rem;
    }


    .fiche-alert strong {

        display: block;

        font-weight: 800;

        margin-bottom: 4px;

        font-size: 0.78rem;
    }


    .fiche-alert ul {

        margin: 0;

        padding-left: 16px;

        list-style: disc;
    }


    .fiche-alert li {

        margin-top: 2px;
    }


    /* =========================================================
       FORMULAIRE PRINCIPAL
    ========================================================= */

    .fiche-form {

        display: flex;

        flex-direction: column;

        gap: 14px;

        width: 100%;
    }


    /* =========================================================
       SECTION PLIABLE
    ========================================================= */

    .fiche-section {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }


    .fiche-section[open] {

        border-color: rgba(240, 229, 53, 0.45);

        box-shadow:
            0 10px 28px rgba(44, 52, 61, 0.06);
    }


    .fiche-section > summary {

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


    .fiche-section > summary::-webkit-details-marker {

        display: none;
    }


    .fiche-section > summary::before {

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


    .fiche-section[open] > summary::before {

        transform: rotate(180deg);
    }


    .fiche-section > summary:hover {

        background: var(--yellow-light);
    }


    .fiche-section-corps {

        padding: 6px 22px 24px;

        border-top: 1px solid var(--border);

        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 16px;
    }


    .fiche-section-corps .pleine-largeur {

        grid-column: 1 / -1;
    }


    /* =========================================================
       CHAMPS
    ========================================================= */

    .champ-fiche {

        display: flex;

        flex-direction: column;

        gap: 7px;
    }


    .champ-fiche-label {

        font-size: 0.62rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--dark);
    }


    .champ-fiche-label .obligatoire {

        color: var(--danger);

        margin-left: 3px;
    }


    .champ-fiche-input,
    .champ-fiche select,
    .champ-fiche textarea {

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


    .champ-fiche textarea {

        min-height: 90px;

        resize: vertical;
    }


    .champ-fiche-input:hover,
    .champ-fiche select:hover,
    .champ-fiche textarea:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .champ-fiche-input:focus,
    .champ-fiche select:focus,
    .champ-fiche textarea:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow:
            0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    .champ-fiche-input::placeholder,
    .champ-fiche textarea::placeholder {

        color: var(--muted-light);

        font-weight: 400;
    }


    /* =========================================================
       FORMULAIRE INCLUS (_formulaire)
    ========================================================= */

    .fiche-formulaire-page fieldset,
    .fiche-formulaire-page .fiche-groupe {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        margin: 0 0 14px;

        width: 100%;
    }


    .fiche-formulaire-page fieldset[open],
    .fiche-formulaire-page .fiche-groupe[open] {

        border-color: rgba(240, 229, 53, 0.45);
    }


    .fiche-formulaire-page legend,
    .fiche-formulaire-page .fiche-groupe > summary {

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
    }


    .fiche-formulaire-page legend {

        padding: 16px 22px 0;
    }


    .fiche-formulaire-page .fiche-groupe > summary::-webkit-details-marker {

        display: none;
    }


    .fiche-formulaire-page .fiche-groupe > summary::before {

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


    .fiche-formulaire-page .fiche-groupe[open] > summary::before {

        transform: rotate(180deg);
    }


    .fiche-formulaire-page .fiche-groupe-corps {

        padding: 20px 22px 24px;

        border-top: 1px solid var(--border);

        display: grid;

        grid-template-columns:
            repeat(3, minmax(0, 1fr));

        gap: 16px;
    }


    .fiche-formulaire-page .fiche-groupe-corps .pleine-largeur,
    .fiche-formulaire-page .fiche-groupe-corps > .pleine-largeur {

        grid-column: 1 / -1;
    }


    /* =========================================================
       ACTIONS / BOUTON
    ========================================================= */

    .fiche-actions {

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

    @media (max-width: 1100px) {

        .fiche-formulaire-page .fiche-groupe-corps {

            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }
    }


    @media (max-width: 700px) {

        .fiche-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .fiche-hero-text strong {

            font-size: 0.9rem;
        }


        .fiche-section-corps,
        .fiche-formulaire-page .fiche-groupe-corps {

            grid-template-columns: 1fr;

            padding: 14px 16px 18px;
        }


        .fiche-section > summary {

            padding: 14px 16px;

            font-size: 0.76rem;
        }


        .fiche-formulaire-page legend,
        .fiche-formulaire-page .fiche-groupe > summary {

            padding: 14px 16px;

            font-size: 0.76rem;
        }
    }


    @media (max-width: 450px) {

        .fiche-hero-icon {

            width: 40px;

            height: 40px;
        }


        .fiche-hero-text strong {

            font-size: 0.82rem;
        }


        .fiche-hero-text span {

            font-size: 0.62rem;
        }
    }

</style>


<div
    class="fiche-page"
    x-data="{ type: '{{ old('type', 'personne_physique') }}' }"
>


    {{-- =====================================================
         HERO
    ====================================================== --}}

    <div class="fiche-hero">

        <div class="fiche-hero-icon">

            <i class="fa-solid fa-user-plus"></i>

        </div>


        <div class="fiche-hero-text">

            <strong>
                Nouvelle fiche client
            </strong>

            <span>
                Renseignez les informations d'identification
                puis complétez les données KYC.
            </span>

        </div>

    </div>



    {{-- =====================================================
         ERREURS
    ====================================================== --}}

    @if ($errors->any())

        <div class="fiche-alert">

            <div class="fiche-alert-icon">

                <i class="fa-solid fa-triangle-exclamation"></i>

            </div>


            <div>

                <strong>
                    Corrigez les champs suivants :
                </strong>

                <ul>

                    @foreach ($errors->all() as $erreur)

                        <li>{{ $erreur }}</li>

                    @endforeach

                </ul>

            </div>

        </div>

    @endif



    {{-- =====================================================
         FORMULAIRE
    ====================================================== --}}

    <form
        method="POST"
        action="{{ route('agent.clients.stocker') }}"
        enctype="multipart/form-data"
        class="fiche-form fiche-formulaire-page"
    >

        @csrf


        {{-- =================================================
             TYPE DE CLIENT
        ================================================== --}}

        <details class="fiche-section" open>

            <summary>
                Type de client
            </summary>


            <div class="fiche-section-corps">


                <div class="champ-fiche">

                    <label
                        class="champ-fiche-label"
                        for="type"
                    >
                        Type de client
                        <span class="obligatoire">*</span>
                    </label>


                    <select
                        name="type"
                        id="type"
                        x-model="type"
                        class="champ-fiche-input"
                    >

                        <option value="personne_physique">
                            Personne physique
                        </option>

                        <option value="personne_morale">
                            Personne morale
                        </option>

                    </select>

                </div>


                <div class="champ-fiche">

                    <label
                        class="champ-fiche-label"
                        for="nature_relation"
                    >
                        Nature de la relation
                        <span class="obligatoire">*</span>
                    </label>


                    <select
                        name="nature_relation"
                        id="nature_relation"
                        class="champ-fiche-input"
                    >

                        <option value="titulaire_compte">
                            Titulaire de compte
                        </option>

                        <option value="occasionnel">
                            Client occasionnel
                        </option>

                    </select>

                </div>


            </div>

        </details>



        {{-- =================================================
             FORMULAIRE PERSONNE PHYSIQUE
        ================================================== --}}

        <div
            x-show="type === 'personne_physique'"
            x-cloak
        >

            @include(
                'agent.clients._formulaire',
                [
                    'type' => 'personne_physique',
                    'valeurs' => old()
                ]
            )

        </div>



        {{-- =================================================
             FORMULAIRE PERSONNE MORALE
        ================================================== --}}

        <div
            x-show="type === 'personne_morale'"
            x-cloak
        >

            @include(
                'agent.clients._formulaire',
                [
                    'type' => 'personne_morale',
                    'valeurs' => old(),
                    'repetables' => [
                        'signataires' => old('signataires', [])
                    ]
                ]
            )

        </div>



        {{-- =================================================
             ACTION
        ================================================== --}}

        <div class="fiche-actions">

            <button
                type="submit"
                class="btn-primary"
            >

                <span>
                    Créer et continuer la fiche
                </span>

                <i class="fa-solid fa-arrow-right"></i>

            </button>

        </div>


    </form>


</div>

@endsection
