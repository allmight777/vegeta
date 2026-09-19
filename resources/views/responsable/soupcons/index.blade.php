@extends('layouts.responsable')

@section('sous-titre', "Fiches d'analyse transmises par le contrôleur permanent pour les clients de votre agence : à vous de décider.")

@section('contenu')

<style>

    /* =========================================================
       LISTE DOSSIERS SUSPECTS — MÊME LANGAGE VISUEL QUE LE DASHBOARD
       --dark   : #2C343D (bleu nuit, base identité)
       --accent : #2563EB (bleu royal, accent)
    ========================================================= */

    .ds-suspects {

        --dark: #2C343D;
        --dark-soft: #3C4650;

        --accent: #2563EB;
        --accent-dark: #1D4ED8;
        --accent-soft: rgba(37, 99, 235, 0.10);
        --accent-light: #EFF6FF;

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --text: #1E293B;
        --muted: #64748B;
        --muted-light: #94A3B8;

        --border: #E8EDF2;
        --white: #FFFFFF;
        --background: #F8FAFC;

        --danger: #DC2626;
        --danger-soft: rgba(220, 38, 38, 0.08);

        --amber: #B45309;
        --amber-soft: rgba(245, 158, 11, 0.10);

        --shadow-sm: 0 4px 15px rgba(44, 52, 61, 0.04);
        --shadow:    0 12px 35px rgba(44, 52, 61, 0.07);
        --shadow-lg: 0 20px 55px rgba(44, 52, 61, 0.10);

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 18px;
    }


    /* =========================================================
       MESSAGE FLASH SUCCÈS
    ========================================================= */

    .ds-succes {

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 12px 16px;

        border-radius: 14px;

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.25);

        color: #249C13;

        font-size: 0.72rem;

        font-weight: 700;
    }


    .ds-succes i {

        font-size: 0.85rem;
    }


    /* =========================================================
       HERO — BANDEAU BLEU EN HAUT (comble le vide)
    ========================================================= */

    .ds-hero {

        min-height: 140px;

        border-radius: 22px;

        position: relative;

        overflow: hidden;

        display: grid;

        grid-template-columns: minmax(0, 1fr) auto;

        gap: 22px;

        padding: 24px 26px;

        background:
            linear-gradient(
                135deg,
                #1E3A8A 0%,
                #1D4ED8 60%,
                #2563EB 100%
            );

        box-shadow: var(--shadow-lg);

        align-items: center;
    }


    .ds-hero::before {

        content: "";

        position: absolute;

        width: 280px;

        height: 280px;

        right: -90px;

        top: -160px;

        border-radius: 50%;

        background: #60A5FA;

        opacity: 0.15;
    }


    .ds-hero::after {

        content: "";

        position: absolute;

        width: 150px;

        height: 150px;

        left: 40%;

        bottom: -110px;

        border-radius: 50%;

        background: var(--green);

        opacity: 0.06;
    }


    .ds-hero-content {

        position: relative;

        z-index: 3;
    }


    .ds-hero-tag {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 5px 11px;

        border-radius: 999px;

        background: rgba(255, 255, 255, 0.15);

        border: 1px solid rgba(255, 255, 255, 0.28);

        color: #FFFFFF;

        font-size: 0.6rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.7px;

        margin-bottom: 10px;
    }


    .ds-hero-tag i {

        color: #93C5FD;
    }


    .ds-hero-content h1 {

        color: #FFFFFF;

        font-size: clamp(1.2rem, 1.9vw, 1.65rem);

        font-weight: 800;

        line-height: 1.15;

        letter-spacing: -0.6px;

        margin: 0 0 8px 0;
    }


    .ds-hero-content h1 span {

        color: #93C5FD;
    }


    .ds-hero-content p {

        color: rgba(255, 255, 255, 0.75);

        font-size: 0.72rem;

        line-height: 1.55;

        max-width: 520px;

        margin: 0;
    }


    /* =========================================================
       MINI-STATS DANS LE HERO
    ========================================================= */

    .ds-hero-stats {

        position: relative;

        z-index: 3;

        display: flex;

        gap: 10px;

        flex-shrink: 0;
    }


    .ds-hero-stat {

        min-width: 100px;

        padding: 14px 16px;

        border-radius: 16px;

        background: rgba(255, 255, 255, 0.12);

        border: 1px solid rgba(255, 255, 255, 0.20);

        backdrop-filter: blur(8px);

        display: flex;

        flex-direction: column;

        gap: 4px;

        text-align: left;
    }


    .ds-hero-stat strong {

        color: #FFFFFF;

        font-size: 1.5rem;

        font-weight: 900;

        letter-spacing: -0.6px;

        line-height: 1;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    .ds-hero-stat span {

        color: rgba(255, 255, 255, 0.72);

        font-size: 0.58rem;

        font-weight: 700;

        letter-spacing: 0.2px;

        text-transform: uppercase;
    }


    .ds-hero-stat.urgent strong {

        color: #FCA5A5;
    }


    /* =========================================================
       ENTÊTE DE LISTE (compteur)
    ========================================================= */

    .ds-entete {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        padding: 0 4px;
    }


    .ds-entete-titre {

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .ds-entete-titre h2 {

        margin: 0;

        color: var(--dark);

        font-size: 0.85rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        display: flex;

        align-items: center;

        gap: 8px;
    }


    .ds-entete-titre h2 i {

        color: var(--accent);

        font-size: 0.82rem;
    }


    .ds-entete-titre span {

        color: var(--muted-light);

        font-size: 0.6rem;

        font-weight: 600;
    }


    .ds-compteur {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 11px;

        border-radius: 999px;

        background: var(--accent-soft);

        border: 1px solid rgba(37, 99, 235, 0.25);

        color: var(--accent-dark);

        font-size: 0.62rem;

        font-weight: 800;

        white-space: nowrap;
    }


    /* =========================================================
       LISTE
    ========================================================= */

    .ds-liste {

        display: flex;

        flex-direction: column;

        gap: 10px;
    }


    /* =========================================================
       CARTE DOSSIER
    ========================================================= */

    .ds-carte {

        position: relative;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 16px;

        padding: 16px 18px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 16px;

        box-shadow: var(--shadow-sm);

        overflow: hidden;

        transition:
            transform 0.18s ease,
            border-color 0.18s ease,
            background 0.18s ease,
            box-shadow 0.18s ease;
    }


    .ds-carte::before {

        content: "";

        position: absolute;

        left: 0;

        top: 0;

        bottom: 0;

        width: 4px;

        background: linear-gradient(180deg, var(--accent) 0%, #60A5FA 100%);

        opacity: 0.9;
    }


    .ds-carte.critique::before {

        background: linear-gradient(180deg, #EF4444 0%, #DC2626 100%);
    }


    .ds-carte.attention::before {

        background: linear-gradient(180deg, #F59E0B 0%, #B45309 100%);
    }


    .ds-carte:hover {

        transform: translateX(3px);

        background: var(--accent-light);

        border-color: rgba(37, 99, 235, 0.45);

        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.10);
    }


    /* =========================================================
       CORPS DE LA CARTE
    ========================================================= */

    .ds-carte-body {

        display: flex;

        align-items: center;

        gap: 14px;

        min-width: 0;

        flex: 1;
    }


    .ds-carte-avatar {

        width: 46px;

        height: 46px;

        flex-shrink: 0;

        border-radius: 13px;

        background: var(--accent-soft);

        color: var(--accent);

        border: 1px solid rgba(37, 99, 235, 0.20);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.95rem;
    }


    .ds-carte-avatar.critique {

        background: var(--danger-soft);

        color: var(--danger);

        border-color: rgba(220, 38, 38, 0.22);
    }


    .ds-carte-avatar.attention {

        background: var(--amber-soft);

        color: var(--amber);

        border-color: rgba(245, 158, 11, 0.25);
    }


    .ds-carte-infos {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .ds-ref {

        font-size: 0.58rem;

        font-weight: 800;

        letter-spacing: 0.6px;

        text-transform: uppercase;

        color: var(--muted-light);
    }


    .ds-nom {

        font-size: 0.85rem;

        font-weight: 800;

        color: var(--dark);

        letter-spacing: -0.2px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .ds-meta {

        font-size: 0.62rem;

        color: var(--muted);

        font-weight: 600;

        display: flex;

        align-items: center;

        flex-wrap: wrap;

        gap: 4px 8px;

        margin-top: 3px;
    }


    .ds-meta-sep {

        width: 3px;

        height: 3px;

        border-radius: 50%;

        background: var(--muted-light);

        display: inline-block;
    }


    .ds-meta strong {

        color: var(--dark);

        font-weight: 800;
    }


    /* =========================================================
       ACTIONS / BADGES
    ========================================================= */

    .ds-carte-actions {

        display: flex;

        align-items: center;

        gap: 10px;

        flex-shrink: 0;
    }


    .ds-badge {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        height: 24px;

        padding: 0 11px;

        border-radius: 999px;

        font-size: 0.58rem;

        font-weight: 800;

        letter-spacing: 0.2px;

        border: 1px solid transparent;

        white-space: nowrap;
    }


    .ds-badge i {

        font-size: 0.55rem;
    }


    .ds-badge.transmise {

        background: var(--accent-soft);

        color: var(--accent-dark);

        border-color: rgba(37, 99, 235, 0.22);
    }


    .ds-badge.traitee {

        background: var(--green-soft);

        color: #249C13;

        border-color: rgba(48, 195, 26, 0.22);
    }


    /* =========================================================
       BOUTON D'ACTION — BLEU NUIT COMME LE LAYOUT
    ========================================================= */

    .ds-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 6px;

        height: 36px;

        padding: 0 15px;

        border-radius: 10px;

        background: var(--dark);

        color: #FFFFFF;

        font-family: inherit;

        font-size: 0.64rem;

        font-weight: 800;

        text-decoration: none;

        border: none;

        cursor: pointer;

        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;

        white-space: nowrap;
    }


    .ds-btn i {

        color: #93C5FD;

        font-size: 0.62rem;
    }


    .ds-btn:hover {

        background: var(--dark-soft);

        transform: translateY(-1px);

        box-shadow: 0 8px 18px rgba(44, 52, 61, 0.20);
    }


    .ds-btn.primary {

        background: var(--accent);

        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.24);
    }


    .ds-btn.primary i {

        color: #FFFFFF;
    }


    .ds-btn.primary:hover {

        background: var(--accent-dark);

        box-shadow: 0 10px 22px rgba(37, 99, 235, 0.30);
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .ds-vide {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        padding: 44px 24px;

        text-align: center;

        color: var(--muted);

        font-size: 0.7rem;

        font-weight: 600;

        box-shadow: var(--shadow-sm);

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 10px;

        line-height: 1.6;
    }


    .ds-vide i {

        font-size: 1.6rem;

        color: var(--green);
    }


    .ds-vide strong {

        color: var(--dark);

        font-size: 0.85rem;

        font-weight: 800;

        display: block;

        margin-bottom: 2px;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 900px) {

        .ds-hero {

            grid-template-columns: 1fr;

            gap: 18px;
        }


        .ds-hero-stats {

            width: 100%;
        }


        .ds-hero-stat {

            flex: 1;
        }
    }


    @media (max-width: 640px) {

        .ds-hero {

            padding: 20px;

            border-radius: 18px;
        }


        .ds-carte {

            flex-direction: column;

            align-items: flex-start;
        }


        .ds-carte-actions {

            width: 100%;

            justify-content: space-between;
        }


        .ds-btn {

            flex: 1;
        }
    }

</style>


<div class="ds-suspects">


    {{-- =====================================================
         MESSAGE FLASH
    ====================================================== --}}

    @if (session('statut'))

        <div class="ds-succes" role="status">

            <i class="fa-solid fa-circle-check"></i>

            {{ session('statut') }}

        </div>

    @endif



    {{-- =====================================================
         HERO — BANDEAU BLEU (comble le vide en haut)
    ====================================================== --}}

    <section class="ds-hero">

        <div class="ds-hero-content">

            <div class="ds-hero-tag">

                <i class="fa-solid fa-user-shield"></i>

                Dossiers suspects reçus

            </div>


            <h1>

                Fiches d'analyse,

                <span>à vous de décider</span>

            </h1>


            <p>

                Le contrôleur permanent a transmis ces dossiers pour votre agence.
                Examinez chaque fiche puis validez votre décision — elle sera consignée à votre nom.

            </p>

        </div>


        <div class="ds-hero-stats">

            <div class="ds-hero-stat urgent">

                <strong>
                    {{ $dossiers->where('statut.value', 'transmise')->count() }}
                </strong>

                <span>
                    À traiter
                </span>

            </div>


            <div class="ds-hero-stat">

                <strong>
                    {{ $dossiers->count() }}
                </strong>

                <span>
                    Total
                </span>

            </div>

        </div>

    </section>



    {{-- =====================================================
         ÉTAT VIDE
    ====================================================== --}}

    @if ($dossiers->isEmpty())

        <div class="ds-vide">

            <i class="fa-solid fa-circle-check"></i>

            <div>

                <strong>Aucun dossier reçu pour le moment.</strong>

                Lorsque le contrôleur permanent vous transmettra une fiche
                d'analyse concernant un client de votre agence,
                elle apparaîtra ici.

            </div>

        </div>

    @else

        {{-- =================================================
             ENTÊTE DE LISTE
        ================================================== --}}

        <div class="ds-entete">

          


            <span class="ds-compteur">

                <i class="fa-solid fa-layer-group"></i>

                {{ $dossiers->count() }} dossier{{ $dossiers->count() > 1 ? 's' : '' }}

            </span>

        </div>



        {{-- =================================================
             LISTE DES DOSSIERS
        ================================================== --}}

        <div class="ds-liste">

            @foreach ($dossiers as $dossier)

                @php
                    $risque = $dossier->niveau_risque->value ?? 'info';
                    $critique = in_array($risque, ['critique', 'eleve', 'élevé']);
                    $attention = in_array($risque, ['moyen', 'attention']);
                    $avatarClass = $critique ? 'critique' : ($attention ? 'attention' : '');
                    $cardClass = $critique ? 'critique' : ($attention ? 'attention' : '');
                @endphp


                <article class="ds-carte {{ $cardClass }}">

                    <div class="ds-carte-body">

                        <div class="ds-carte-avatar {{ $avatarClass }}">

                            <i class="fa-solid fa-user-shield"></i>

                        </div>


                        <div class="ds-carte-infos">

                            <span class="ds-ref">
                                {{ $dossier->reference() }}
                            </span>

                            <span class="ds-nom">
                                {{ $dossier->client->nomAffichage() }}
                            </span>

                            <span class="ds-meta">

                                <span>
                                    Transmis le
                                    <strong>{{ $dossier->transmis_le?->format('d/m/Y H:i') }}</strong>
                                </span>

                                <span class="ds-meta-sep"></span>

                                <span>
                                    Risque : <strong>{{ $dossier->niveau_risque->libelle() }}</strong>
                                </span>

                                <span class="ds-meta-sep"></span>

                                <span>
                                    Avis : <strong>{{ $dossier->avis_technique_controleur?->libelle() }}</strong>
                                </span>

                            </span>

                        </div>

                    </div>


                    <div class="ds-carte-actions">

                        <span class="ds-badge {{ $dossier->statut->value }}">

                            @if ($dossier->statut->value === 'transmise')

                                <i class="fa-solid fa-hourglass-half"></i>
                                À traiter

                            @else

                                <i class="fa-solid fa-circle-check"></i>
                                Traité

                            @endif

                        </span>


                        <a
                            class="ds-btn {{ $dossier->statut->value === 'transmise' ? 'primary' : '' }}"
                            href="{{ route('responsable.soupcons.afficher', $dossier->id) }}"
                        >

                            @if ($dossier->statut->value === 'transmise')

                                <i class="fa-solid fa-gavel"></i>
                                Examiner et décider

                            @else

                                <i class="fa-solid fa-eye"></i>
                                Consulter

                            @endif

                        </a>

                    </div>

                </article>

            @endforeach

        </div>

    @endif


</div>

@endsection
