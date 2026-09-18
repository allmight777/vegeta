@extends('layouts.agent')


@section('sous-titre', 'Enregistrez un dépôt ou un retrait sur un compte client.')


@section('contenu')

<style>

    /* =========================================================
       PAGE OPÉRATION
    ========================================================= */

    .operation-page {

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

    .operation-hero {

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


    .operation-hero::before {

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


    .operation-hero-icon {

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


    .operation-hero-text {

        position: relative;

        z-index: 2;

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .operation-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .operation-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* =========================================================
       ALERTE ERREUR
    ========================================================= */

    .operation-alert {

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


    .operation-alert-icon {

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


    .operation-alert strong {

        display: block;

        font-weight: 800;

        font-size: 0.78rem;
    }


    /* =========================================================
       CARTE FORMULAIRE — PLEINE LARGEUR
    ========================================================= */

    .operation-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        padding: 26px 30px;

        box-shadow: var(--shadow-sm);

        width: 100%;

        max-width: 100%;

        display: grid;

        grid-template-columns:
            repeat(3, minmax(0, 1fr));

        gap: 22px 24px;

        align-items: end;
    }


    /* Le bouton prend toute la ligne */

    .operation-actions {

        grid-column: 1 / -1;

        padding-top: 6px;
    }


    /* =========================================================
       CHAMPS
    ========================================================= */

    .champ-operation {

        display: flex;

        flex-direction: column;

        gap: 8px;

        min-width: 0;
    }


    .champ-operation-label {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        font-size: 0.64rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--dark);
    }


    .champ-operation-label i {

        color: var(--muted);

        font-size: 0.74rem;
    }


    .champ-operation-label .obligatoire {

        color: var(--danger);

        margin-left: 2px;
    }


    .champ-operation-input,
    .champ-operation select {

        width: 100%;

        min-height: 48px;

        padding: 12px 14px;

        border: 1px solid var(--border);

        border-radius: 12px;

        background: #F8FAFC;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.82rem;

        font-weight: 600;

        outline: none;

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            box-shadow 0.2s ease;
    }


    .champ-operation-input:hover,
    .champ-operation select:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .champ-operation-input:focus,
    .champ-operation select:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow:
            0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    .champ-operation-input::placeholder {

        color: var(--muted-light);

        font-weight: 400;
    }


    /* =========================================================
       MONTANT AVEC DEVISE
    ========================================================= */

    .montant-wrapper {

        position: relative;

        display: flex;

        align-items: center;
    }


    .montant-wrapper input {

        padding-right: 78px;
    }


    .montant-devise {

        position: absolute;

        right: 14px;

        font-size: 0.72rem;

        font-weight: 800;

        color: var(--muted);

        background: var(--yellow-soft);

        padding: 4px 9px;

        border-radius: 8px;

        border: 1px solid rgba(240, 229, 53, 0.28);

        pointer-events: none;
    }


    /* =========================================================
       BOUTON
    ========================================================= */

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

        transform: translateX(3px);
    }


    .btn-primary:active {

        transform: translateY(0);
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1100px) {

        .operation-card {

            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }
    }


    @media (max-width: 700px) {

        .operation-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .operation-hero-text strong {

            font-size: 0.9rem;
        }


        .operation-card {

            grid-template-columns: 1fr;

            padding: 18px 16px;

            border-radius: 17px;

            gap: 18px;
        }
    }


    @media (max-width: 450px) {

        .operation-hero-icon {

            width: 40px;

            height: 40px;
        }


        .operation-hero-text strong {

            font-size: 0.82rem;
        }


        .operation-hero-text span {

            font-size: 0.62rem;
        }
    }

</style>


<div class="operation-page">


    {{-- =====================================================
         HERO
    ====================================================== --}}

    <div class="operation-hero">

        <div class="operation-hero-icon">

            <i class="fa-solid fa-money-bill-transfer"></i>

        </div>


        <div class="operation-hero-text">

            <strong>
                Nouvelle opération
            </strong>

            <span>
                Enregistrez un dépôt ou un retrait sur un compte client.
            </span>

        </div>

    </div>



    {{-- =====================================================
         ERREUR
    ====================================================== --}}

    @if ($errors->any())

        <div class="operation-alert">

            <div class="operation-alert-icon">

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
        action="{{ route('agent.operations.stocker') }}"
        class="operation-card"
    >

        @csrf


        {{-- COMPTE --}}

        <div class="champ-operation">

            <label
                class="champ-operation-label"
                for="compte_id"
            >

                <i class="fa-solid fa-wallet"></i>

                Compte

                <span class="obligatoire">*</span>

            </label>


            <select
                name="compte_id"
                id="compte_id"
                required
                class="champ-operation-input"
            >

                <option value="">— Sélectionner un compte —</option>


                @foreach ($comptes as $compte)

                    <option value="{{ $compte->id }}">

                        {{ $compte->numero }} —

                        {{ $compte->client->type->value === 'personne_morale'
                            ? $compte->client->personneMorale?->raison_sociale
                            : trim(
                                ($compte->client->personnePhysique?->prenoms ?? '')
                                . ' '
                                . ($compte->client->personnePhysique?->nom ?? '')
                            ) }}

                    </option>

                @endforeach

            </select>

        </div>



        {{-- TYPE --}}

        <div class="champ-operation">

            <label
                class="champ-operation-label"
                for="type"
            >

                <i class="fa-solid fa-right-left"></i>

                Type d'opération

                <span class="obligatoire">*</span>

            </label>


            <select
                name="type"
                id="type"
                class="champ-operation-input"
            >

                <option value="depot">Dépôt</option>

                <option value="retrait">Retrait</option>

            </select>

        </div>



        {{-- MODE DE PAIEMENT --}}
        {{-- Les cumuls et les seuils ne visent que les espèces (Loi art. 17 i) : --}}
        {{-- sans ce champ, un virement de salaire déclencherait une alerte. --}}

        <div class="champ-operation">

            <label
                class="champ-operation-label"
                for="mode_paiement"
            >

                <i class="fa-solid fa-money-bill-wave"></i>

                Mode de paiement

                <span class="obligatoire">*</span>

            </label>


            <select
                name="mode_paiement"
                id="mode_paiement"
                class="champ-operation-input"
            >

                <option value="especes">Espèces</option>

                <option value="virement">Virement</option>

                <option value="mobile_money">Mobile money</option>

            </select>

        </div>


        {{-- MONTANT --}}

        <div class="champ-operation">

            <label
                class="champ-operation-label"
                for="montant"
            >

                <i class="fa-solid fa-coins"></i>

                Montant

                <span class="obligatoire">*</span>

            </label>


            <div class="montant-wrapper">

                <input
                    type="number"
                    step="0.01"
                    name="montant"
                    id="montant"
                    required
                    placeholder="0.00"
                    class="champ-operation-input"
                >


                <span class="montant-devise">
                    FCFA
                </span>

            </div>

        </div>



        {{-- ACTION --}}

        <div class="operation-actions">

            <button
                type="submit"
                class="btn-primary"
            >

                <span>
                    Enregistrer l'opération
                </span>

                <i class="fa-solid fa-arrow-right"></i>

            </button>

        </div>


    </form>


</div>

@endsection
