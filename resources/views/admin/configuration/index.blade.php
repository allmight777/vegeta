@extends('layouts.admin')


@section('sous-titre', "Nom, logos et couleurs du produit : le changement s'applique partout, immédiatement.")

@section('contenu')

@php
    $groupes = [
        'Couleurs du produit' => [
            'couleur_primaire' => 'Primaire (jaune)',
            'couleur_secondaire' => 'Secondaire (vert)',
            'couleur_accent' => 'Accent',
            'couleur_sombre' => 'Sombre (anthracite)',
        ],
        'Accent de chaque espace' => [
            'couleur_espace_caissier' => 'Espace caissier',
            'couleur_espace_responsable' => "Espace responsable d'agence",
            'couleur_espace_admin' => 'Espace administrateur',
        ],
        'Page de connexion' => [
            'couleur_page_connexion' => 'Fond de la page de connexion',
        ],
    ];
    $logos = [
        'logo_principal_path' => ['Logo principal', "En-tête des trois espaces. Sans logo : pastille « CIF »."],
        'logo_connexion_path' => ['Logo de connexion', "Pages de connexion. Sans logo : image d'origine."],
        'favicon_path' => ['Favicon', "Icône d'onglet du navigateur. Sans favicon : image d'origine."],
    ];
    $urlActuelle = fn ($champ) => ! empty($valeurs[$champ]) && is_file(public_path('identite/'.$valeurs[$champ]))
        ? asset('identite/'.$valeurs[$champ]) : null;
@endphp

