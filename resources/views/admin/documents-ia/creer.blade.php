@extends('layouts.admin')



@section('sous-titre', 'Ajoutez des documents de référence au corpus de l\'assistant IA.')


@section('contenu')

<style>

    /* =========================================================
       PAGE TÉLÉVERSEMENT DOCUMENTS
    ========================================================= */

    .upload-page {

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

    .upload-hero {

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


    .upload-hero::before {

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


    .upload-hero-icon {

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


    .upload-hero-text {

        position: relative;

        z-index: 2;

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .upload-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .upload-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* =========================================================
       ALERTE ERREUR
    ========================================================= */

    .upload-alert {

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


    .upload-alert-icon {

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


    .upload-alert strong {

        display: block;

        font-weight: 800;

        font-size: 0.78rem;
    }


    /* =========================================================
       CARTE FORMULAIRE
    ========================================================= */

    .upload-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        padding: 26px 28px;

        box-shadow: var(--shadow-sm);

        width: 100%;

        max-width: 100%;

        display: flex;

        flex-direction: column;

        gap: 22px;
    }


    /* =========================================================
       SECTION PLIABLE
    ========================================================= */

    .upload-section {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        overflow: hidden;

        width: 100%;

        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }


    .upload-section[open] {

        border-color: rgba(240, 229, 53, 0.45);

        box-shadow:
            0 10px 28px rgba(44, 52, 61, 0.06);
    }


    .upload-section > summary {

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


    .upload-section > summary::-webkit-details-marker {

        display: none;
    }


    .upload-section > summary::before {

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


    .upload-section[open] > summary::before {

        transform: rotate(180deg);
    }


    .upload-section > summary:hover {

        background: var(--yellow-light);
    }


    .upload-section-corps {

        padding: 4px 22px 22px;

        border-top: 1px solid var(--border);

        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 16px;
    }


    .upload-section-corps .pleine-largeur {

        grid-column: 1 / -1;
    }


    /* =========================================================
       CHAMPS
    ========================================================= */

    .champ-upload {

        display: flex;

        flex-direction: column;

        gap: 7px;

        min-width: 0;
    }


    .champ-upload-label {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        font-size: 0.62rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--dark);
    }


    .champ-upload-label i {

        color: var(--muted);

        font-size: 0.72rem;
    }


    .champ-upload-label .obligatoire {

        color: var(--danger);

        margin-left: 2px;
    }


    .champ-upload-input,
    .champ-upload select {

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


    .champ-upload-input:hover,
    .champ-upload select:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .champ-upload-input:focus,
    .champ-upload select:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow:
            0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    .champ-upload-input::placeholder {

        color: var(--muted-light);

        font-weight: 400;
    }


    /* Input file */

    .champ-upload-input[type="file"] {

        padding: 10px;

        height: auto;

        cursor: pointer;
    }


    .champ-upload-input[type="file"]::file-selector-button {

        height: 34px;

        padding: 0 14px;

        margin-right: 12px;

        border: none;

        border-radius: 8px;

        background: var(--dark);

        color: #FFFFFF;

        font-family: inherit;

        font-size: 0.68rem;

        font-weight: 800;

        cursor: pointer;

        transition: background 0.2s ease;
    }


    .champ-upload-input[type="file"]::file-selector-button:hover {

        background: #3A4650;
    }


    /* =========================================================
       DESCRIPTION SOUS CHAMP
    ========================================================= */

    .champ-aide {

        display: flex;

        align-items: flex-start;

        gap: 7px;

        font-size: 0.66rem;

        color: var(--muted);

        font-weight: 500;

        line-height: 1.5;

        padding: 8px 11px;

        background: var(--background);

        border: 1px solid var(--border);

        border-radius: 9px;
    }


    .champ-aide i {

        color: var(--muted-light);

        font-size: 0.68rem;

        margin-top: 2px;

        flex-shrink: 0;
    }


    /* =========================================================
       VISIBILITÉ (CHECKBOXES)
    ========================================================= */

    .visibility-list {

        display: flex;

        flex-direction: column;

        gap: 9px;

        grid-column: 1 / -1;
    }


    .visibility-check {

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 12px 14px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 12px;

        font-size: 0.76rem;

        font-weight: 700;

        color: var(--dark);

        cursor: pointer;

        user-select: none;

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            transform 0.2s ease;
    }


    .visibility-check:hover {

        border-color: rgba(240, 229, 53, 0.45);

        background: var(--yellow-light);

        transform: translateX(2px);
    }


    .visibility-check input[type="checkbox"] {

        width: 18px;

        height: 18px;

        accent-color: var(--yellow);

        cursor: pointer;

        flex-shrink: 0;
    }


    .visibility-check-icon {

        width: 30px;

        height: 30px;

        flex-shrink: 0;

        border-radius: 9px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.25);

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.72rem;
    }


    .visibility-check.disabled {

        opacity: 0.55;

        cursor: not-allowed;

        background: var(--background);

        border-style: dashed;
    }


    .visibility-check.disabled:hover {

        transform: none;

        background: var(--background);

        border-color: var(--border);
    }


    /* =========================================================
       ACTIONS / BOUTON
    ========================================================= */

    .upload-actions {

        display: flex;

        justify-content: flex-end;

        gap: 10px;

        padding-top: 6px;
    }


    .btn-primary {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 10px;

        height: 50px;

        padding: 0 26px;

        border: none;

        border-radius: 12px;

        background: var(--yellow);

        color: var(--dark);

        font-family: inherit;

        font-size: 0.80rem;

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

        font-size: 0.80rem;

        transition: transform 0.2s ease;
    }


    .btn-primary:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow:
            0 12px 26px rgba(240, 229, 53, 0.36);
    }


    .btn-primary:hover i {

        transform: translateY(-2px);
    }


    .btn-primary:active {

        transform: translateY(0);
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 900px) {

        .upload-section-corps {

            grid-template-columns: 1fr;

            padding: 4px 18px 18px;
        }
    }


    @media (max-width: 700px) {

        .upload-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .upload-hero-text strong {

            font-size: 0.9rem;
        }


        .upload-card {

            padding: 18px 16px;

            border-radius: 17px;
        }
    }


    @media (max-width: 450px) {

        .upload-hero-icon {

            width: 40px;

            height: 40px;
        }


        .upload-hero-text strong {

            font-size: 0.82rem;
        }


        .upload-hero-text span {

            font-size: 0.62rem;
        }
    }

</style>


<div class="upload-page">


    {{-- =====================================================
         HERO
    ====================================================== --}}

    <div class="upload-hero">

        <div class="upload-hero-icon">

            <i class="fa-solid fa-cloud-arrow-up"></i>

        </div>


        <div class="upload-hero-text">

            <strong>
                Téléverser des documents
            </strong>

            <span>
                Ajoutez des documents de référence au corpus de l'assistant IA.
            </span>

        </div>

    </div>



    {{-- =====================================================
         ERREUR
    ====================================================== --}}

    @if ($errors->any())

        <div class="upload-alert">

            <div class="upload-alert-icon">

                <i class="fa-solid fa-triangle-exclamation"></i>

            </div>


            <strong>
                {{ $errors->first() }}
            </strong>

        </div>

    @endif



    {{-- =====================================================
         FORMULAIRE
    ====================================================== --}}

    <form
        method="POST"
        action="{{ route('admin.documents-ia.stocker') }}"
        enctype="multipart/form-data"
        class="upload-card"
    >

        @csrf


        {{-- =================================================
             FICHIERS
        ================================================== --}}

        <details class="upload-section" open>

            <summary>
                Fichiers à téléverser
            </summary>


            <div class="upload-section-corps">


                <div class="champ-upload pleine-largeur">

                    <label
                        class="champ-upload-label"
                        for="documents"
                    >

                        <i class="fa-solid fa-file-arrow-up"></i>

                        Fichiers

                        <span class="obligatoire">*</span>

                    </label>


                    <input
                        type="file"
                        name="documents[]"
                        id="documents"
                        multiple
                        required
                        accept=".pdf,.doc,.docx,.xlsx,.xls,.txt,.md"
                        class="champ-upload-input"
                    >


                    <div class="champ-aide">

                        <i class="fa-solid fa-circle-info"></i>

                        <span>
                            Formats acceptés : PDF, Word, Excel, texte.
                            Maximum <strong>10 fichiers</strong>. Upload direct
                            depuis ce poste — aucun connecteur externe
                            (Google Drive ou autre).
                        </span>

                    </div>

                </div>


            </div>

        </details>



        {{-- =================================================
             PORTÉE
        ================================================== --}}

        <details class="upload-section" open>

            <summary>
                Portée des documents
            </summary>


            <div class="upload-section-corps">


                <div class="champ-upload pleine-largeur">

                    <label
                        class="champ-upload-label"
                        for="portee"
                    >

                        <i class="fa-solid fa-globe"></i>

                        Portée

                        <span class="obligatoire">*</span>

                    </label>


                    <select
                        name="portee"
                        id="portee"
                        required
                        class="champ-upload-input"
                        onchange="document.getElementById('champ-reseau').classList.toggle('hidden', this.value === 'toutes_agences'); document.getElementById('champ-agence').classList.toggle('hidden', this.value !== 'agence');"
                    >

                        <option value="toutes_agences">
                            Toutes agences
                        </option>

                        <option value="reseau">
                            Un réseau entier
                        </option>

                        <option value="agence">
                            Une seule agence
                        </option>

                    </select>

                </div>



                <div
                    id="champ-reseau"
                    class="champ-upload hidden"
                >

                    <label
                        class="champ-upload-label"
                        for="reseau_id"
                    >

                        <i class="fa-solid fa-network-wired"></i>

                        Réseau

                    </label>


                    <select
                        name="reseau_id"
                        id="reseau_id"
                        class="champ-upload-input"
                    >

                        @foreach ($reseaux as $reseau)

                            <option value="{{ $reseau->id }}">
                                {{ $reseau->nom }}
                            </option>

                        @endforeach

                    </select>

                </div>



                <div
                    id="champ-agence"
                    class="champ-upload hidden"
                >

                    <label
                        class="champ-upload-label"
                        for="agence_id"
                    >

                        <i class="fa-solid fa-building"></i>

                        Agence

                    </label>


                    <select
                        name="agence_id"
                        id="agence_id"
                        class="champ-upload-input"
                    >

                        @foreach ($agences as $agence)

                            <option value="{{ $agence->id }}">
                                {{ $agence->reseau->nom }} — {{ $agence->nom }}
                            </option>

                        @endforeach

                    </select>

                </div>


            </div>

        </details>



        {{-- =================================================
             VISIBILITÉ
        ================================================== --}}

        <details class="upload-section" open>

            <summary>
                Visibilité
            </summary>


            <div class="upload-section-corps">


                <div class="visibility-list">


                    <label class="visibility-check">

                        <input
                            type="checkbox"
                            name="visible_caissier"
                            value="1"
                        >


                        <span class="visibility-check-icon">

                            <i class="fa-solid fa-cash-register"></i>

                        </span>


                        <span>
                            Visible par les caissiers
                        </span>

                    </label>



                    <label class="visibility-check">

                        <input
                            type="checkbox"
                            name="visible_responsable_agence"
                            value="1"
                            checked
                        >


                        <span class="visibility-check-icon">

                            <i class="fa-solid fa-user-tie"></i>

                        </span>


                        <span>
                            Visible par les responsables d'agence
                        </span>

                    </label>



                    <label class="visibility-check disabled">

                        <input
                            type="checkbox"
                            checked
                            disabled
                        >


                        <span class="visibility-check-icon">

                            <i class="fa-solid fa-user-shield"></i>

                        </span>


                        <span>
                            Visible par les administrateurs (toujours)
                        </span>

                    </label>


                </div>


            </div>

        </details>



        {{-- =================================================
             ACTION
        ================================================== --}}

        <div class="upload-actions">

            <button
                type="submit"
                class="btn-primary"
            >

                <span>
                    Téléverser les documents
                </span>

                <i class="fa-solid fa-cloud-arrow-up"></i>

            </button>

        </div>


    </form>


</div>

@endsection
