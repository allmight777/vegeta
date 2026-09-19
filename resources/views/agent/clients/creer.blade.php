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

        --shadow-sm: 0 4px 15px rgba(44, 52, 61, 0.04);
        --shadow: 0 12px 35px rgba(44, 52, 61, 0.07);

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    /* HERO */

    .fiche-hero {

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


    /* ALERTE ERREURS */

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


    /* FORMULAIRE PRINCIPAL */

    .fiche-form {

        display: flex;

        flex-direction: column;

        gap: 14px;

        width: 100%;
    }


    /* SECTION PLIABLE */

    .fiche-section {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }


    .fiche-section[open] {

        border-color: rgba(240, 229, 53, 0.45);

        box-shadow: 0 10px 28px rgba(44, 52, 61, 0.06);
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

        grid-template-columns: repeat(2, minmax(0, 1fr));

        gap: 16px;
    }


    .fiche-section-corps .pleine-largeur {

        grid-column: 1 / -1;
    }


    /* CHAMPS */

    .champ-fiche {

        display: flex;

        flex-direction: column;

        gap: 7px;
    }


    .champ-fiche-label {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        font-size: 0.62rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--dark);
    }


    .champ-fiche-label i {

        color: var(--muted);

        font-size: 0.72rem;
    }


    .champ-fiche-label .obligatoire {

        color: var(--danger);

        margin-left: 2px;
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

        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }


    .champ-fiche textarea {

        min-height: 90px;

        resize: vertical;
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

        box-shadow: 0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    .champ-fiche-input::placeholder,
    .champ-fiche textarea::placeholder {

        color: var(--muted-light);

        font-weight: 400;
    }


    /* FORMULAIRE INCLUS (_formulaire) */

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

        grid-template-columns: repeat(3, minmax(0, 1fr));

        gap: 16px;
    }


    .fiche-formulaire-page .fiche-groupe-corps .pleine-largeur,
    .fiche-formulaire-page .fiche-groupe-corps > .pleine-largeur {

        grid-column: 1 / -1;
    }


    /* =========================================================
       RÉCAPITULATIF AVANT CONFIRMATION
    ========================================================= */

    .recap-card {

        background: #FFFFFF;

        border: 1px solid rgba(240, 229, 53, 0.45);

        border-radius: 18px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        width: 100%;
    }


    .recap-head {

        display: flex;

        align-items: center;

        gap: 12px;

        padding: 18px 22px;

        background: linear-gradient(180deg, #FFFDF7 0%, #FFFFFF 100%);

        border-bottom: 1px solid var(--border);
    }


    .recap-head-icon {

        width: 38px;

        height: 38px;

        flex-shrink: 0;

        border-radius: 11px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.30);

        color: #A08F00;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.88rem;
    }


    .recap-head-text {

        display: flex;

        flex-direction: column;

        gap: 2px;
    }


    .recap-head-text strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .recap-head-text span {

        color: var(--muted);

        font-size: 0.62rem;

        font-weight: 500;
    }


    .recap-body {

        padding: 20px 22px;

        display: flex;

        flex-direction: column;

        gap: 18px;
    }


    /* SECTION DANS LE RÉCAP */

    .recap-section {

        display: flex;

        flex-direction: column;

        gap: 10px;
    }


    .recap-section-titre {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        font-size: 0.6rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--dark);

        padding-bottom: 8px;

        border-bottom: 1px solid var(--border);
    }


    .recap-section-titre i {

        color: var(--yellow);

        font-size: 0.7rem;

    }


    .recap-grid {

        display: grid;

        grid-template-columns: repeat(2, minmax(0, 1fr));

        gap: 10px 20px;
    }


    .recap-item {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .recap-item.pleine-largeur {

        grid-column: 1 / -1;
    }


    .recap-item-label {

        font-size: 0.56rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted-light);
    }


    .recap-item-value {

        color: var(--dark);

        font-size: 0.78rem;

        font-weight: 700;

        letter-spacing: -0.2px;

        word-wrap: break-word;

        overflow-wrap: anywhere;
    }


    .recap-item-value.vide {

        color: var(--muted-light);

        font-style: italic;

        font-weight: 500;
    }


    /* BANDEAU SIMULATION DÉPÔT */

    .recap-depot {

        display: flex;

        align-items: flex-start;

        gap: 12px;

        padding: 14px 16px;

        border-radius: 12px;

        font-size: 0.74rem;

        font-weight: 600;

        line-height: 1.55;
    }


    .recap-depot.ok {

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: #238E15;
    }


    .recap-depot.alerte {

        background: #FEF2F2;

        border: 1px solid #FECACA;

        color: var(--danger);
    }


    .recap-depot.neutre {

        background: var(--background);

        border: 1px solid var(--border);

        color: var(--muted);
    }


    .recap-depot-icon {

        width: 32px;

        height: 32px;

        flex-shrink: 0;

        border-radius: 10px;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.85rem;
    }


    .recap-depot.ok .recap-depot-icon {

        background: rgba(48, 195, 26, 0.14);

        color: var(--green);
    }


    .recap-depot.alerte .recap-depot-icon {

        background: rgba(220, 38, 38, 0.10);

        color: var(--danger);
    }


    .recap-depot.neutre .recap-depot-icon {

        background: var(--background);

        color: var(--muted-light);
    }


    .recap-depot-body {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;

        flex: 1;
    }


    .recap-depot-titre {

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .recap-depot-texte {

        font-weight: 500;

        opacity: 0.9;
    }


    /* ACTIONS */

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

        box-shadow: 0 8px 20px rgba(240, 229, 53, 0.30);

        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;

        width: 100%;
    }


    .btn-primary i {

        font-size: 0.78rem;

        transition: transform 0.2s ease;
    }


    .btn-primary:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow: 0 12px 26px rgba(240, 229, 53, 0.36);
    }


    .btn-primary:hover i {

        transform: translateX(3px);
    }


    .btn-primary:active {

        transform: translateY(0);
    }


    .btn-primary.retour {

        background: #FFFFFF;

        color: var(--dark);

        border: 1px solid var(--border);

        box-shadow: none;
    }


    .btn-primary.retour:hover {

        background: var(--background);

        transform: translateY(-1px);

        box-shadow: 0 6px 15px rgba(44, 52, 61, 0.06);
    }


    /* RESPONSIVE */

    @media (max-width: 1100px) {

        .fiche-formulaire-page .fiche-groupe-corps {

            grid-template-columns: repeat(2, minmax(0, 1fr));
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


        .recap-grid {

            grid-template-columns: 1fr;
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
    x-data="{ type: '{{ old('type', 'personne_physique') }}', etape: 'saisie' }"
>


    {{-- HERO --}}

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



    {{-- ERREURS --}}

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



    {{-- FORMULAIRE --}}

    <form
        method="POST"
        action="{{ route('agent.clients.stocker') }}"
        enctype="multipart/form-data"
        class="fiche-form fiche-formulaire-page"
    >

        @csrf


        {{-- ÉTAPE 1 : SAISIE --}}

        <div x-show="etape === 'saisie'">


            <details class="fiche-section" open>

                <summary>
                    Type de client
                </summary>


                <div class="fiche-section-corps">


                    <div class="champ-fiche">

                        <label class="champ-fiche-label" for="type">

                            <i class="fa-solid fa-user-tag"></i>

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

                        <label class="champ-fiche-label" for="nature_relation">

                            <i class="fa-solid fa-handshake"></i>

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


            {{-- Le bloc masqué est désactivé : ses champs (telephone, email, ifu…) portent les mêmes
                 name que ceux du bloc visible et écraseraient sa saisie à l'envoi. --}}
            <fieldset x-show="type === 'personne_physique'" :disabled="type !== 'personne_physique'" x-cloak
                style="border:0;padding:0;margin:0;min-width:0">

                @include('agent.clients._formulaire', [
                    'type' => 'personne_physique',
                    'valeurs' => old()
                ])

            </fieldset>


            <fieldset x-show="type === 'personne_morale'" :disabled="type !== 'personne_morale'" x-cloak
                style="border:0;padding:0;margin:0;min-width:0">

                @include('agent.clients._formulaire', [
                    'type' => 'personne_morale',
                    'valeurs' => old(),
                    'repetables' => [
                        'signataires' => old('signataires', [])
                    ]
                ])

            </fieldset>

        </div>

       



        {{-- ÉTAPE 2 : RÉCAPITULATIF --}}

        <div x-show="etape === 'recap'" x-cloak>


            <div class="recap-card">


                <div class="recap-head">

                    <div class="recap-head-icon">

                        <i class="fa-solid fa-clipboard-check"></i>

                    </div>


                    <div class="recap-head-text">

                        <strong>
                            Récapitulatif avant enregistrement
                        </strong>

                        <span>
                            Vérifiez les informations saisies avant de confirmer l'inscription.
                        </span>

                    </div>

                </div>


                <div class="recap-body" id="recap-contenu">

                    {{-- Rempli par Alpine / JS --}}

                </div>


            </div>

        </div>



        {{-- ACTIONS --}}

        <div class="fiche-actions">

            <button
                type="button"
                class="btn-primary"
                x-show="etape === 'saisie'"
                @click="if (window.validerChampsObligatoires()) { window.afficherRecapitulatif(); etape = 'recap' }"
            >

                <span>
                    Vérifier et continuer
                </span>

                <i class="fa-solid fa-arrow-right"></i>

            </button>


            <button
                type="button"
                class="btn-primary retour"
                x-show="etape === 'recap'"
                x-cloak
                @click="etape = 'saisie'"
            >

                <i class="fa-solid fa-arrow-left"></i>

                <span>
                    Retour
                </span>

            </button>


            <button
                type="submit"
                class="btn-primary"
                x-show="etape === 'recap'"
                x-cloak
            >

                <span>
                    Confirmer l'inscription
                </span>

                <i class="fa-solid fa-check"></i>

            </button>

        </div>


    </form>

</div>



<script>

    /* =========================================================
       RÉCAPITULATIF AVANT CONFIRMATION
    ========================================================= */

    // Champs à afficher dans le récap, groupés par section.
    // Chaque entrée : [name HTML, libellé, icône FA, full-width ?]
    const RECAP_SECTIONS = [
        {
            titre: 'Type de client',
            icone: 'fa-user-tag',
            champs: [
                { name: 'type', label: 'Type de client', icone: 'fa-user-tag', type: 'select' },
                { name: 'nature_relation', label: 'Nature de la relation', icone: 'fa-handshake', type: 'select' },
            ],
        },
        {
            titre: 'Identification',
            icone: 'fa-id-card',
            champs: [
                { name: 'nom', label: 'Nom', icone: 'fa-user' },
                { name: 'prenoms', label: 'Prénoms', icone: 'fa-signature' },
                { name: 'date_naissance', label: 'Date de naissance', icone: 'fa-calendar', type: 'date' },
                { name: 'sexe', label: 'Sexe', icone: 'fa-venus-mars', type: 'select' },
                { name: 'lieu_naissance', label: 'Lieu de naissance', icone: 'fa-location-dot' },
                { name: 'type_piece_identite', label: 'Type de pièce', icone: 'fa-id-badge', type: 'select' },
                { name: 'numero_piece_identite', label: 'N° de pièce', icone: 'fa-hashtag' },
                { name: 'npi', label: 'NPI', icone: 'fa-fingerprint' },
                { name: 'date_expiration_piece', label: 'Expiration pièce', icone: 'fa-calendar-xmark', type: 'date' },
                { name: 'methode_validation', label: 'Méthode de validation', icone: 'fa-circle-check', type: 'select' },
            ],
        },
        {
            titre: 'Coordonnées',
            icone: 'fa-address-book',
            champs: [
                { name: 'telephone', label: 'Téléphone', icone: 'fa-phone' },
                { name: 'email', label: 'Email', icone: 'fa-envelope' },
                { name: 'adresse', label: 'Adresse', icone: 'fa-house', full: true },
                { name: 'domicile', label: 'Domicile', icone: 'fa-house-user' },
                { name: 'lot', label: 'Lot', icone: 'fa-map-pin' },
                { name: 'maison', label: 'Maison', icone: 'fa-house-chimney' },
                { name: 'quartier', label: 'Quartier', icone: 'fa-map' },
                { name: 'indication_maison', label: 'Indication maison', icone: 'fa-comment-dots' },
                { name: 'indication_lieu_travail', label: 'Lieu de travail', icone: 'fa-briefcase' },
            ],
        },
        {
            titre: 'Filiation et situation',
            icone: 'fa-people-roof',
            champs: [
                { name: 'pere', label: 'Père', icone: 'fa-user-tie' },
                { name: 'mere', label: 'Mère', icone: 'fa-user-nurse' },
                { name: 'conjoint', label: 'Conjoint(e)', icone: 'fa-heart' },
                { name: 'statut_matrimonial', label: 'Statut matrimonial', icone: 'fa-ring', type: 'select' },
                { name: 'nationalite', label: 'Nationalité', icone: 'fa-flag' },
                { name: 'employeur', label: 'Employeur', icone: 'fa-building' },
            ],
        },
        {
            titre: 'Activité économique',
            icone: 'fa-chart-line',
            champs: [
                { name: 'profession', label: 'Profession', icone: 'fa-user-gear' },
                { name: 'ifu', label: 'IFU', icone: 'fa-hashtag' },
                { name: 'rccm', label: 'N° RCCM', icone: 'fa-file-contract' },
                { name: 'activite_1', label: 'Activité 1', icone: 'fa-briefcase' },
                { name: 'activite_2', label: 'Activité 2', icone: 'fa-briefcase' },
                { name: 'revenus_mensuels_estimes', label: 'Revenus mensuels estimés', icone: 'fa-coins' },
            ],
        },
        {
            titre: 'Versements initiaux',
            icone: 'fa-money-bill-wave',
            champs: [
                { name: 'droit_adhesion', label: 'Droit d\'adhésion', icone: 'fa-receipt' },
                { name: 'part_sociale', label: 'Part sociale', icone: 'fa-pie-chart' },
                { name: 'depot_especes', label: 'Dépôt espèces', icone: 'fa-money-bill' },
                { name: 'total_versements_initiaux', label: 'Total versements initiaux', icone: 'fa-calculator' },
            ],
        },
        {
            titre: 'Signatures',
            icone: 'fa-signature',
            champs: [
                { name: 'signature_responsable_nom', label: 'Nom du responsable', icone: 'fa-user-shield' },
                { name: 'signature_responsable_fonction', label: 'Fonction du responsable', icone: 'fa-id-badge' },
                { name: 'signature_responsable_date', label: 'Date signature', icone: 'fa-calendar', type: 'date' },
            ],
        },
        {
            titre: 'Personne morale (si applicable)',
            icone: 'fa-building',
            champs: [
                { name: 'raison_sociale', label: 'Raison sociale', icone: 'fa-building-columns', full: true },
                { name: 'forme_juridique', label: 'Forme juridique', icone: 'fa-sitemap', type: 'select' },
                { name: 'date_creation', label: 'Date de création', icone: 'fa-calendar', type: 'date' },
                { name: 'beneficiaire_effectif_texte', label: 'Bénéficiaire effectif', icone: 'fa-user-secret', full: true },
            ],
        },
    ];


    /* Récupère l'élément input visible pour un name donné (ignore l'autre type). */
    function champVisible(nom) {

        const elements = document.querySelectorAll(`[name="${nom}"]`);

        for (const element of elements) {

            if (element.offsetParent !== null) {

                return element;
            }
        }

        return null;
    }


    /* Lit la valeur affichable d'un champ (texte pour input/textarea, libellé pour select). */
    function lireValeur(champ) {

        if (! champ) {

            return null;
        }


        if (champ.tagName === 'SELECT') {

            const option = champ.options[champ.selectedIndex];

            return option && option.value !== '' ? option.text.trim() : null;
        }


        if (champ.type === 'file') {

            return champ.files?.[0]?.name ?? null;
        }


        const valeur = (champ.value ?? '').trim();

        return valeur === '' ? null : valeur;
    }


    /* Échappe le HTML pour éviter toute injection depuis un champ. */
    function echapper(valeur) {

        const div = document.createElement('div');

        div.textContent = valeur ?? '';

        return div.innerHTML;
    }


    /* Construit le HTML d'une section du récap. */
    function construireSection(section) {

        const items = section.champs.map(champ => {

            const element = champVisible(champ.name);

            const valeur = lireValeur(element);

            const classe = valeur === null ? 'recap-item-value vide' : 'recap-item-value';

            const texte = valeur === null ? 'Non renseigné' : echapper(valeur);

            return `
                <div class="recap-item ${champ.full ? 'pleine-largeur' : ''}">
                    <span class="recap-item-label">
                        ${echapper(champ.label)}
                    </span>
                    <span class="${classe}">
                        ${texte}
                    </span>
                </div>
            `;
        }).join('');


        return `
            <div class="recap-section">
                <div class="recap-section-titre">
                    <i class="fa-solid ${section.icone}"></i>
                    ${echapper(section.titre)}
                </div>
                <div class="recap-grid">
                    ${items}
                </div>
            </div>
        `;
    }


    /* Construit le bandeau de simulation dépôt mobile money. */
    function construireBandeauDepot() {

        const telephone = champVisible('telephone')?.value?.trim() ?? '';

        const resultat = window.__resultatSimulationDepot;


        if (! telephone) {

            return `
                <div class="recap-depot neutre">
                    <div class="recap-depot-icon"><i class="fa-solid fa-circle-info"></i></div>
                    <div class="recap-depot-body">
                        <span class="recap-depot-titre">Aucun numéro de téléphone saisi</span>
                        <span class="recap-depot-texte">La simulation de dépôt mobile money sera ignorée.</span>
                    </div>
                </div>
            `;
        }


        if (! resultat || ! resultat.trouve) {

            return `
                <div class="recap-depot neutre">
                    <div class="recap-depot-icon"><i class="fa-solid fa-circle-info"></i></div>
                    <div class="recap-depot-body">
                        <span class="recap-depot-titre">Numéro non reconnu dans l'annuaire simulé</span>
                        <span class="recap-depot-texte">Aucune vérification de titulaire possible pour ${echapper(telephone)}.</span>
                    </div>
                </div>
            `;
        }


        const nomSaisi = [champVisible('prenoms')?.value, champVisible('nom')?.value]
            .filter(Boolean).join(' ').trim();

        const normaliser = (v) => (v ?? '').toUpperCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^A-Z ]/g, '').trim();

        const correspond = normaliser(nomSaisi) === normaliser(resultat.nom_titulaire);


        if (correspond) {

            return `
                <div class="recap-depot ok">
                    <div class="recap-depot-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="recap-depot-body">
                        <span class="recap-depot-titre">Correspondance vérifiée</span>
                        <span class="recap-depot-texte">
                            Titulaire simulé : <strong>${echapper(resultat.nom_titulaire)}</strong>
                            — opérateur ${echapper(resultat.operateur_libelle ?? 'inconnu')}.
                        </span>
                    </div>
                </div>
            `;
        }


        return `
            <div class="recap-depot alerte">
                <div class="recap-depot-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="recap-depot-body">
                    <span class="recap-depot-titre">Nom différent du titulaire simulé</span>
                    <span class="recap-depot-texte">
                        Le nom saisi ne correspond pas au titulaire du numéro ${echapper(telephone)}.
                        Titulaire simulé : <strong>${echapper(resultat.nom_titulaire)}</strong>.
                        Le responsable d'agence sera alerté.
                    </span>
                </div>
            </div>
        `;
    }


    /* Affiche le récapitulatif complet. */
    /* Entoure en rouge les champs obligatoires vides du formulaire visible ; renvoie true si tout est rempli. */
    window.validerChampsObligatoires = function () {

        let premier = null;
        const manquants = [];

        document.querySelectorAll('.champ-fiche[data-obligatoire]').forEach((bloc) => {

            const zone = bloc.closest('[x-show]');

            if (zone && zone.style.display === 'none') {

                return;
            }

            const champ = bloc.querySelector('input:not([type="hidden"]), select, textarea');

            if (! champ || champ.disabled) {

                return;
            }

            const vide = (champ.value ?? '').trim() === '';

            bloc.classList.toggle('champ-erreur', vide);
            bloc.querySelector('.champ-erreur-message')?.remove();

            if (vide) {

                const message = document.createElement('p');
                message.className = 'champ-erreur-message';
                message.setAttribute('role', 'alert');
                message.textContent = 'Ce champ est obligatoire.';
                bloc.appendChild(message);

                manquants.push(bloc.dataset.libelle);
                premier = premier ?? champ;

                champ.addEventListener('input', () => {

                    bloc.classList.remove('champ-erreur');
                    bloc.querySelector('.champ-erreur-message')?.remove();
                }, { once: true });
            }
        });

        if (premier) {

            premier.closest('details')?.setAttribute('open', '');
            premier.scrollIntoView({ behavior: 'smooth', block: 'center' });
            premier.focus({ preventScroll: true });
        }

        return manquants.length === 0;
    };


    window.afficherRecapitulatif = function () {

        const conteneur = document.getElementById('recap-contenu');

        if (! conteneur) {

            return;
        }


        const sections = RECAP_SECTIONS.map(construireSection).join('');

        const bandeau = construireBandeauDepot();


        conteneur.innerHTML = bandeau + sections;
    };

</script>

@endsection
