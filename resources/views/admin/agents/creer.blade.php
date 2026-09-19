@extends('layouts.admin')


@section('sous-titre', 'Créez un compte caissier, responsable d\'agence ou contrôleur permanent avec identifiants générés automatiquement.')


@section('contenu')

<style>

    /* =========================================================
       PAGE CRÉATION COMPTE AGENT
    ========================================================= */

    .agent-create-page {

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

    .agent-create-hero {

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


    .agent-create-hero::before {

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


    .agent-create-hero-icon {

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


    .agent-create-hero-text {

        position: relative;

        z-index: 2;

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .agent-create-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .agent-create-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* =========================================================
       ALERTE ERREUR
    ========================================================= */

    .agent-create-alert {

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


    .agent-create-alert-icon {

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


    .agent-create-alert strong {

        display: block;

        font-weight: 800;

        font-size: 0.78rem;
    }


    /* =========================================================
       CARTE FORMULAIRE
    ========================================================= */

    .agent-create-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        padding: 26px 28px;

        box-shadow: var(--shadow-sm);

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 22px;
    }


    /* =========================================================
       SECTION
    ========================================================= */

    .agent-create-section {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        overflow: hidden;

        width: 100%;

        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }


    .agent-create-section[open] {

        border-color: rgba(240, 229, 53, 0.45);

        box-shadow:
            0 10px 28px rgba(44, 52, 61, 0.06);
    }


    .agent-create-section > summary {

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


    .agent-create-section > summary::-webkit-details-marker {

        display: none;
    }


    .agent-create-section > summary::before {

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


    .agent-create-section[open] > summary::before {

        transform: rotate(180deg);
    }


    .agent-create-section > summary:hover {

        background: var(--yellow-light);
    }


    .agent-create-section-corps {

        padding: 6px 22px 22px;

        border-top: 1px solid var(--border);

        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 16px;
    }


    .agent-create-section-corps .pleine-largeur {

        grid-column: 1 / -1;
    }


    /* =========================================================
       CHAMPS
    ========================================================= */

    .champ-agent {

        display: flex;

        flex-direction: column;

        gap: 7px;

        min-width: 0;
    }


    .champ-agent-label {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        font-size: 0.62rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--dark);
    }


    .champ-agent-label i {

        color: var(--muted);

        font-size: 0.72rem;
    }


    .champ-agent-label .obligatoire {

        color: var(--danger);

        margin-left: 2px;
    }


    .champ-agent-input,
    .champ-agent select {

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


    .champ-agent-input:hover,
    .champ-agent select:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .champ-agent-input:focus,
    .champ-agent select:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow:
            0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    .champ-agent-input::placeholder {

        color: var(--muted-light);

        font-weight: 400;
    }


    /* =========================================================
       INFO-BOX
    ========================================================= */

    .agent-create-info {

        display: flex;

        align-items: flex-start;

        gap: 10px;

        padding: 12px 14px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        border-radius: 11px;

        grid-column: 1 / -1;
    }


    .agent-create-info i {

        color: #A08F00;

        font-size: 0.86rem;

        margin-top: 2px;

        flex-shrink: 0;
    }


    .agent-create-info p {

        margin: 0;

        font-size: 0.72rem;

        color: #665D00;

        line-height: 1.55;

        font-weight: 500;
    }


    /* =========================================================
       ACTIONS / BOUTON
    ========================================================= */

    .agent-create-actions {

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

    @media (max-width: 700px) {

        .agent-create-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .agent-create-hero-text strong {

            font-size: 0.9rem;
        }


        .agent-create-card {

            padding: 18px 16px;

            border-radius: 17px;
        }


        .agent-create-section-corps {

            grid-template-columns: 1fr;

            padding: 4px 16px 18px;
        }


        .agent-create-section > summary {

            padding: 14px 16px;

            font-size: 0.76rem;
        }
    }


    @media (max-width: 450px) {

        .agent-create-hero-icon {

            width: 40px;

            height: 40px;
        }


        .agent-create-hero-text strong {

            font-size: 0.82rem;
        }


        .agent-create-hero-text span {

            font-size: 0.62rem;
        }
    }

</style>


<div class="agent-create-page">


    {{-- =====================================================
         HERO
    ====================================================== --}}

    <div class="agent-create-hero">

        <div class="agent-create-hero-icon">

            <i class="fa-solid fa-user-plus"></i>

        </div>


        <div class="agent-create-hero-text">

            <strong>
                Nouveau compte agent
            </strong>

            <span>
                Créez un accès caissier ou responsable d'agence.
            </span>

        </div>

    </div>



    {{-- =====================================================
         ERREUR
    ====================================================== --}}

    @if ($errors->any())

        <div class="agent-create-alert">

            <div class="agent-create-alert-icon">

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
        action="{{ route('admin.agents.stocker') }}"
        class="agent-create-card"
    >

        @csrf


        {{-- =================================================
             IDENTIFICATION
        ================================================== --}}

        <details class="agent-create-section" open>

            <summary>
                Identification
            </summary>


            <div class="agent-create-section-corps">


                {{-- NOM --}}

                <div class="champ-agent">

                    <label
                        class="champ-agent-label"
                        for="nom"
                    >

                        <i class="fa-solid fa-user"></i>

                        Nom

                        <span class="obligatoire">*</span>

                    </label>


                    <input
                        type="text"
                        name="nom"
                        id="nom"
                        value="{{ old('nom') }}"
                        required
                        class="champ-agent-input"
                        placeholder="Nom de l'agent"
                    >

                </div>


                {{-- MATRICULE --}}

                <div class="champ-agent">

                    <label
                        class="champ-agent-label"
                        for="matricule"
                    >

                        <i class="fa-solid fa-id-badge"></i>

                        Matricule

                        <span class="obligatoire">*</span>

                    </label>


                    <input
                        type="text"
                        name="matricule"
                        id="matricule"
                        value="{{ old('matricule') }}"
                        required
                        class="champ-agent-input"
                        placeholder="Ex : AG-8849"
                    >

                </div>


                {{-- CIVILITÉ --}}

                <div class="champ-agent">

                    <label
                        class="champ-agent-label"
                        for="civilite"
                    >

                        <i class="fa-solid fa-venus-mars"></i>

                        Civilité

                        <span style="color: var(--muted-light); font-weight: 600; text-transform: none; letter-spacing: 0;">(affichage uniquement)</span>

                    </label>


                    <select
                        name="civilite"
                        id="civilite"
                        class="champ-agent-input"
                    >

                        <option
                            value="non_precise"
                            @selected(old('civilite', 'non_precise') === 'non_precise')
                        >
                            Non précisée
                        </option>

                        <option
                            value="m"
                            @selected(old('civilite') === 'm')
                        >
                            Masculin
                        </option>

                        <option
                            value="f"
                            @selected(old('civilite') === 'f')
                        >
                            Féminin
                        </option>

                    </select>

                </div>


            </div>

        </details>



        {{-- =================================================
             AFFECTATION
        ================================================== --}}

        <details class="agent-create-section" open>

            <summary>
                Affectation et rôle
            </summary>


            <div class="agent-create-section-corps">


                {{-- AGENCE (tous les rôles sauf contrôleur permanent) --}}

                <div class="champ-agent" id="bloc-agence">

                    <label
                        class="champ-agent-label"
                        for="agence_id"
                    >

                        <i class="fa-solid fa-building"></i>

                        Agence

                        <span class="obligatoire">*</span>

                    </label>


                    <select
                        name="agence_id"
                        id="agence_id"
                        class="champ-agent-input"
                    >

                        @foreach ($agences as $agence)

                            <option
                                value="{{ $agence->id }}"
                                @selected(old('agence_id') == $agence->id)
                            >
                                {{ $agence->reseau->nom }} — {{ $agence->nom }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- RÉSEAU (contrôleur permanent : supervise un réseau, pas une agence) --}}

                <div class="champ-agent" id="bloc-reseau" hidden>

                    <label class="champ-agent-label" for="reseau_id">

                        <i class="fa-solid fa-network-wired"></i>

                        Réseau supervisé

                        <span class="obligatoire">*</span>

                    </label>

                    <select name="reseau_id" id="reseau_id" class="champ-agent-input">
                        @foreach ($reseaux as $reseau)
                            <option value="{{ $reseau->id }}" @selected(old('reseau_id') == $reseau->id)>{{ $reseau->nom }}</option>
                        @endforeach
                    </select>

                </div>


                {{-- RÔLE --}}

                <div class="champ-agent">

                    <label
                        class="champ-agent-label"
                        for="role"
                    >

                        <i class="fa-solid fa-user-tag"></i>

                        Rôle

                        <span class="obligatoire">*</span>

                    </label>


                    <select
                        name="role"
                        id="role"
                        required
                        class="champ-agent-input"
                    >

                        <option
                            value="caissier"
                            @selected(old('role') === 'caissier')
                        >
                            Caissier
                        </option>

                        <option
                            value="responsable_agence"
                            @selected(old('role') === 'responsable_agence')
                        >
                            Responsable d'agence
                        </option>

                        <option
                            value="controleur_permanent"
                            @selected(old('role') === 'controleur_permanent')
                        >
                            Contrôleur permanent
                        </option>

                    </select>

                    <script>
                        (function () {
                            const role = document.getElementById('role');
                            const basculer = () => {
                                const controleur = role.value === 'controleur_permanent';
                                document.getElementById('bloc-reseau').hidden = !controleur;
                                document.getElementById('bloc-agence').hidden = controleur;
                                document.getElementById('agence_id').disabled = controleur;
                                document.getElementById('reseau_id').disabled = !controleur;
                            };
                            role.addEventListener('change', basculer);
                            basculer();
                        })();
                    </script>

                </div>


            </div>

        </details>



        {{-- =================================================
             INFORMATION
        ================================================== --}}

        <details class="agent-create-section" open>

            <summary>
                Information sur les identifiants
            </summary>


            <div class="agent-create-section-corps">


                <div class="agent-create-info">

                    <i class="fa-solid fa-circle-info"></i>

                    <p>
                        Un mot de passe est <strong>généré automatiquement</strong>
                        et affiché une seule fois après la création — aucun e-mail
                        n'est envoyé. Notez-le immédiatement pour le transmettre
                        à l'agent concerné.
                    </p>

                </div>


            </div>

        </details>



        {{-- =================================================
             ACTION
        ================================================== --}}

        <div class="agent-create-actions">

            <button
                type="submit"
                class="btn-primary"
            >

                <span>
                    Créer le compte
                </span>

                <i class="fa-solid fa-user-plus"></i>

            </button>

        </div>


    </form>


</div>

@endsection
