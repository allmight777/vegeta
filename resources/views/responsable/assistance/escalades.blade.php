@extends('layouts.responsable')



@section('sous-titre', 'Répondez aux questions transmises par les agents à l\'assistant IA.')

@section('contenu')

<style>

    /* =========================================================
       PAGE ESCALADES — ASSISTANT IA
    ========================================================= */

    .esc-page {

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
       ALERTE SUCCÈS
    ========================================================= */

    .esc-alert {

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


    .esc-alert-icon {

        width: 30px;

        height: 30px;

        flex-shrink: 0;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 9px;

        background: rgba(48, 195, 26, 0.14);

        color: var(--green);

        font-size: 0.75rem;
    }


    /* =========================================================
       SECTION
    ========================================================= */

    .esc-section {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);
    }


    .esc-section-head {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        padding: 18px 22px;

        border-bottom: 1px solid var(--border);

        background: linear-gradient(180deg, #FFFFFF 0%, #FBFCFE 100%);
    }


    .esc-section-head-left {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .esc-section-title {

        margin: 0;

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        display: flex;

        align-items: center;

        gap: 9px;
    }


    .esc-section-title i {

        width: 28px;

        height: 28px;

        flex-shrink: 0;

        border-radius: 9px;

        background: var(--accent-soft);

        color: var(--accent);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.72rem;
    }


    .esc-section-sub {

        color: var(--muted);

        font-size: 0.62rem;

        font-weight: 600;

        padding-left: 37px;
    }


    .esc-section-count {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-width: 32px;

        height: 26px;

        padding: 0 10px;

        border-radius: 999px;

        background: var(--accent-soft);

        border: 1px solid rgba(37, 99, 235, 0.25);

        color: var(--accent-dark);

        font-size: 0.62rem;

        font-weight: 800;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    /* =========================================================
       LISTE
    ========================================================= */

    .esc-list {

        display: flex;

        flex-direction: column;
    }


    .esc-item {

        padding: 20px 22px;

        border-bottom: 1px solid var(--border);

        display: flex;

        flex-direction: column;

        gap: 12px;

        transition: background 0.2s ease;
    }


    .esc-item:last-child {

        border-bottom: none;
    }


    .esc-item:hover {

        background: linear-gradient(180deg, #FFFFFF 0%, #FBFCFE 100%);
    }


    /* =========================================================
       EN-TÊTE ITEM
    ========================================================= */

    .esc-item-top {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        flex-wrap: wrap;
    }


    .esc-item-context {

        display: flex;

        align-items: center;

        gap: 8px;

        min-width: 0;

        flex-wrap: wrap;
    }


    .esc-pill {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        height: 24px;

        padding: 0 10px;

        border-radius: 999px;

        font-size: 0.58rem;

        font-weight: 800;

        letter-spacing: 0.3px;

        border: 1px solid transparent;

        white-space: nowrap;

        text-transform: uppercase;
    }


    .esc-pill i {

        font-size: 0.6rem;
    }


    .esc-pill-blue {

        background: var(--accent-soft);

        color: var(--accent-dark);

        border-color: rgba(37, 99, 235, 0.20);
    }


    .esc-pill-blue i {

        color: var(--accent);
    }


    .esc-pill-slate {

        background: var(--background);

        color: var(--muted);

        border-color: var(--border);
    }


    .esc-pill-slate i {

        color: var(--muted-light);
    }


    .esc-context-text {

        color: var(--muted);

        font-size: 0.62rem;

        font-weight: 600;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;

        max-width: 340px;
    }


    .esc-item-time {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        color: var(--muted-light);

        font-size: 0.6rem;

        font-weight: 700;

        white-space: nowrap;

        flex-shrink: 0;
    }


    .esc-item-time i {

        font-size: 0.62rem;
    }


    /* =========================================================
       QUESTION
    ========================================================= */

    .esc-question {

        display: flex;

        gap: 12px;

        margin: 0;

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 700;

        line-height: 1.55;

        letter-spacing: -0.1px;
    }


    .esc-question::before {

        content: "\f059";

        font-family: "Font Awesome 6 Free";

        font-weight: 900;

        flex-shrink: 0;

        width: 26px;

        height: 26px;

        border-radius: 8px;

        background: var(--accent-soft);

        color: var(--accent);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.68rem;
    }


    /* =========================================================
       BLOC RÉPONSE IA
    ========================================================= */

    .esc-ia-block {

        display: flex;

        gap: 12px;

        padding: 14px 16px;

        border-radius: 12px;

        background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);

        border: 1px solid var(--border);

        position: relative;

        overflow: hidden;
    }


    .esc-ia-block::before {

        content: "";

        position: absolute;

        left: 0;

        top: 0;

        bottom: 0;

        width: 3px;

        background: linear-gradient(180deg, var(--accent) 0%, #60A5FA 100%);
    }


    .esc-ia-icon {

        width: 26px;

        height: 26px;

        flex-shrink: 0;

        border-radius: 8px;

        background: var(--accent-soft);

        color: var(--accent);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.68rem;
    }


    .esc-ia-content {

        display: flex;

        flex-direction: column;

        gap: 4px;

        min-width: 0;

        flex: 1;
    }


    .esc-ia-label {

        color: var(--muted-light);

        font-size: 0.56rem;

        font-weight: 800;

        letter-spacing: 0.6px;

        text-transform: uppercase;
    }


    .esc-ia-text {

        margin: 0;

        color: var(--text);

        font-size: 0.74rem;

        line-height: 1.6;

        font-weight: 500;
    }


    /* =========================================================
       FORMULAIRE RÉPONSE
    ========================================================= */

    .esc-form {

        display: flex;

        flex-direction: column;

        gap: 11px;

    }


    .esc-form-label {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        font-size: 0.6rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--dark);
    }


    .esc-form-label i {

        color: var(--accent);

        font-size: 0.68rem;
    }


    .esc-textarea {

        width: 100%;

        min-height: 96px;

        padding: 13px 15px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #F8FAFC;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.78rem;

        font-weight: 600;

        line-height: 1.55;

        resize: vertical;

        outline: none;

        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }


    .esc-textarea:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .esc-textarea:focus {

        border-color: var(--accent);

        background: #FFFFFF;

        box-shadow: 0 0 0 3px var(--accent-soft);
    }


    .esc-textarea::placeholder {

        color: var(--muted-light);

        font-weight: 400;
    }


    /* =========================================================
       BOUTON
    ========================================================= */

    .esc-actions {

        display: flex;

        justify-content: flex-end;

    }


    .esc-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 9px;

        height: 44px;

        padding: 0 22px;

        border-radius: 11px;

        background: var(--dark);

        color: #FFFFFF;

        font-family: inherit;

        font-size: 0.72rem;

        font-weight: 800;

        border: none;

        cursor: pointer;

        box-shadow: 0 8px 20px rgba(44, 52, 61, 0.20);

        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }


    .esc-btn i {

        color: #93C5FD;

        font-size: 0.72rem;

        transition: transform 0.2s ease;
    }


    .esc-btn:hover {

        background: var(--dark-soft);

        transform: translateY(-2px);

        box-shadow: 0 12px 26px rgba(44, 52, 61, 0.26);
    }


    .esc-btn:hover i {

        transform: translateX(3px);
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .esc-empty {

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 12px;

        padding: 55px 25px;

        text-align: center;

        color: var(--muted);
    }


    .esc-empty-icon {

        width: 62px;

        height: 62px;

        border-radius: 50%;

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: var(--green);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.5rem;
    }


    .esc-empty strong {

        color: var(--dark);

        font-size: 0.9rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .esc-empty span {

        font-size: 0.68rem;

        color: var(--muted-light);

        max-width: 340px;

        line-height: 1.55;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 700px) {

        .esc-section-head {

            padding: 14px 16px;
        }


        .esc-item {

            padding: 16px 16px;
        }


        .esc-section-sub {

            padding-left: 0;
        }


        .esc-context-text {

            max-width: 100%;

            white-space: normal;
        }


        .esc-btn {

            width: 100%;
        }


        .esc-actions {

            width: 100%;
        }
    }

</style>


<div class="esc-page">


    {{-- =====================================================
         MESSAGE SUCCÈS
    ====================================================== --}}

    @if (session('statut'))

        <div class="esc-alert">

            <span class="esc-alert-icon">

                <i class="fa-solid fa-check"></i>

            </span>

            <span>

                {{ session('statut') }}

            </span>

        </div>

    @endif



    {{-- =====================================================
         SECTION QUESTIONS ESCALADÉES
    ====================================================== --}}

    <section class="esc-section">


        <div class="esc-section-head">


            <div class="esc-section-head-left">

                <h2 class="esc-section-title">

                    <i class="fa-solid fa-comments"></i>

                    Questions en attente de réponse

                </h2>


                <span class="esc-section-sub">

                    Demandes des agents transmises à l'assistant IA

                </span>

            </div>


            <span class="esc-section-count">

                {{ $escalades->count() }}

            </span>

        </div>



        <div class="esc-list">

            @forelse ($escalades as $escalade)


                <div class="esc-item">


                    {{-- EN-TÊTE DE L'ITEM --}}

                    <div class="esc-item-top">

                        <div class="esc-item-context">


                            <span class="esc-pill esc-pill-blue">

                                <i class="fa-solid fa-user-tie"></i>

                                {{ $escalade->role_agent }}

                            </span>


                            <span class="esc-context-text">

                                {{ $escalade->contexte_ecran }}

                            </span>

                        </div>


                        <span class="esc-item-time">

                            <i class="fa-regular fa-clock"></i>

                            {{ $escalade->created_at->diffForHumans() }}

                        </span>

                    </div>



                    {{-- QUESTION --}}

                    <p class="esc-question">

                        {{ $escalade->question }}

                    </p>



                    {{-- RÉPONSE IA (si présente) --}}

                    @if ($escalade->reponse_ia)

                        <div class="esc-ia-block">

                            <div class="esc-ia-icon">

                                <i class="fa-solid fa-robot"></i>

                            </div>


                            <div class="esc-ia-content">

                                <span class="esc-ia-label">

                                    Réponse de l'assistant

                                </span>


                                <p class="esc-ia-text">

                                    {{ $escalade->reponse_ia }}

                                </p>

                            </div>

                        </div>

                    @endif



                    {{-- FORMULAIRE DE RÉPONSE --}}

                    <form
                        method="POST"
                        action="{{ route('responsable.assistance.escalades.repondre', $escalade) }}"
                        class="esc-form"
                    >

                        @csrf


                        <label
                            for="reponse-{{ $escalade->id }}"
                            class="esc-form-label"
                        >

                            <i class="fa-solid fa-pen-to-square"></i>

                            Votre réponse

                        </label>


                        <textarea
                            name="reponse_responsable"
                            id="reponse-{{ $escalade->id }}"
                            required
                            rows="3"
                            class="esc-textarea"
                            placeholder="Rédigez votre réponse officielle — elle sera ajoutée à la base de connaissances de l'assistant..."
                        ></textarea>



                        <div class="esc-actions">

                            <button type="submit" class="esc-btn">

                                <i class="fa-solid fa-paper-plane"></i>

                                Répondre et enrichir la base

                            </button>

                        </div>

                    </form>


                </div>

            @empty


                <div class="esc-empty">

                    <div class="esc-empty-icon">

                        <i class="fa-solid fa-circle-check"></i>

                    </div>


                    <strong>

                        Aucune question en attente

                    </strong>


                    <span>

                        Les questions transmises par les agents
                        apparaîtront ici dès qu'elles seront escaladées.

                    </span>

                </div>

            @endforelse

        </div>

    </section>


</div>

@endsection