<style>

    /* =========================================================
       PAGE CONFIGURATION — identité visuelle JAUNE
    ========================================================= */

    .cfg {

        --yellow: #F0E535;
        --yellow-soft: rgba(240, 229, 53, 0.12);
        --yellow-light: #FFFDE7;

        --dark: #2C343D;
        --dark-soft: #3C4650;

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --danger: #DC2626;
        --danger-soft: rgba(220, 38, 38, 0.08);

        --amber: #B45309;
        --amber-soft: rgba(245, 158, 11, 0.10);

        --text: #1E293B;
        --muted: #64748B;
        --muted-light: #94A3B8;

        --border: #E8EDF2;
        --white: #FFFFFF;
        --background: #F8FAFC;

        --shadow-sm: 0 4px 15px rgba(44, 52, 61, 0.04);
        --shadow: 0 12px 35px rgba(44, 52, 61, 0.07);

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    /* =========================================================
       HERO
    ========================================================= */

    .cfg-hero {

        display: flex;

        align-items: center;

        gap: 15px;

        padding: 18px 22px;

        border-radius: 20px;

        background: linear-gradient(135deg, #2C343D 0%, #3A4650 65%, #303A43 100%);

        position: relative;

        overflow: hidden;

        box-shadow: var(--shadow);
    }


    .cfg-hero::before {

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


    .cfg-hero-icon {

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


    .cfg-hero-text {

        position: relative;

        z-index: 2;

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .cfg-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .cfg-hero-text span {

        color: rgba(255, 255, 255, 0.72);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* =========================================================
       MESSAGES
    ========================================================= */

    .cfg-succes {

        display: flex;

        align-items: center;

        gap: 12px;

        padding: 14px 16px;

        border-radius: 14px;

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: #238E15;

        font-size: 0.75rem;

        font-weight: 700;

        box-shadow: var(--shadow-sm);
    }


    .cfg-succes i {

        font-size: 0.9rem;
    }


    .cfg-alerte {

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


    .cfg-alerte i {

        margin-top: 2px;

        font-size: 0.9rem;
    }


    /* =========================================================
       CARTES
    ========================================================= */

    .cfg-carte {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        padding: 22px 24px;

        box-shadow: var(--shadow-sm);

        display: flex;

        flex-direction: column;

        gap: 16px;

        width: 100%;
    }


    .cfg-carte-head {

        display: flex;

        align-items: center;

        gap: 11px;

        padding-bottom: 14px;

        border-bottom: 1px solid var(--border);
    }


    .cfg-carte-head-icon {

        width: 34px;

        height: 34px;

        flex-shrink: 0;

        border-radius: 10px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.30);

        color: #A08F00;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.85rem;
    }


    .cfg-carte-head-text {

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .cfg-carte-head-text strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .cfg-carte-head-text span {

        color: var(--muted);

        font-size: 0.62rem;

        font-weight: 500;
    }


    /* =========================================================
       GRILLE
    ========================================================= */

    .cfg-grille {

        display: grid;

        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));

        gap: 16px;
    }


    /* =========================================================
       CHAMPS
    ========================================================= */

    .cfg-champ {

        display: flex;

        flex-direction: column;

        gap: 7px;

        min-width: 0;
    }


    .cfg-champ label {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        color: var(--dark);

        font-size: 0.62rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;
    }


    .cfg-champ label i {

        color: var(--muted);

        font-size: 0.7rem;
    }


    .cfg input[type=text] {

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

        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }


    .cfg input[type=text]:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .cfg input[type=text]:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow: 0 0 0 3px var(--yellow-soft);
    }


    .cfg small {

        display: block;

        color: var(--muted);

        font-size: 0.66rem;

        font-weight: 500;

        margin-top: 2px;

        line-height: 1.5;
    }


    .cfg-erreur {

        color: var(--danger);

        font-size: 0.68rem;

        margin-top: 4px;

        font-weight: 700;
    }


    /* =========================================================
       SÉLECTEUR COULEUR
    ========================================================= */

    .cfg-couleur {

        display: flex;

        align-items: center;

        gap: 12px;

        padding: 10px 12px;

        background: #F8FAFC;

        border: 1px solid var(--border);

        border-radius: 11px;

        transition: border-color 0.2s ease, background 0.2s ease;
    }


    .cfg-couleur:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .cfg-couleur input[type=color] {

        width: 48px;

        height: 36px;

        padding: 2px;

        border: 1px solid var(--border);

        border-radius: 8px;

        background: #FFFFFF;

        cursor: pointer;
    }


    .cfg-couleur code {

        font-family: 'JetBrains Mono', ui-monospace, monospace;

        font-size: 0.72rem;

        font-weight: 700;

        color: var(--dark);

        letter-spacing: 0.3px;

    }


    /* =========================================================
       APERÇU EN DIRECT
    ========================================================= */

    .cfg-bande {

        border-radius: 14px;

        overflow: hidden;

        border: 1px solid var(--border);

    }


    .cfg-bande-entete {

        display: flex;

        align-items: center;

        gap: 12px;

        padding: 16px 20px;

        color: #FFFFFF;
    }


    .cfg-bande-logo {

        width: 44px;

        height: 44px;

        border-radius: 12px;

        display: flex;

        align-items: center;

        justify-content: center;

        font-weight: 900;

        font-size: 0.72rem;

        overflow: hidden;

        flex-shrink: 0;
    }


    .cfg-bande-logo img {

        width: 100%;

        height: 100%;

        object-fit: contain;
    }


    .cfg-bande-entete-texte {

        display: flex;

        flex-direction: column;

        gap: 2px;
    }


    .cfg-bande-entete-texte strong {

        font-weight: 800;

        font-size: 0.86rem;

        letter-spacing: -0.2px;
    }


    .cfg-bande-entete-texte span {

        font-size: 0.66rem;

        opacity: 0.75;
    }


    .cfg-bande-pastilles {

        display: flex;

        flex-wrap: wrap;

        gap: 8px;

        padding: 16px 20px;

        background: #F8FAFC;
    }


    .cfg-pastille {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 6px 12px;

        border-radius: 999px;

        font-size: 0.62rem;

        font-weight: 800;

        letter-spacing: 0.3px;

        text-transform: uppercase;
    }


    /* =========================================================
       LOGOS
    ========================================================= */

    .cfg-logo-apercu {

        width: 72px;

        height: 72px;

        border: 1px dashed var(--border);

        border-radius: 14px;

        display: flex;

        align-items: center;

        justify-content: center;

        overflow: hidden;

        background: #F8FAFC;

        margin-bottom: 10px;
    }


    .cfg-logo-apercu img {

        max-width: 100%;

        max-height: 100%;

        object-fit: contain;
    }


    .cfg-logo-apercu span {

        font-size: 0.6rem;

        color: var(--muted-light);

        text-align: center;

        padding: 0 6px;

        line-height: 1.3;
    }


    .cfg input[type=file] {

        width: 100%;

        padding: 10px 12px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #F8FAFC;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.72rem;

        font-weight: 600;

        outline: none;

        cursor: pointer;

        transition: border-color 0.2s ease, background 0.2s ease;
    }


    .cfg input[type=file]:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .cfg input[type=file]::file-selector-button {

        height: 32px;

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

        transition: background 0.2s ease;
    }


    .cfg input[type=file]::file-selector-button:hover {

        background: var(--dark-soft);
    }


    .cfg-retirer {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        margin-top: 8px;

        font-size: 0.68rem;

        font-weight: 700;

        color: var(--danger);

        cursor: pointer;

        user-select: none;
    }


    .cfg-retirer input[type=checkbox] {

        width: 15px;

        height: 15px;

        accent-color: var(--danger);

        cursor: pointer;
    }


    /* =========================================================
       BOUTONS
    ========================================================= */

    .cfg-actions {

        display: flex;

        flex-wrap: wrap;

        gap: 12px;

        align-items: center;
    }


    .cfg-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 9px;

        height: 48px;

        padding: 0 24px;

        border: none;

        border-radius: 12px;

        font-family: inherit;

        font-size: 0.76rem;

        font-weight: 800;

        cursor: pointer;

        transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
        white-space: nowrap;
    }


    .cfg-btn i {

        font-size: 0.76rem;
    }


    .cfg-btn-principal {

        background: var(--yellow);

        color: var(--dark);

        box-shadow: 0 8px 20px rgba(240, 229, 53, 0.30);
    }


    .cfg-btn-principal:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow: 0 12px 26px rgba(240, 229, 53, 0.36);
    }


    .cfg-btn-secondaire {

        background: #FFFFFF;

        color: var(--dark);

        border: 1px solid var(--border);
    }


    .cfg-btn-secondaire:hover {

        background: var(--background);

        border-color: #CBD5E1;

        transform: translateY(-1px);
    }


    /* =========================================================
       CARTE RÉINITIALISATION
    ========================================================= */

    .cfg-carte-reinit {

        background: #FFFBEB;

        border: 1px solid rgba(245, 158, 11, 0.28);
    }


    .cfg-carte-reinit .cfg-carte-head-icon {

        background: var(--amber-soft);

        border-color: rgba(245, 158, 11, 0.28);

        color: var(--amber);
    }


    .cfg-carte-reinit p {

        margin: 0 0 12px;

        color: #665D00;

        font-size: 0.72rem;

        line-height: 1.6;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 700px) {

        .cfg-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .cfg-hero-text strong {

            font-size: 0.9rem;
        }


        .cfg-carte {

            padding: 18px 16px;

            border-radius: 17px;
        }


        .cfg-grille {

            grid-template-columns: 1fr;
        }


        .cfg-btn {

            width: 100%;
        }


        .cfg-actions {

            width: 100%;
        }
    }

</style>


<div
    class="cfg"
    x-data="{
        nom: @js(old('nom_systeme', $valeurs['nom_systeme'])),
        sousTitre: @js(old('sous_titre', $valeurs['sous_titre'])),
        c: {
            @foreach (array_keys(array_merge(...array_values($groupes))) as $champ)
            {{ $champ }}: @js(old($champ, $valeurs[$champ])),
            @endforeach
        },
        logo: @js($urlActuelle('logo_principal_path')),
        apercu(evt) {
            const f = evt.target.files[0];
            return f ? URL.createObjectURL(f) : null;
        }
    }"
>


    {{-- HERO --}}

    <div class="cfg-hero">

        <div class="cfg-hero-icon">

            <i class="fa-solid fa-palette"></i>

        </div>


        <div class="cfg-hero-text">

            <strong>
                Configuration de l'identité visuelle
            </strong>

            <span>
                Nom, logos et couleurs du produit — appliqués instantanément sur tous les espaces.
            </span>

        </div>

    </div>



    {{-- MESSAGES --}}

    @if (session('succes'))

        <div class="cfg-succes" role="status">

            <i class="fa-solid fa-circle-check"></i>

            <span>{{ session('succes') }}</span>

        </div>

    @endif


    @if ($errors->any())

        <div class="cfg-alerte" role="alert">

            <i class="fa-solid fa-triangle-exclamation"></i>

            <span>
                Certaines valeurs ne sont pas valides : corrigez les champs signalés
                puis enregistrez de nouveau.
            </span>

        </div>

    @endif



    {{-- APERÇU EN DIRECT --}}

    <div class="cfg-carte">

        <div class="cfg-carte-head">

            <div class="cfg-carte-head-icon">

                <i class="fa-solid fa-eye"></i>

            </div>


            <div class="cfg-carte-head-text">

                <strong>
                    Aperçu en direct
                </strong>

                <span>
                    Prévisualisation — rien n'est appliqué tant que vous n'avez pas enregistré
                </span>

            </div>

        </div>


        <div class="cfg-bande">

            <div class="cfg-bande-entete" :style="`background:${c.couleur_sombre}`">

                <div
                    class="cfg-bande-logo"
                    :style="`background:${c.couleur_sombre};color:${c.couleur_espace_admin};border:1px solid ${c.couleur_espace_admin}`"
                >

                    <template x-if="logo"><img :src="logo" alt=""></template>
                    <template x-if="!logo"><span>CIF</span></template>

                </div>


                <div class="cfg-bande-entete-texte">

                    <strong x-text="nom || 'Nom du système'"></strong>
                    <span x-text="sousTitre"></span>

                </div>

            </div>


            <div class="cfg-bande-pastilles">

                <span class="cfg-pastille" :style="`background:${c.couleur_primaire};color:${c.couleur_sombre}`">
                    Primaire
                </span>

                <span class="cfg-pastille" :style="`background:${c.couleur_secondaire};color:#fff`">
                    Secondaire
                </span>

                <span class="cfg-pastille" :style="`background:${c.couleur_accent};color:#fff`">
                    Accent
                </span>

                <span class="cfg-pastille" :style="`background:${c.couleur_espace_caissier};color:${c.couleur_sombre}`">
                    Caissier
                </span>

                <span class="cfg-pastille" :style="`background:${c.couleur_espace_responsable};color:#fff`">
                    Responsable
                </span>

                <span class="cfg-pastille" :style="`background:${c.couleur_espace_admin};color:${c.couleur_sombre}`">
                    Admin
                </span>

                <span class="cfg-pastille" :style="`background:${c.couleur_page_connexion};color:${c.couleur_sombre};border:1px solid #CBD5E1`">
                    Page de connexion
                </span>

            </div>

        </div>

    </div>



    {{-- FORMULAIRE --}}

    <form
        method="POST"
        action="{{ route('admin.configuration.mettre-a-jour') }}"
        enctype="multipart/form-data"
        class="cfg"
    >

        @csrf
        @method('PUT')


        {{-- NOM DU SYSTÈME --}}

        <div class="cfg-carte">

            <div class="cfg-carte-head">

                <div class="cfg-carte-head-icon">

                    <i class="fa-solid fa-tag"></i>

                </div>


                <div class="cfg-carte-head-text">

                    <strong>
                        Nom du système
                    </strong>

                    <span>
                        Nom et sous-titre affichés dans les en-têtes et les pages de connexion
                    </span>

                </div>

            </div>


            <div class="cfg-grille">

                <div class="cfg-champ">

                    <label for="nom_systeme">

                        <i class="fa-solid fa-tag"></i>

                        Nom

                    </label>


                    <input
                        type="text"
                        id="nom_systeme"
                        name="nom_systeme"
                        x-model="nom"
                        maxlength="60"
                        required
                    >


                    @error('nom_systeme')

                        <div class="cfg-erreur">{{ $message }}</div>

                    @enderror

                </div>


                <div class="cfg-champ">

                    <label for="sous_titre">

                        <i class="fa-solid fa-heading"></i>

                        Sous-titre

                    </label>


                    <input
                        type="text"
                        id="sous_titre"
                        name="sous_titre"
                        x-model="sousTitre"
                        maxlength="100"
                        required
                    >


                    @error('sous_titre')

                        <div class="cfg-erreur">{{ $message }}</div>

                    @enderror

                </div>

            </div>

        </div>



        {{-- LOGOS ET FAVICON --}}

        <div class="cfg-carte">

            <div class="cfg-carte-head">

                <div class="cfg-carte-head-icon">

                    <i class="fa-solid fa-image"></i>

                </div>


                <div class="cfg-carte-head-text">

                    <strong>
                        Logos et favicon
                    </strong>

                    <span>
                        PNG, JPG ou SVG, 2 Mo maximum
                    </span>

                </div>

            </div>


            <div class="cfg-grille">

                @foreach ($logos as $champ => [$titre, $aide])


                    <div class="cfg-champ" x-data="{ nouveau: null, retirer: false }">

                        <label for="{{ $champ }}">

                            <i class="fa-solid fa-image"></i>

                            {{ $titre }}

                        </label>


                        <div class="cfg-logo-apercu">

                            <template x-if="nouveau"><img :src="nouveau" alt="Aperçu"></template>

                            <template x-if="!nouveau && !retirer && {{ $urlActuelle($champ) ? 'true' : 'false' }}">
                                <img src="{{ $urlActuelle($champ) }}" alt="Actuel">
                            </template>

                            <template x-if="!nouveau && (retirer || {{ $urlActuelle($champ) ? 'false' : 'true' }})">
                                <span>Image d'origine</span>
                            </template>

                        </div>


                        <input
                            type="file"
                            id="{{ $champ }}"
                            name="{{ $champ }}"
                            accept=".png,.jpg,.jpeg,.svg,image/png,image/jpeg,image/svg+xml"
                            @change="nouveau = apercu($event); retirer = false; @if ($champ === 'logo_principal_path') logo = nouveau; @endif"
                        >


                        <small>{{ $aide }}</small>


                        @if ($urlActuelle($champ))

                            <label class="cfg-retirer">

                                <input
                                    type="checkbox"
                                    name="retirer_{{ $champ }}"
                                    value="1"
                                    x-model="retirer"
                                    @if ($champ === 'logo_principal_path')
                                        @change="if (retirer) { logo = null; nouveau = null }"
                                    @endif
                                >

                                Retirer cette image

                            </label>

                        @endif


                        @error($champ)

                            <div class="cfg-erreur">{{ $message }}</div>

                        @enderror

                    </div>

                @endforeach

            </div>

        </div>



        {{-- GROUPES DE COULEURS --}}

        @foreach ($groupes as $titre => $champs)


            <div class="cfg-carte">

                <div class="cfg-carte-head">

                    <div class="cfg-carte-head-icon">

                        <i class="fa-solid fa-swatchbook"></i>

                    </div>


                    <div class="cfg-carte-head-text">

                        <strong>
                            {{ $titre }}
                        </strong>

                        <span>
                            Cliquez sur la pastille pour ouvrir le sélecteur
                        </span>

                    </div>

                </div>


                <div class="cfg-grille">

                    @foreach ($champs as $champ => $libelle)


                        <div class="cfg-champ">

                            <label for="{{ $champ }}">

                                <i class="fa-solid fa-palette"></i>

                                {{ $libelle }}

                            </label>


                            <div class="cfg-couleur">

                                <input
                                    type="color"
                                    id="{{ $champ }}"
                                    name="{{ $champ }}"
                                    x-model="c.{{ $champ }}"
                                >

                                <code x-text="c.{{ $champ }}"></code>

                            </div>


                            @error($champ)

                                <div class="cfg-erreur">{{ $message }}</div>

                            @enderror

                        </div>

                    @endforeach

                </div>

            </div>

        @endforeach



        {{-- ACTIONS --}}

        <div class="cfg-actions">

            <button type="submit" class="cfg-btn cfg-btn-principal">

                <i class="fa-solid fa-floppy-disk"></i>

                Enregistrer les modifications

            </button>

        </div>


    </form>



    {{-- RÉINITIALISATION --}}

    <div class="cfg-carte cfg-carte-reinit">

        <div class="cfg-carte-head">

            <div class="cfg-carte-head-icon">

                <i class="fa-solid fa-rotate-left"></i>

            </div>


            <div class="cfg-carte-head-text">

                <strong>
                    Rétablir les valeurs par défaut
                </strong>

                <span>
                    Restaure l'identité visuelle d'origine
                </span>

            </div>

        </div>


        <p>
            Remet exactement l'identité visuelle d'origine (nom, couleurs, logos).
            À utiliser si un choix de couleurs rend l'écran illisible.
        </p>


        <form
            method="POST"
            action="{{ route('admin.configuration.reinitialiser') }}"
            onsubmit="return confirm('Rétablir l\'identité visuelle d\'origine ?');"
        >

            @csrf

            <button type="submit" class="cfg-btn cfg-btn-secondaire">

                <i class="fa-solid fa-rotate-left"></i>

                Rétablir les valeurs par défaut

            </button>

        </form>

    </div>


</div>

@endsection
