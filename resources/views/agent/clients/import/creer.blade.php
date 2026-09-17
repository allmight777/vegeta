@extends('layouts.agent')


@section('sous-titre', 'Importez des fiches d\'adhésion et lancez l\'extraction automatique des données.')


@section('contenu')

@include('agent.clients._styles')

<style>

    /* =========================================================
       PAGE UPLOAD FICHES
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
       SECTION CARTE
    ========================================================= */

    .upload-section {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

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

        padding: 4px 22px 24px;

        border-top: 1px solid var(--border);
    }


    /* =========================================================
       DESCRIPTION
    ========================================================= */

    .upload-description {

        font-size: 0.74rem;

        color: var(--muted);

        font-weight: 500;

        line-height: 1.6;

        margin: 14px 0 16px;

        max-width: 720px;
    }


    .upload-description strong {

        color: var(--dark);

        font-weight: 800;
    }


    /* =========================================================
       DROP ZONE
    ========================================================= */

    .drop-zone {

        position: relative;

        display: flex;

        flex-direction: column;

        align-items: center;

        justify-content: center;

        gap: 12px;

        padding: 38px 22px;

        border: 2px dashed var(--border);

        border-radius: 16px;

        background: var(--background);

        cursor: pointer;

        text-align: center;

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            transform 0.2s ease;
    }


    .drop-zone:hover {

        border-color: rgba(240, 229, 53, 0.55);

        background: var(--yellow-light);
    }


    .drop-zone-icon {

        width: 62px;

        height: 62px;

        border-radius: 50%;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.5rem;

    }


    .drop-zone strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .drop-zone span {

        color: var(--muted);

        font-size: 0.68rem;

        font-weight: 500;

        max-width: 420px;

        line-height: 1.55;
    }


    .drop-zone-tags {

        display: flex;

        flex-wrap: wrap;

        justify-content: center;

        gap: 6px;

        margin-top: 4px;
    }


    .drop-tag {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 4px 9px;

        border-radius: 999px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        color: var(--muted);

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.4px;
    }


    .drop-tag i {

        font-size: 0.6rem;

        color: var(--dark);
    }


    /* Input masqué */

    .drop-zone input[type="file"] {

        position: absolute;

        inset: 0;

        opacity: 0;

        cursor: pointer;

    }


    /* =========================================================
       LISTE DES FICHIERS SÉLECTIONNÉS
    ========================================================= */

    .file-list {

        display: flex;

        flex-direction: column;

        gap: 8px;

        margin-top: 16px;
    }


    .file-list-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 10px;

        margin-bottom: 4px;
    }


    .file-list-header strong {

        color: var(--dark);

        font-size: 0.72rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;
    }


    .file-list-count {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 4px 10px;

        border-radius: 999px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: var(--dark);

        font-size: 0.6rem;

        font-weight: 800;
    }


    .file-row {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 10px;

        padding: 10px 12px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 12px;

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            transform 0.2s ease;
    }


    .file-row:hover {

        border-color: rgba(240, 229, 53, 0.45);

        background: var(--yellow-light);

        transform: translateX(2px);
    }


    .file-row-left {

        display: flex;

        align-items: center;

        gap: 10px;

        min-width: 0;

        flex: 1;
    }


    .file-row-icon {

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

        font-size: 0.75rem;
    }


    .file-row-icon.pdf {

        background: var(--danger-soft);

        border-color: rgba(220, 38, 38, 0.18);

        color: var(--danger);
    }


    .file-row-icon.doc {

        background: rgba(44, 52, 61, 0.06);

        border-color: rgba(44, 52, 61, 0.10);

        color: var(--dark);
    }


    .file-row-icon.image {

        background: var(--green-soft);

        border-color: rgba(48, 195, 26, 0.18);

        color: var(--green);
    }


    .file-row-body {

        display: flex;

        flex-direction: column;

        gap: 2px;

        min-width: 0;
    }


    .file-row-name {

        color: var(--dark);

        font-size: 0.74rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .file-row-size {

        color: var(--muted-light);

        font-size: 0.6rem;

        font-weight: 600;
    }


    /* =========================================================
       ACTIONS
    ========================================================= */

    .upload-actions {

        padding-top: 18px;
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

    @media (max-width: 700px) {

        .upload-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .upload-hero-text strong {

            font-size: 0.9rem;
        }


        .upload-section > summary {

            padding: 14px 16px;

            font-size: 0.76rem;
        }


        .upload-section-corps {

            padding: 4px 16px 18px;
        }


        .drop-zone {

            padding: 28px 16px;
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


        .drop-zone-icon {

            width: 52px;

            height: 52px;

            font-size: 1.25rem;
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
                Uploader des fiches d'adhésion
            </strong>

            <span>
                Déposez vos documents, le système extrait
                automatiquement les informations à valider.
            </span>

        </div>

    </div>



    {{-- =====================================================
         FORMULAIRE
    ====================================================== --}}

    <form
        method="POST"
        action="{{ route('agent.clients.import.stocker') }}"
        enctype="multipart/form-data"
        x-data="{ fichiers: [] }"
        @submit="fichiers.length === 0 && $event.preventDefault()"
    >

        @csrf


        {{-- ERREUR --}}

        @if ($errors->any())

            <div class="upload-alert" style="margin-bottom: 14px;">

                <div class="upload-alert-icon">

                    <i class="fa-solid fa-triangle-exclamation"></i>

                </div>


                <strong>
                    {{ $errors->first() }}
                </strong>

            </div>

        @endif



        {{-- SECTION UPLOAD --}}

        <details class="upload-section" open>

            <summary>
                Fiches d'adhésion (PDF, Word ou photo)
            </summary>


            <div class="upload-section-corps">


                <p class="upload-description">

                    Déposez jusqu'à <strong>10 fichiers</strong>
                    (<strong>.pdf</strong>, <strong>.doc</strong>,
                    <strong>.docx</strong>, <strong>.jpg</strong>,
                    <strong>.jpeg</strong>, <strong>.png</strong>,
                    10 Mo max chacun). Chaque document sera traité
                    indépendamment ; vous validerez ensuite chaque
                    dossier un par un — aucun client n'est créé
                    automatiquement.

                </p>



                {{-- DROP ZONE --}}

                <label class="drop-zone">

                    <input
                        type="file"
                        name="documents[]"
                        multiple
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        x-on:change="fichiers = Array.from($event.target.files)"
                    >


                    <div class="drop-zone-icon">

                        <i class="fa-solid fa-file-arrow-up"></i>

                    </div>


                    <strong>
                        Cliquez pour sélectionner vos fichiers
                    </strong>


                    <span>
                        Chaque fichier sera analysé individuellement
                        et donnera lieu à une validation manuelle.
                    </span>


                    <div class="drop-zone-tags">

                        <span class="drop-tag">

                            <i class="fa-solid fa-file-pdf"></i>

                            PDF

                        </span>


                        <span class="drop-tag">

                            <i class="fa-solid fa-file-word"></i>

                            DOC

                        </span>


                        <span class="drop-tag">

                            <i class="fa-solid fa-file-image"></i>

                            IMAGE

                        </span>


                        <span class="drop-tag">

                            <i class="fa-solid fa-weight-hanging"></i>

                            10 Mo

                        </span>

                    </div>

                </label>



                {{-- LISTE DES FICHIERS --}}

                <div
                    class="file-list"
                    x-show="fichiers.length > 0"
                    x-cloak
                >

                    <div class="file-list-header">

                        <strong>
                            Fichiers sélectionnés
                        </strong>


                        <span class="file-list-count">

                            <i class="fa-solid fa-paperclip"></i>

                            <span x-text="fichiers.length"></span>

                        </span>

                    </div>


                    <template
                        x-for="fichier in fichiers"
                        :key="fichier.name"
                    >

                        <div class="file-row">

                            <div class="file-row-left">

                                <div
                                    class="file-row-icon"
                                    :class="{
                                        'pdf': fichier.name.toLowerCase().endsWith('.pdf'),
                                        'doc': fichier.name.toLowerCase().match(/\.docx?$/),
                                        'image': fichier.name.toLowerCase().match(/\.(jpe?g|png)$/)
                                    }"
                                >

                                    <i
                                        class="fa-solid"
                                        :class="{
                                            'fa-file-pdf': fichier.name.toLowerCase().endsWith('.pdf'),
                                            'fa-file-word': fichier.name.toLowerCase().match(/\.docx?$/),
                                            'fa-file-image': fichier.name.toLowerCase().match(/\.(jpe?g|png)$/),
                                            'fa-file': !fichier.name.toLowerCase().match(/\.(pdf|docx?|jpe?g|png)$/)
                                        }"
                                    ></i>

                                </div>


                                <div class="file-row-body">

                                    <span
                                        class="file-row-name"
                                        x-text="fichier.name"
                                    ></span>


                                    <span
                                        class="file-row-size"
                                        x-text="(fichier.size / 1024 / 1024).toFixed(2) + ' Mo'"
                                    ></span>

                                </div>

                            </div>

                        </div>

                    </template>

                </div>


            </div>

        </details>



        {{-- ACTIONS --}}

        <div class="upload-actions">

            <button
                type="submit"
                class="btn-primary"
            >

                <span>
                    Lancer l'extraction
                </span>

                <i class="fa-solid fa-wand-magic-sparkles"></i>

            </button>

        </div>


    </form>


</div>

@endsection
