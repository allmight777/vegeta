@extends('layouts.responsable')



@section('sous-titre', 'Tous les comptes de la même personne, toutes agences confondues.')


@section('contenu')

<style>

    /* =========================================================
       VUE CONSOLIDÉE DE LA PERSONNE
       Palette : bleu nuit (#2C343D) + accent bleu royal (#2563EB)
    ========================================================= */

    .identite-page {

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
       EN-TÊTE DE PAGE (HERO)
    ========================================================= */

    .identite-header {

        display: flex;

        align-items: center;

        gap: 16px;

        padding: 22px 26px;

        border-radius: 22px;

        background:
            linear-gradient(
                135deg,
                #1E3A8A 0%,
                #1D4ED8 60%,
                #2563EB 100%
            );

        position: relative;

        overflow: hidden;

        box-shadow: var(--shadow);

        color: #FFFFFF;
    }


    .identite-header::before {

        content: "";

        position: absolute;

        width: 260px;

        height: 260px;

        right: -100px;

        top: -140px;

        border-radius: 50%;

        background: #60A5FA;

        opacity: 0.15;
    }


    .identite-header::after {

        content: "";

        position: absolute;

        width: 140px;

        height: 140px;

        left: 45%;

        bottom: -100px;

        border-radius: 50%;

        background: var(--green);

        opacity: 0.06;
    }


    .identite-avatar {

        width: 64px;

        height: 64px;

        flex-shrink: 0;

        border-radius: 18px;

        background: rgba(255, 255, 255, 0.16);

        border: 1px solid rgba(255, 255, 255, 0.28);

        color: #FFFFFF;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.5rem;

        position: relative;

        z-index: 2;

        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.18);
    }


    .identite-header h2 {

        color: #FFFFFF;

        font-size: clamp(1.05rem, 1.6vw, 1.35rem);

        font-weight: 800;

        letter-spacing: -0.5px;

        margin: 0 0 10px;

        line-height: 1.2;

        position: relative;

        z-index: 2;
    }


    .identite-chips {

        display: flex;

        flex-wrap: wrap;

        gap: 8px;

        position: relative;

        z-index: 2;
    }


    .chip-dark {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 6px 11px;

        border-radius: 999px;

        background: rgba(255, 255, 255, 0.14);

        border: 1px solid rgba(255, 255, 255, 0.24);

        color: #FFFFFF;

        font-size: 0.62rem;

        font-weight: 800;

        white-space: nowrap;

        backdrop-filter: blur(6px);
    }


    .chip-dark i {

        font-size: 0.66rem;

        color: #93C5FD;
    }


    .chip-dark.npi {

        background: rgba(48, 195, 26, 0.18);

        border-color: rgba(48, 195, 26, 0.35);

    }


    .chip-dark.npi i {

        color: #6EE85A;
    }


    .chip-dark.empreinte {

        background: rgba(245, 158, 11, 0.18);

        border-color: rgba(245, 158, 11, 0.35);

    }


    .chip-dark.empreinte i {

        color: #FBBF24;
    }


    /* =========================================================
       BLOCS (CARTES)
    ========================================================= */

    .bloc {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }


    .bloc:hover {

        border-color: rgba(37, 99, 235, 0.28);

        box-shadow:
            0 10px 28px rgba(37, 99, 235, 0.06);
    }


    .bloc-titre {

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 16px 22px;

        border-bottom: 1px solid var(--border);

        background: linear-gradient(180deg, #FFFFFF 0%, #FBFCFE 100%);

        color: var(--dark);

        font-size: 0.82rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .bloc-titre i {

        width: 32px;

        height: 32px;

        flex-shrink: 0;

        border-radius: 10px;

        background: var(--accent-soft);

        color: var(--accent);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.78rem;
    }


    .bloc-corps {

        padding: 18px 22px 22px;

        display: flex;

        flex-direction: column;

        gap: 10px;
    }


    /* =========================================================
       JAUGE PLAFOND QUOTIDIEN
    ========================================================= */

    .jauge-chiffres {

        display: flex;

        align-items: baseline;

        justify-content: space-between;

        gap: 12px;

        flex-wrap: wrap;

        margin-bottom: 4px;
    }


    .jauge-cumul {

        color: var(--dark);

        font-size: 1.4rem;

        font-weight: 900;

        letter-spacing: -0.6px;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    .jauge-plafond {

        color: var(--muted);

        font-size: 0.68rem;

        font-weight: 700;

        letter-spacing: 0.2px;
    }


    .jauge-bar {

        width: 100%;

        height: 10px;

        background: var(--background);

        border: 1px solid var(--border);

        border-radius: 999px;

        overflow: hidden;

        position: relative;
    }


    .jauge-bar span {

        display: block;

        height: 100%;

        border-radius: 999px;

        background:
            linear-gradient(
                90deg,
                var(--accent) 0%,
                #60A5FA 100%
            );

        transition: width 0.6s ease;
    }


    .jauge-bar span.warn {

        background:
            linear-gradient(
                90deg,
                #F59E0B 0%,
                #FBBF24 100%
            );
    }


    .jauge-bar span.danger {

        background:
            linear-gradient(
                90deg,
                var(--danger) 0%,
                #F87171 100%
            );
    }


    .jauge-note {

        color: var(--muted);

        font-size: 0.68rem;

        font-weight: 500;

        line-height: 1.6;

        margin: 8px 0 0;
    }


    /* =========================================================
       LIGNES (comptes, rattachements)
    ========================================================= */

    .ligne {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        padding: 12px 14px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 13px;

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            transform 0.2s ease;
    }


    .ligne:hover {

        border-color: rgba(37, 99, 235, 0.45);

        background: var(--accent-light);

        transform: translateX(2px);
    }


    .ligne-gauche {

        display: flex;

        align-items: center;

        gap: 12px;

        min-width: 0;

        flex: 1;
    }


    .ligne-icone {

        width: 36px;

        height: 36px;

        flex-shrink: 0;

        border-radius: 10px;

        background: var(--accent-soft);

        color: var(--accent);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.78rem;
    }


    .ligne-titre {

        color: var(--dark);

        font-size: 0.76rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .ligne-titre a {

        color: var(--dark);

        text-decoration: none;

        border-bottom: 1px dashed rgba(37, 99, 235, 0.35);

        transition: color 0.2s ease, border-color 0.2s ease;
    }


    .ligne-titre a:hover {

        color: var(--accent);

        border-bottom-color: var(--accent);
    }


    .ligne-sous {

        color: var(--muted-light);

        font-size: 0.6rem;

        font-weight: 600;

        margin-top: 3px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .ligne-droite {

        color: var(--muted);

        font-size: 0.62rem;

        font-weight: 700;

        white-space: nowrap;

        padding: 4px 9px;

        border-radius: 8px;

        background: var(--background);

        border: 1px solid var(--border);
    }


    /* =========================================================
       ALERTES
    ========================================================= */

    .alerte {

        padding: 13px 15px;

        border-radius: 13px;

        border: 1px solid var(--border);

        background: #FFFFFF;

        display: flex;

        flex-direction: column;

        gap: 8px;

        transition: border-color 0.2s ease, background 0.2s ease;
    }


    .alerte:hover {

        background: #FBFCFE;
    }


    .alerte.critique {

        border-color: rgba(220, 38, 38, 0.28);

        background: linear-gradient(180deg, #FFFFFF 0%, #FEF7F7 100%);

        border-left: 4px solid var(--danger);
    }


    .alerte.attention {

        border-color: rgba(245, 158, 11, 0.28);

        background: linear-gradient(180deg, #FFFFFF 0%, #FFFCF5 100%);

        border-left: 4px solid #F59E0B;
    }


    .alerte.info {

        border-color: rgba(37, 99, 235, 0.24);

        background: linear-gradient(180deg, #FFFFFF 0%, #F5F9FF 100%);

        border-left: 4px solid var(--accent);
    }


    .alerte-tete {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 10px;
    }


    .alerte-type {

        color: var(--dark);

        font-size: 0.72rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .alerte-date {

        color: var(--muted-light);

        font-size: 0.58rem;

        font-weight: 600;
    }


    .alerte-texte {

        color: var(--text);

        font-size: 0.72rem;

        line-height: 1.55;

        font-weight: 500;

        margin: 0;
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .vide {

        padding: 22px 18px;

        text-align: center;

        color: var(--muted);

        font-size: 0.7rem;

        font-weight: 600;

        background: var(--background);

        border: 1px dashed var(--border);

        border-radius: 12px;

        margin: 0;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 850px) {

        .identite-header {

            padding: 18px 18px;

            border-radius: 18px;
        }


        .identite-avatar {

            width: 54px;

            height: 54px;

            border-radius: 15px;

            font-size: 1.3rem;
        }


        .identite-header h2 {

            font-size: 1rem;
        }


        .bloc-titre {

            padding: 14px 18px;

        }


        .bloc-corps {

            padding: 15px 18px 18px;
        }


        .jauge-cumul {

            font-size: 1.2rem;
        }
    }


    @media (max-width: 560px) {

        .ligne {

            flex-direction: column;

            align-items: flex-start;

            gap: 8px;
        }


        .ligne-droite {

            align-self: flex-end;
        }


        .chip-dark {

            font-size: 0.58rem;

            padding: 5px 9px;
        }
    }

</style>


<div class="identite-page">


    {{-- =====================================================
         EN-TÊTE
    ====================================================== --}}

    <div class="identite-header">

        <div class="identite-avatar">

            <i class="fa-solid fa-fingerprint"></i>

        </div>


        <div style="min-width: 0;">

            <h2>
                Personne physique consolidée
            </h2>


            <div class="identite-chips">

                <span class="chip-dark">

                    <i class="fa-solid fa-folder-open"></i>

                    {{ $clients->count() }} fiche{{ $clients->count() > 1 ? 's' : '' }}

                </span>


                <span class="chip-dark">

                    <i class="fa-solid fa-wallet"></i>

                    {{ $comptes->count() }} compte{{ $comptes->count() > 1 ? 's' : '' }}

                </span>


                <span class="chip-dark">

                    <i class="fa-solid fa-location-dot"></i>

                    {{ $comptes->pluck('agence_id')->unique()->count() }} agence{{ $comptes->pluck('agence_id')->unique()->count() > 1 ? 's' : '' }}

                </span>


                @if ($identite->rapprocheeParNpi())

                    <span class="chip-dark npi">

                        <i class="fa-solid fa-id-card"></i>

                        Rapprochement par NPI vérifié

                    </span>

                @else

                    <span class="chip-dark empreinte">

                        <i class="fa-solid fa-wave-square"></i>

                        Rapprochement par empreinte — à confirmer

                    </span>

                @endif

            </div>

        </div>

    </div>



    {{-- =====================================================
         PLAFOND QUOTIDIEN
    ====================================================== --}}

    <div class="bloc">

        <div class="bloc-titre">

            <i class="fa-solid fa-money-bill-wave"></i>

            Plafond quotidien espèces

        </div>


        <div class="bloc-corps">


            @if ((float) $identite->plafond_quotidien_especes > 0)

                @php
                    $classeJauge = $pourcentagePlafond >= 100
                        ? 'danger'
                        : ($pourcentagePlafond >= 80 ? 'warn' : '');
                @endphp


                <div class="jauge-chiffres">

                    <span class="jauge-cumul">

                        {{ number_format($cumul?->totalRetenu() ?? 0, 0, ',', ' ') }} XOF

                    </span>


                    <span class="jauge-plafond">

                        plafond {{ number_format((float) $identite->plafond_quotidien_especes, 0, ',', ' ') }} XOF
                        &middot; {{ $pourcentagePlafond }} %

                    </span>

                </div>


                <div class="jauge-bar">

                    <span
                        class="{{ $classeJauge }}"
                        style="width: {{ $pourcentagePlafond }}%"
                    ></span>

                </div>


                <p class="jauge-note">

                    Calculé sur : {{ $identite->base_calcul_plafond }}
                    &middot; source {{ $identite->source_plafond->libelle() }}

                    @if ($cumul !== null)

                        <br>

                        {{ $cumul->nb_operations }} opération{{ $cumul->nb_operations > 1 ? 's' : '' }}
                        sur {{ $cumul->nb_comptes }} compte{{ $cumul->nb_comptes > 1 ? 's' : '' }},
                        dans {{ $cumul->nb_agences }} agence{{ $cumul->nb_agences > 1 ? 's' : '' }}.

                    @endif

                </p>

            @else

                <p class="vide">

                    Aucun plafond calculé : profil client à compléter.

                </p>

            @endif

        </div>

    </div>



    {{-- =====================================================
         COMPTES DE LA PERSONNE
    ====================================================== --}}

    <div class="bloc">

        <div class="bloc-titre">

            <i class="fa-solid fa-building-columns"></i>

            Comptes de la personne

        </div>


        <div class="bloc-corps">

            @forelse ($comptes as $compte)

                <div class="ligne">

                    <div class="ligne-gauche">

                        <div class="ligne-icone">

                            <i class="fa-solid fa-wallet"></i>

                        </div>


                        <div>

                            <div class="ligne-titre">

                                {{ $compte->agence->nom }}

                            </div>


                            <div class="ligne-sous">

                                {{ $compte->statut->libelle() }}

                            </div>

                        </div>

                    </div>


                    <span class="ligne-droite">

                        {{ $compte->derniere_operation_le?->diffForHumans() ?? 'aucune opération' }}

                    </span>

                </div>

            @empty

                <p class="vide">
                    Aucun compte ouvert.
                </p>

            @endforelse

        </div>

    </div>



    {{-- =====================================================
         POURQUOI CES COMPTES SONT LIÉS
    ====================================================== --}}

    <div class="bloc">

        <div class="bloc-titre">

            <i class="fa-solid fa-diagram-project"></i>

            Pourquoi ces comptes sont-ils liés ?

        </div>


        <div class="bloc-corps">

            @foreach ($rattachements as $rattachement)

                <div class="ligne">

                    <div class="ligne-gauche">

                        <div class="ligne-icone">

                            <i class="fa-solid fa-link"></i>

                        </div>


                        <div>

                            <div class="ligne-titre">

                                <a href="{{ route('agent.clients.completer', $rattachement->client) }}">

                                    {{ $rattachement->client->nomAffichage() }}

                                </a>

                            </div>


                            <div class="ligne-sous">

                                {{ $rattachement->justification() }}

                            </div>

                        </div>

                    </div>


                    <span class="ligne-droite">

                        {{ $rattachement->created_at->diffForHumans() }}

                    </span>

                </div>

            @endforeach

        </div>

    </div>



    {{-- =====================================================
         ALERTES EN COURS
    ====================================================== --}}

    <div class="bloc">

        <div class="bloc-titre">

            <i class="fa-solid fa-bell"></i>

            Alertes en cours sur cette personne

        </div>


        <div class="bloc-corps">

            @forelse ($alertes as $alerte)

                <div class="alerte {{ $alerte->gravite->value }}">

                    <div class="alerte-tete">

                        <span class="alerte-type">

                            {{ $alerte->type->libelle() }}

                        </span>


                        <span class="alerte-date">

                            {{ $alerte->created_at->diffForHumans() }}

                        </span>

                    </div>


                    <p class="alerte-texte">

                        {{ $alerte->explication_texte }}

                    </p>

                </div>

            @empty

                <p class="vide">
                    Aucune alerte en cours.
                </p>

            @endforelse

        </div>

    </div>


</div>

@endsection
