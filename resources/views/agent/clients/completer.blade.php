@extends('layouts.agent')



@section('sous-titre', 'Renseignez et validez les informations KYC du dossier sélectionné.')


@section('contenu')

@include('agent.clients._styles')

<style>

    /* =========================================================
       PAGE COMPLÉTER FICHE
    ========================================================= */

    .completer-page {

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

    .completer-hero {

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


    .completer-hero::before {

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


    .completer-hero-icon {

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


    .completer-hero-text {

        position: relative;

        z-index: 2;

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .completer-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .completer-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* =========================================================
       ALERTES
    ========================================================= */

    .completer-alert {

        display: flex;

        align-items: flex-start;

        gap: 12px;

        padding: 14px 16px;

        border-radius: 14px;

        font-size: 0.75rem;

        font-weight: 600;

        line-height: 1.5;

        box-shadow: var(--shadow-sm);
    }


    .completer-alert.success {

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: #238E15;
    }


    .completer-alert.danger {

        background: #FEF2F2;

        border: 1px solid #FECACA;

        color: var(--danger);
    }


    .completer-alert-icon {

        width: 32px;

        height: 32px;

        flex-shrink: 0;

        border-radius: 10px;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.85rem;
    }


    .completer-alert.success .completer-alert-icon {

        background: rgba(48, 195, 26, 0.14);

        color: var(--green);
    }


    .completer-alert.danger .completer-alert-icon {

        background: rgba(220, 38, 38, 0.10);

        color: var(--danger);
    }


    .completer-alert strong {

        display: block;

        font-weight: 800;

        margin-bottom: 4px;

        font-size: 0.78rem;
    }


    .completer-alert ul {

        margin: 0;

        padding-left: 16px;

        list-style: disc;
    }


    /* =========================================================
       GRILLE DES CARTES
    ========================================================= */

    .completer-grid {

        display: grid;

        grid-template-columns:
            repeat(auto-fit, minmax(280px, 1fr));

        gap: 14px;
    }


    /* =========================================================
       CARTE INFO
    ========================================================= */

    .info-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        padding: 16px 18px;

        box-shadow: var(--shadow-sm);

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }


    .info-card:hover {

        border-color: rgba(240, 229, 53, 0.45);

        box-shadow:
            0 10px 28px rgba(44, 52, 61, 0.06);
    }


    .info-card-left {

        display: flex;

        align-items: center;

        gap: 12px;

        min-width: 0;
    }


    .info-card-icon {

        width: 40px;

        height: 40px;

        flex-shrink: 0;

        border-radius: 12px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.25);

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.88rem;
    }


    .info-card-icon.green {

        background: var(--green-soft);

        border-color: rgba(48, 195, 26, 0.18);

        color: var(--green);
    }


    .info-card-icon.warning {

        background: rgba(240, 229, 53, 0.18);

        border-color: rgba(240, 229, 53, 0.35);

        color: #8A7C00;
    }


    .info-card-icon.danger {

        background: var(--danger-soft);

        border-color: rgba(220, 38, 38, 0.16);

        color: var(--danger);
    }


    .info-card-body {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .info-card-label {

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted-light);
    }


    .info-card-value {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .info-card-value.green {

        color: var(--green);
    }


    .info-card-value.warning {

        color: #8A7C00;
    }


    .info-card-value.danger {

        color: var(--danger);
    }


    /* =========================================================
       SECTION CARTE (Comptes, Mandataires, Signataires)
    ========================================================= */

    .completer-section {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }


    .completer-section[open] {

        border-color: rgba(240, 229, 53, 0.45);

        box-shadow:
            0 10px 28px rgba(44, 52, 61, 0.06);
    }


    .completer-section > summary {

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


    .completer-section > summary::-webkit-details-marker {

        display: none;
    }


    .completer-section > summary::before {

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


    .completer-section[open] > summary::before {

        transform: rotate(180deg);
    }


    .completer-section > summary:hover {

        background: var(--yellow-light);
    }


    .completer-section-corps {

        padding: 4px 22px 22px;

        border-top: 1px solid var(--border);
    }


    /* =========================================================
       EN-TÊTE INTERNE DE SECTION
    ========================================================= */

    .section-inner-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        flex-wrap: wrap;

        margin: 12px 0 14px;
    }


    .section-inner-header p {

        font-size: 0.72rem;

        color: var(--muted);

        font-weight: 500;

        margin: 0;

        line-height: 1.5;

        max-width: 620px;
    }


    /* =========================================================
       LISTE COMPTES / MANDATAIRES / SIGNATAIRES
    ========================================================= */

    .entity-list {

        display: flex;

        flex-direction: column;

        gap: 8px;

        margin-bottom: 14px;
    }


    .entity-row {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        padding: 11px 14px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 12px;

        font-size: 0.76rem;

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            transform 0.2s ease;
    }


    .entity-row:hover {

        border-color: rgba(240, 229, 53, 0.45);

        background: var(--yellow-light);

        transform: translateX(2px);
    }


    .entity-row-left {

        display: flex;

        align-items: center;

        gap: 10px;

        min-width: 0;

        flex: 1;
    }


    .entity-row-avatar {

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


    .entity-row-body {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .entity-row-name {

        color: var(--dark);

        font-size: 0.76rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .entity-row-meta {

        display: flex;

        align-items: center;

        flex-wrap: wrap;

        gap: 6px;
    }


    .entity-chip {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 3px 8px;

        border-radius: 999px;

        background: var(--background);

        border: 1px solid var(--border);

        color: var(--muted);

        font-size: 0.56rem;

        font-weight: 700;

        white-space: nowrap;
    }


    .entity-chip.green {

        background: var(--green-soft);

        border-color: rgba(48, 195, 26, 0.16);

        color: #249C13;
    }


    .entity-chip.yellow {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.28);

        color: #8A7C00;
    }


    .entity-chip.danger {

        background: var(--danger-soft);

        border-color: rgba(220, 38, 38, 0.16);

        color: var(--danger);
    }


    .entity-chip-link {

        text-decoration: none;

        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 4px 9px;

        border-radius: 999px;

        background: var(--dark);

        color: #FFFFFF;

        font-size: 0.56rem;

        font-weight: 800;

        transition:
            background 0.2s ease,
            transform 0.2s ease;
    }


    .entity-chip-link i {

        color: var(--yellow);
        font-size: 0.58rem;
    }


    .entity-chip-link:hover {

        background: #3A4650;

        transform: translateY(-1px);
    }


    .entity-remove {

        border: none;

        background: var(--danger-soft);

        color: var(--danger);

        width: 32px;

        height: 32px;

        border-radius: 9px;

        cursor: pointer;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.72rem;

        transition:
            background 0.2s ease,
            transform 0.2s ease;
    }


    .entity-remove:hover {

        background: rgba(220, 38, 38, 0.16);

        transform: scale(1.05);
    }


    /* =========================================================
       AJOUT RAPIDE
    ========================================================= */

    .entity-add-form {

        display: grid;

        grid-template-columns:
            repeat(auto-fit, minmax(160px, 1fr));

        gap: 9px;

        padding: 14px;

        background: var(--background);

        border: 1px dashed var(--border);

        border-radius: 13px;
    }


    .entity-add-form .champ-fiche-input {

        min-height: 42px;

        font-size: 0.74rem;

        background: #FFFFFF;
    }


    .entity-add-form .btn-secondary {

        min-height: 42px;
    }


    .entity-add-form .full {

        grid-column: 1 / -1;
    }


    /* =========================================================
       BOUTONS
    ========================================================= */

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


    .btn-secondary.block {

        width: 100%;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 700px) {

        .completer-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .completer-hero-text strong {

            font-size: 0.9rem;
        }


        .info-card {

            padding: 13px 14px;
        }


        .completer-section > summary {

            padding: 14px 16px;

            font-size: 0.76rem;
        }


        .completer-section-corps {

            padding: 4px 16px 18px;
        }
    }


    @media (max-width: 450px) {

        .completer-hero-icon {

            width: 40px;

            height: 40px;
        }


        .completer-hero-text strong {

            font-size: 0.82rem;
        }


        .completer-hero-text span {

            font-size: 0.62rem;
        }


        .entity-row {

            flex-wrap: wrap;

            gap: 9px;
        }
    }

</style>


<div class="completer-page">


    {{-- =====================================================
         HERO
    ====================================================== --}}

    <div class="completer-hero">

        <div class="completer-hero-icon">

            <i class="fa-solid fa-user-pen"></i>

        </div>


        <div class="completer-hero-text">

            <strong>
                Compléter la fiche client
            </strong>

            <span>
                Vérifiez la conformité, complétez les informations
                KYC et gérez les tiers associés.
            </span>

        </div>

    </div>



    {{-- =====================================================
         MESSAGES DE SESSION
    ====================================================== --}}

    @if (session('statut'))

        <div class="completer-alert success">

            <div class="completer-alert-icon">

                <i class="fa-solid fa-check"></i>

            </div>

            <span>
                {{ session('statut') }}
            </span>

        </div>

    @endif


    @if ($errors->any())

        <div class="completer-alert danger">

            <div class="completer-alert-icon">

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
         INDICATEURS DE CONFORMITÉ
    ====================================================== --}}

    <div class="completer-grid">


        {{-- STATUT CONFORMITÉ --}}

        <div class="info-card">

            <div class="info-card-left">

                <div class="info-card-icon {{ $client->statutConformiteAffichable() ? 'green' : 'warning' }}">

                    <i class="fa-solid {{ $client->statutConformiteAffichable() ? 'fa-circle-check' : 'fa-clock' }}"></i>

                </div>


                <div class="info-card-body">

                    <span class="info-card-label">
                        Statut conformité
                    </span>

                    <span class="info-card-value {{ $client->statutConformiteAffichable() ? 'green' : 'warning' }}">

                        {{ $client->statutConformiteAffichable() ? 'À jour' : 'Vérification complémentaire requise' }}                        

                    </span>

                </div>

            </div>

        </div>



        {{-- VÉRIFICATION NPI --}}

        @if ($client->statut_verification_npi)

            @php

                $npiClass = match ($client->statut_verification_npi) {

                    \App\Enums\StatutVerificationNpi::VerifieValide => 'green',

                    \App\Enums\StatutVerificationNpi::EnAttenteConnexion => 'warning',

                    default => 'danger',

                };

                $npiIcon = match ($client->statut_verification_npi) {

                    \App\Enums\StatutVerificationNpi::VerifieValide => 'fa-shield-halved',

                    \App\Enums\StatutVerificationNpi::EnAttenteConnexion => 'fa-hourglass-half',

                    default => 'fa-circle-xmark',

                };

            @endphp


            <div class="info-card">

                <div class="info-card-left">

                    <div class="info-card-icon {{ $npiClass }}">

                        <i class="fa-solid {{ $npiIcon }}"></i>

                    </div>


                    <div class="info-card-body">

                        <span class="info-card-label">
                            Vérification NPI
                        </span>

                        <span class="info-card-value {{ $npiClass }}">

                            {{ $client->statut_verification_npi->libelle() }}

                        </span>

                    </div>

                </div>

            </div>

        @endif



        {{-- COMPLÉTUDE KYC --}}

        <div class="info-card">

            <div class="info-card-left">

                <div class="info-card-icon {{ $client->score_completude_kyc >= 100 ? 'green' : 'warning' }}">

                    <i class="fa-solid fa-chart-simple"></i>

                </div>


                <div class="info-card-body">

                    <span class="info-card-label">
                        Complétude KYC
                    </span>

                    <span class="info-card-value {{ $client->score_completude_kyc >= 100 ? 'green' : 'warning' }}">

                        {{ $client->score_completude_kyc }} %

                    </span>

                </div>

            </div>

        </div>


    </div>



    {{-- =====================================================
         ACTIONS RAPIDES
    ====================================================== --}}

    @if ($type === 'personne_physique')

        <div class="info-card">

            <div class="info-card-left">

                <div class="info-card-icon">

                    <i class="fa-solid fa-database"></i>

                </div>


                <div class="info-card-body">

                    <span class="info-card-label">
                        Synchronisation
                    </span>

                    <span class="info-card-value">
                        Importer les données du système existant
                    </span>

                </div>

            </div>


            <a
                href="{{ route('agent.clients.systeme-existant.rechercher', $client) }}"
                class="btn-secondary"
            >

                <i class="fa-solid fa-magnifying-glass"></i>

                Compléter

            </a>

        </div>

    @endif



    {{-- =====================================================
         COMPTES
    ====================================================== --}}

    <details class="completer-section" open>

        <summary>
            Comptes bancaires
        </summary>


        <div class="completer-section-corps">


            <div class="section-inner-header">

                <p>
                    Gérez les comptes rattachés à ce client
                    et saisissez directement des opérations.
                </p>


                <form
                    method="POST"
                    action="{{ route('agent.comptes.stocker', $client) }}"
                >

                    @csrf

                    <button
                        type="submit"
                        class="btn-secondary"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Ouvrir un compte

                    </button>

                </form>

            </div>


            <div class="entity-list">

                @forelse ($client->comptes as $compte)

                    <div class="entity-row">

                        <div class="entity-row-left">

                            <div class="entity-row-avatar">

                                <i class="fa-solid fa-wallet"></i>

                            </div>


                            <div class="entity-row-body">

                                <span class="entity-row-name">

                                    {{ $compte->numero }}

                                </span>


                                <div class="entity-row-meta">

                                    <span class="entity-chip">

                                        <i class="fa-solid fa-circle-info"></i>

                                        {{ $compte->statut->libelle() }}

                                    </span>

                                </div>

                            </div>

                        </div>


                        <a
                            href="{{ route('agent.operations.creer') }}"
                            class="entity-chip-link"
                        >

                            <i class="fa-solid fa-fingerprint"></i>

                            Saisir une opération

                        </a>

                    </div>

                @empty

                    <div class="entity-row" style="justify-content: center; color: var(--muted);">

                        Aucun compte rattaché à ce client.

                    </div>

                @endforelse

            </div>

        </div>

    </details>



    {{-- =====================================================
         FORMULAIRE PRINCIPAL
    ====================================================== --}}

    <form
        method="POST"
        action="{{ route('agent.clients.mettre-a-jour', $client) }}"
        enctype="multipart/form-data"
    >

        @csrf

        @method('PUT')


        @include('agent.clients._formulaire', [
            'type' => $type,
            'client' => $client,
            'valeurs' => $valeurs,
            'champsManquants' => $champsManquants,
            'ficheRlbcft' => $ficheRlbcft ?? [],
            'fichesRlbcftSignataires' => $fichesRlbcftSignataires ?? [],
            'signatairesExistants' => $signatairesExistants ?? null,
        ])


        <div style="padding-top: 14px;">

            <button
                type="submit"
                class="btn-primary"
            >

                <span>
                    Enregistrer la fiche
                </span>

                <i class="fa-solid fa-floppy-disk"></i>

            </button>

        </div>

    </form>



    {{-- =====================================================
         MANDATAIRES / SIGNATAIRES
    ====================================================== --}}

    @if ($type === 'personne_physique')

        <details class="completer-section" open>

            <summary>
                Mandataires désignés
            </summary>


            <div class="completer-section-corps">


                <div class="section-inner-header">

                    <p>
                        Ajoutez jusqu'à 3 mandataires autorisés
                        à agir au nom de ce client.
                    </p>

                </div>


                <div class="entity-list">

                    @forelse ($client->personnePhysique?->mandataires ?? [] as $mandataire)

                        <div class="entity-row">

                            <div class="entity-row-left">

                                <div class="entity-row-avatar">

                                    <i class="fa-solid fa-user-tie"></i>

                                </div>


                                <div class="entity-row-body">

                                    <span class="entity-row-name">

                                        {{ $mandataire->nom }} {{ $mandataire->prenoms }}

                                    </span>


                                    <div class="entity-row-meta">

                                        <span class="entity-chip">

                                            <i class="fa-solid fa-link"></i>

                                            {{ $mandataire->lien_parente }}

                                        </span>

                                    </div>

                                </div>

                            </div>


                            <form
                                method="POST"
                                action="{{ route('agent.clients.mandataires.detruire', $mandataire) }}"
                            >

                                @csrf

                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="entity-remove"
                                    title="Retirer"
                                >

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </form>

                        </div>

                    @empty

                        <div class="entity-row" style="justify-content: center; color: var(--muted);">

                            Aucun mandataire enregistré.

                        </div>

                    @endforelse

                </div>



                @if (($client->personnePhysique?->mandataires->count() ?? 0) < 3)

                    <form
                        method="POST"
                        action="{{ route('agent.clients.mandataires.stocker', $client->personnePhysique) }}"
                        class="entity-add-form"
                    >

                        @csrf


                        <input
                            type="text"
                            name="nom"
                            placeholder="Nom"
                            required
                            class="champ-fiche-input"
                        >


                        <input
                            type="text"
                            name="prenoms"
                            placeholder="Prénoms"
                            class="champ-fiche-input"
                        >


                        <input
                            type="text"
                            name="lien_parente"
                            placeholder="Lien de parenté"
                            class="champ-fiche-input"
                        >


                        <button
                            type="submit"
                            class="btn-secondary"
                        >

                            <i class="fa-solid fa-plus"></i>

                            Ajouter

                        </button>

                    </form>

                @endif

            </div>

        </details>

    @else


        {{-- =====================================================
             SIGNATAIRES / BÉNÉFICIAIRES / MANDATAIRES (PERSONNE MORALE)
        ====================================================== --}}

        <details class="completer-section" open>

            <summary>
                Signataires, mandataires & bénéficiaires effectifs
            </summary>


            <div class="completer-section-corps">


                <div class="section-inner-header">

                    <p>
                        Chaque personne ajoutée ici est contrôlée
                        individuellement contre les listes de filtrage.
                        Aucune validation tant qu'un signataire reste
                        « à vérifier ».
                    </p>

                </div>


                <div class="entity-list">

                    @forelse ($client->personneMorale?->signataires ?? [] as $signataire)

                        <div class="entity-row">

                            <div class="entity-row-left">

                                <div class="entity-row-avatar">

                                    <i class="fa-solid fa-user-shield"></i>

                                </div>


                                <div class="entity-row-body">

                                    <span class="entity-row-name">

                                        {{ $signataire->nom }}

                                    </span>


                                    <div class="entity-row-meta">

                                        <span class="entity-chip">

                                            <i class="fa-solid fa-briefcase"></i>

                                            {{ $signataire->role->libelle() }}

                                        </span>


                                        @if ($signataire->pourcentage_detention)

                                            <span class="entity-chip yellow">

                                                <i class="fa-solid fa-percent"></i>

                                                {{ $signataire->pourcentage_detention }} %

                                            </span>

                                        @endif


                                        @unless (auth('agent')->user()->estGuichet())

                                            <x-badge-statut-filtrage :statut="$signataire->statut_filtrage" />

                                        @endunless

                                    </div>

                                </div>

                            </div>


                            <form
                                method="POST"
                                action="{{ route('agent.clients.signataires.detruire', $signataire) }}"
                            >

                                @csrf

                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="entity-remove"
                                    title="Retirer"
                                >

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </form>

                        </div>

                    @empty

                        <div class="entity-row" style="justify-content: center; color: var(--muted);">

                            Aucun signataire enregistré.

                        </div>

                    @endforelse

                </div>



                @if (($client->personneMorale?->signataires->count() ?? 0) < 3)

                    <form
                        method="POST"
                        action="{{ route('agent.clients.signataires.stocker', $client->personneMorale) }}"
                        class="entity-add-form"
                    >

                        @csrf


                        <input
                            type="text"
                            name="nom"
                            placeholder="Nom complet"
                            required
                            class="champ-fiche-input"
                        >


                        <select
                            name="role"
                            class="champ-fiche-input"
                        >

                            <option value="signataire">
                                Signataire
                            </option>

                            <option value="beneficiaire_effectif">
                                Bénéficiaire effectif
                            </option>

                            <option value="mandataire">
                                Mandataire
                            </option>

                        </select>


                        <input
                            type="number"
                            step="0.01"
                            name="pourcentage_detention"
                            placeholder="% détention"
                            class="champ-fiche-input"
                        >


                        <button
                            type="submit"
                            class="btn-secondary"
                        >

                            <i class="fa-solid fa-plus"></i>

                            Ajouter

                        </button>

                    </form>

                @endif

            </div>

        </details>

    @endif


</div>

@endsection
