@extends('layouts.responsable')

@section('sous-titre', 'Chaque décision est mémorisée : une correspondance tranchée ne revient plus pour cette personne.')

@section('contenu')

<style>

    /* =========================================================
       PAGE FILTRAGE RESPONSABLE
       Palette : bleu nuit (#2C343D) + accent bleu royal (#2563EB)
    ========================================================= */

    .filtrage-page {

        --dark: #2C343D;
        --dark-soft: #3C4650;

        --accent: #2563EB;
        --accent-dark: #1D4ED8;
        --accent-soft: rgba(37, 99, 235, 0.10);
        --accent-light: #EFF6FF;

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --amber: #B45309;
        --amber-soft: rgba(245, 158, 11, 0.10);

        --danger: #DC2626;
        --danger-soft: rgba(220, 38, 38, 0.08);

        --violet: #7C3AED;
        --violet-soft: rgba(124, 58, 237, 0.08);

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

        gap: 18px;
    }


    /* =========================================================
       STATS (3 cartes)
    ========================================================= */

    .filtrage-stats {

        display: grid;

        grid-template-columns: repeat(3, minmax(0, 1fr));

        gap: 14px;
    }


    .stat-card {

        display: flex;

        align-items: center;

        gap: 14px;

        padding: 18px 20px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        box-shadow: var(--shadow-sm);

        position: relative;

        overflow: hidden;

        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }


    .stat-card:hover {

        border-color: rgba(37, 99, 235, 0.30);

        box-shadow: 0 10px 28px rgba(37, 99, 235, 0.06);

        transform: translateY(-1px);
    }


    .stat-card::after {

        content: "";

        position: absolute;

        left: 0;

        top: 0;

        bottom: 0;

        width: 4px;

        background: linear-gradient(180deg, var(--accent) 0%, #60A5FA 100%);

        border-radius: 18px 0 0 18px;

        opacity: 0.85;
    }


    .stat-icon {

        width: 44px;

        height: 44px;

        flex-shrink: 0;

        border-radius: 12px;

        background: var(--accent-soft);

        color: var(--accent);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1rem;
    }


    .stat-icon.warn {

        background: var(--amber-soft);

        color: var(--amber);
    }


    .stat-icon.green {

        background: var(--green-soft);

        color: var(--green);
    }


    .stat-value {

        color: var(--dark);

        font-size: 1.6rem;

        font-weight: 900;

        letter-spacing: -0.6px;

        line-height: 1;

        margin-bottom: 5px;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    .stat-label {

        color: var(--muted);

        font-size: 0.65rem;

        font-weight: 700;

        letter-spacing: 0.2px;

        line-height: 1.4;
    }


    /* =========================================================
       LISTE
    ========================================================= */

    .filtrage-list {

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    /* =========================================================
       CARTE CORRESPONDANCE — BORDURE ROUGE CLIGNOTANTE
    ========================================================= */

    .match-card {

        position: relative;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        transition: box-shadow 0.2s ease;

        /* Animation clignotante rouge : signale un cas grave à examiner */
        animation: match-alerte-clignotante 1.8s ease-in-out infinite;
    }


    @keyframes match-alerte-clignotante {

        0%,
        100% {

            border-color: rgba(220, 38, 38, 0.55);

            box-shadow:
                0 0 0 0 rgba(220, 38, 38, 0.20),
                var(--shadow-sm);
        }

        50% {

            border-color: rgba(220, 38, 38, 1);

            box-shadow:
                0 0 0 6px rgba(220, 38, 38, 0.10),
                0 8px 24px rgba(220, 38, 38, 0.15);
        }
    }


    .match-card:hover {

        animation-play-state: paused;

        box-shadow: 0 14px 34px rgba(220, 38, 38, 0.15);
    }


    /* Barre latérale rouge pour renforcer le signal */
    .match-card::before {

        content: "";

        position: absolute;

        left: 0;

        top: 0;

        bottom: 0;

        width: 4px;

        background: linear-gradient(180deg, #EF4444 0%, #DC2626 100%);

        z-index: 2;
    }


    /* =========================================================
       EN-TÊTE DE LA CARTE
    ========================================================= */

    .match-head {

        display: flex;

        align-items: flex-start;

        justify-content: space-between;

        gap: 20px;

        padding: 20px 22px;

        border-bottom: 1px solid var(--border);

        background: linear-gradient(180deg, #FFFFFF 0%, #FBFCFE 100%);

        flex-wrap: wrap;
    }


    .match-identity {

        display: flex;

        align-items: flex-start;

        gap: 14px;

        min-width: 0;

        flex: 1;
    }


    .match-avatar {

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

        font-size: 1.05rem;
    }


    .match-identity-text {

        min-width: 0;

        flex: 1;

        display: flex;

        flex-direction: column;

        gap: 6px;
    }


    .match-name {

        color: var(--dark);

        font-size: 0.98rem;

        font-weight: 800;

        letter-spacing: -0.3px;

        line-height: 1.25;
    }


    .match-meta {

        display: flex;

        flex-wrap: wrap;

        gap: 6px;
    }


    .meta-chip {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 10px;

        border-radius: 999px;

        background: var(--background);

        border: 1px solid var(--border);

        color: var(--muted);

        font-size: 0.6rem;

        font-weight: 700;

        white-space: nowrap;
    }


    .meta-chip i {

        font-size: 0.62rem;

        color: var(--muted-light);
    }


    .meta-chip.source {

        background: var(--accent-soft);

        border-color: rgba(37, 99, 235, 0.22);

        color: var(--accent-dark);
    }


    .meta-chip.source i {

        color: var(--accent);
    }


    /* =========================================================
       BOUTON « EXPLIQUER » + PANNEAU REPLIABLE
    ========================================================= */

    .expliquer-toggle {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 6px 12px;

        border-radius: 999px;

        background: var(--violet-soft);

        border: 1px solid rgba(124, 58, 237, 0.22);

        color: var(--violet);

        font-family: inherit;

        font-size: 0.62rem;

        font-weight: 800;

        letter-spacing: 0.2px;

        cursor: pointer;

        transition: background 0.2s ease, transform 0.2s ease;

        align-self: flex-start;
    }


    .expliquer-toggle i {

        font-size: 0.62rem;
    }


    .expliquer-toggle:hover {

        background: rgba(124, 58, 237, 0.14);

        transform: translateY(-1px);
    }


    .expliquer-toggle .chevron {

        transition: transform 0.25s ease;
    }


    .expliquer-toggle.ouvert .chevron {

        transform: rotate(180deg);
    }


    .expliquer-panneau {

        margin-top: 6px;

        padding: 14px 16px;

        border-radius: 14px;

        background: linear-gradient(135deg, #F5F3FF 0%, #FAF5FF 100%);

        border: 1px solid rgba(124, 58, 237, 0.22);

        display: flex;

        flex-direction: column;

        gap: 10px;

        animation: expliquer-apparition 0.25s ease;
    }


    @keyframes expliquer-apparition {

        from {

            opacity: 0;

            transform: translateY(-4px);
        }

        to {

            opacity: 1;

            transform: translateY(0);
        }
    }


    .expliquer-texte {

        color: var(--text);

        font-size: 0.74rem;

        line-height: 1.6;

        font-weight: 500;
    }


    .expliquer-texte strong {

        color: var(--violet);

        font-weight: 800;
    }


    .expliquer-source {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding-top: 10px;

        border-top: 1px dashed rgba(124, 58, 237, 0.22);

        color: var(--muted);

        font-size: 0.58rem;

        font-weight: 700;

        font-style: italic;
    }


    .expliquer-source i {

        color: var(--violet);

        font-size: 0.6rem;
    }


    /* =========================================================
       CAS SIMILAIRES
    ========================================================= */

    .cas-similaires {

        display: flex;

        align-items: flex-start;

        gap: 8px;

        padding: 9px 12px;

        margin-top: 4px;

        border-radius: 10px;

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.22);

        color: #1B7A0F;

        font-size: 0.68rem;

        font-weight: 500;

        line-height: 1.5;
    }


    .cas-similaires i {

        color: var(--green);

        font-size: 0.72rem;

        margin-top: 1px;

        flex-shrink: 0;
    }


    /* =========================================================
       CONTEXTE SIGNATAIRE
    ========================================================= */

    .signataire-contexte {

        display: flex;

        align-items: center;

        gap: 12px;

        padding: 12px 14px;

        margin-top: 4px;

        border-radius: 12px;

        background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);

        border: 1px solid var(--border);

        position: relative;

        overflow: hidden;
    }


    .signataire-contexte::before {

        content: "";

        position: absolute;

        left: 0;

        top: 0;

        bottom: 0;

        width: 3px;

        background: linear-gradient(180deg, var(--accent) 0%, #60A5FA 100%);
    }


    .signataire-contexte-icon {

        width: 34px;

        height: 34px;

        flex-shrink: 0;

        border-radius: 10px;

        background: var(--accent-soft);

        color: var(--accent);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.82rem;
    }


    .signataire-contexte-body {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;

        flex: 1;
    }


    .signataire-contexte-label {

        color: var(--muted-light);

        font-size: 0.56rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;
    }


    .signataire-contexte-value {

        color: var(--dark);

        font-size: 0.76rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .signataire-contexte-role {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 4px 10px;

        border-radius: 999px;

        background: var(--accent-soft);

        border: 1px solid rgba(37, 99, 235, 0.22);

        color: var(--accent-dark);

        font-size: 0.58rem;

        font-weight: 800;

        white-space: nowrap;

        align-self: flex-end;
    }


    .signataire-contexte-role i {

        font-size: 0.6rem;

        color: var(--accent);
    }


    /* =========================================================
       BLOC SCORE
    ========================================================= */

    .score-block {

        flex-shrink: 0;

        min-width: 180px;

        text-align: right;

        display: flex;

        flex-direction: column;

        align-items: flex-end;

        gap: 10px;
    }


    .score-value {

        color: var(--dark);

        font-size: 2rem;

        font-weight: 900;

        letter-spacing: -1px;

        line-height: 1;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    .score-value.high {

        color: var(--danger);
    }


    .score-label {

        color: var(--muted-light);

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        margin-top: 5px;
    }


    .score-bar {

        width: 100%;

        height: 8px;

        background: var(--background);

        border: 1px solid var(--border);

        border-radius: 999px;

        overflow: hidden;
    }


    .score-bar span {

        display: block;

        height: 100%;

        border-radius: 999px;

        background: linear-gradient(90deg, var(--accent) 0%, #60A5FA 100%);

        transition: width 0.6s ease;
    }


    .score-bar span.high {

        background: linear-gradient(90deg, var(--danger) 0%, #F87171 100%);
    }


    /* =========================================================
       BOUTON EXPORTER LE RAPPORT PDF
    ========================================================= */

    .btn-exporter {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        height: 38px;

        padding: 0 16px;

        border: none;

        border-radius: 10px;

        background: var(--dark);

        color: #FFFFFF;

        font-family: inherit;

        font-size: 0.65rem;

        font-weight: 800;

        text-decoration: none;

        cursor: pointer;

        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;

        white-space: nowrap;

        margin-top: 4px;
    }


    .btn-exporter i {

        color: #93C5FD;

        font-size: 0.68rem;
    }


    .btn-exporter:hover {

        background: var(--dark-soft);

        transform: translateY(-1px);

        box-shadow: 0 8px 20px rgba(44, 52, 61, 0.20);
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .filtrage-vide {

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 12px;

        padding: 60px 25px;

        text-align: center;

        background: #FFFFFF;

        border: 1px dashed var(--border);

        border-radius: 20px;

        color: var(--muted);
    }


    .filtrage-vide i {

        width: 68px;

        height: 68px;

        border-radius: 50%;

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: var(--green);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.6rem;
    }


    .filtrage-vide p {

        color: var(--dark);

        font-size: 0.95rem;

        font-weight: 800;

        margin: 0;

        letter-spacing: -0.2px;
    }


    .filtrage-vide small {

        font-size: 0.72rem;

        color: var(--muted-light);

        max-width: 380px;

        line-height: 1.6;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 900px) {

        .filtrage-stats {

            grid-template-columns: 1fr;
        }


        .match-head {

            flex-direction: column;

            gap: 16px;
        }


        .score-block {

            text-align: left;

            align-items: flex-start;

            min-width: 0;

            width: 100%;
        }
    }


    @media (max-width: 560px) {

        .match-head {

            padding: 16px 18px;
        }


        .match-avatar {

            width: 40px;

            height: 40px;

            border-radius: 11px;

            font-size: 0.9rem;
        }


        .match-name {

            font-size: 0.86rem;
        }


        .score-value {

            font-size: 1.5rem;
        }


        .signataire-contexte {

            flex-wrap: wrap;
        }


        .signataire-contexte-role {

            align-self: flex-start;
        }
    }

</style>


<div class="filtrage-page">


    {{-- =====================================================
         STATISTIQUES
    ====================================================== --}}

    <div class="filtrage-stats">


        <div class="stat-card">

            <div class="stat-icon warn">

                <i class="fa-solid fa-magnifying-glass"></i>

            </div>

            <div>

                <div class="stat-value">

                    {{ $resultats->count() }}

                </div>

                <div class="stat-label">

                    À examiner maintenant

                </div>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon green">

                <i class="fa-solid fa-filter-circle-xmark"></i>

            </div>

            <div>

                <div class="stat-value">

                    {{ $alertesEvitees }}

                </div>

                <div class="stat-label">

                    Alertes évitées par vos décisions

                </div>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-brain"></i>

            </div>

            <div>

                <div class="stat-value">

                    {{ $decisionsConnues }}

                </div>

                <div class="stat-label">

                    Cas mémorisés

                </div>

            </div>

        </div>


    </div>



    {{-- =====================================================
         LISTE DES CORRESPONDANCES
    ====================================================== --}}

    @if ($resultats->count())


        <div class="filtrage-list">

            @foreach ($resultats as $resultat)

                @php
                    $cible = $resultat->filtrable;

                    $estSignataire = $cible instanceof \App\Models\Signataire;

                    $nomCible = $estSignataire
                        ? $cible->nom
                        : $cible->nomAffichage();

                    $pourcentage = (int) round($resultat->score_similarite * 100);
                    $eleve = $pourcentage >= 85;

                    $personneMoraleParente = $estSignataire ? $cible->personneMorale : null;
                    $roleSignataire = $estSignataire && $cible->role ? $cible->role->libelle() : null;

                    $similaires = $casSimilaires[$resultat->id] ?? ['total' => 0, 'parMotif' => []];

                    $explication = app(\App\Services\Explication\GenerateurExplication::class)
                        ->pourFiltrage($cible, $resultat->entreeListe, (float) $resultat->score_similarite);
                @endphp


                <div class="match-card" x-data="{ expliquerOuvert: false }">


                    {{-- EN-TÊTE --}}

                    <div class="match-head">


                        <div class="match-identity">

                            <div class="match-avatar">

                                <i class="fa-solid {{ $estSignataire ? 'fa-pen-nib' : 'fa-user' }}"></i>

                            </div>


                            <div class="match-identity-text">


                                {{-- NOM --}}

                                <div class="match-name">

                                    {{ $nomCible }}

                                </div>



                                {{-- CHIPS MÉTA --}}

                                <div class="match-meta">


                                    <span class="meta-chip source">

                                        <i class="fa-solid fa-list-check"></i>

                                        {{ $resultat->entreeListe->source->libelle() }}

                                    </span>


                                    <span class="meta-chip">

                                        <i class="fa-solid fa-arrow-right-arrow-left"></i>

                                        {{ $resultat->entreeListe->nom }}

                                    </span>


                                    @if ($resultat->entreeListe->categorie)

                                        <span class="meta-chip">

                                            <i class="fa-solid fa-tag"></i>

                                            {{ $resultat->entreeListe->categorie }}

                                        </span>

                                    @endif


                                    @if ($cible instanceof \App\Models\Client && $cible->identite_id)

                                        <span class="meta-chip">

                                            <i class="fa-solid fa-link"></i>

                                            Décision valable pour tous ses comptes

                                        </span>

                                    @endif


                                </div>



                                {{-- BOUTON EXPLIQUER + PANNEAU REPLIABLE --}}

                                <button
                                    type="button"
                                    class="expliquer-toggle"
                                    :class="{ 'ouvert': expliquerOuvert }"
                                    @click="expliquerOuvert = !expliquerOuvert"
                                    :aria-expanded="expliquerOuvert"
                                >

                                    <i class="fa-solid fa-circle-question"></i>

                                    <span x-text="expliquerOuvert ? 'Masquer l\'explication' : 'Expliquer cette correspondance'">
                                        Expliquer cette correspondance
                                    </span>

                                    <i class="fa-solid fa-chevron-down chevron"></i>

                                </button>


                                <div
                                    class="expliquer-panneau"
                                    x-show="expliquerOuvert"
                                    x-cloak
                                    x-transition.opacity.duration.250ms
                                >

                                    <div class="expliquer-texte">

                                        {!! $explication !!}

                                    </div>


                                    <div class="expliquer-source">

                                        <i class="fa-solid fa-microchip"></i>

                                        Explication locale — texte généré par des gabarits fixes, sans IA en ligne.

                                    </div>

                                </div>



                                {{-- CAS SIMILAIRES --}}

                                @if ($similaires['total'] > 0)

                                    <div class="cas-similaires">

                                        <i class="fa-solid fa-clock-rotate-left"></i>

                                        <span>
                                            <strong>{{ $similaires['total'] }}</strong>
                                            cas similaire{{ $similaires['total'] > 1 ? 's' : '' }} déjà tranché{{ $similaires['total'] > 1 ? 's' : '' }} dans votre réseau
                                            — motif dominant :
                                            <strong>{{ $similaires['parMotif'][0]['motif']->libelle() }}</strong>
                                            ({{ $similaires['parMotif'][0]['nombre'] }})
                                        </span>

                                    </div>

                                @else

                                    <div class="cas-similaires" style="background: #F8FAFC; border-color: #E7EBEF; color: #64748B;">

                                        <i class="fa-solid fa-clock-rotate-left" style="color: #94A3B8;"></i>

                                        <span>Aucun cas similaire tranché pour l'instant dans votre réseau.</span>

                                    </div>

                                @endif



                                {{-- CONTEXTE SIGNATAIRE --}}

                                @if ($estSignataire && $personneMoraleParente)

                                    <div class="signataire-contexte">

                                        <div class="signataire-contexte-icon">

                                            <i class="fa-solid fa-building"></i>

                                        </div>


                                        <div class="signataire-contexte-body">

                                            <span class="signataire-contexte-label">

                                                Signataire de la personne morale

                                            </span>

                                            <span class="signataire-contexte-value">

                                                {{ $personneMoraleParente->raison_sociale }}

                                            </span>

                                        </div>


                                        @if ($roleSignataire)

                                            <span class="signataire-contexte-role">

                                                <i class="fa-solid fa-briefcase"></i>

                                                {{ $roleSignataire }}

                                            </span>

                                        @endif

                                    </div>

                                @endif


                            </div>

                        </div>



                        {{-- SCORE + EXPORT PDF --}}

                        <div class="score-block">

                            <div>

                                <div class="score-value {{ $eleve ? 'high' : '' }}">

                                    {{ $pourcentage }} %

                                </div>

                                <div class="score-label">

                                    similarité du nom

                                </div>

                            </div>


                            <div class="score-bar">

                                <span
                                    class="{{ $eleve ? 'high' : '' }}"
                                    style="width: {{ $pourcentage }}%"
                                ></span>

                            </div>


                            <a
                                href="{{ route('responsable.filtrage.exporter-pdf', $resultat) }}"
                                class="btn-exporter"
                                title="Exporter le rapport PDF de cette correspondance"
                            >

                                <i class="fa-solid fa-file-pdf"></i>

                                Exporter le rapport

                            </a>

                        </div>


                    </div>


                </div>

            @endforeach

        </div>


    @else


        <div class="filtrage-vide">

            <i class="fa-solid fa-circle-check"></i>


            <p>

                Aucune correspondance en attente

            </p>


            <small>

                {{ $alertesEvitees }}
                alerte{{ $alertesEvitees > 1 ? 's' : '' }}
                déjà évitée{{ $alertesEvitees > 1 ? 's' : '' }}
                grâce aux décisions enregistrées.

            </small>

        </div>

    @endif


</div>

@endsection
