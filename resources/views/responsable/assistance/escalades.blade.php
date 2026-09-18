@extends('layouts.responsable')

@section('titre', 'Questions escaladées — Assistant IA')

@push('styles')
<style>
    .esc-grid {
        display: grid;
        gap: 16px;
    }

    .esc-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 14px;
        border-radius: 12px;
        background: var(--cif-green-soft);
        border: 1px solid rgba(48, 195, 26, 0.22);
        color: #15803D;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .esc-alert-icon {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
        background: rgba(48, 195, 26, 0.16);
        color: #15803D;
        font-size: 0.68rem;
    }

    .esc-section {
        background: var(--cif-white);
        border: 1px solid var(--cif-border);
        border-radius: 18px;
        box-shadow: var(--cif-shadow);
        overflow: hidden;
    }

    .esc-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--cif-border);
    }

    .esc-section-head-left {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .esc-section-title {
        margin: 0;
        color: var(--cif-dark);
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: -0.2px;
    }

    .esc-section-sub {
        color: var(--cif-muted);
        font-size: 0.63rem;
        font-weight: 500;
        margin-top: 3px;
    }

    .esc-section-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 24px;
        padding: 0 9px;
        border-radius: 999px;
        background: var(--cif-accent-soft);
        color: var(--cif-accent);
        font-size: 0.63rem;
        font-weight: 800;
    }

    .esc-list {
        display: flex;
        flex-direction: column;
    }

    .esc-item {
        padding: 16px 20px;
        border-bottom: 1px solid var(--cif-border);
    }

    .esc-item:last-child {
        border-bottom: none;
    }

    .esc-item-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .esc-item-context {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .esc-pill {
        display: inline-flex;
        align-items: center;
        height: 20px;
        padding: 0 8px;
        border-radius: 999px;
        font-size: 0.58rem;
        font-weight: 800;
        letter-spacing: 0.2px;
        border: 1px solid transparent;
        white-space: nowrap;
        text-transform: uppercase;
    }

    .esc-pill-blue {
        background: var(--cif-accent-soft);
        color: var(--cif-accent);
        border-color: rgba(37, 99, 235, 0.20);
    }

    .esc-pill-slate {
        background: #F1F5F9;
        color: #475569;
        border-color: #E2E8F0;
    }

    .esc-context-text {
        color: var(--cif-muted);
        font-size: 0.63rem;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .esc-item-time {
        color: #94A3B8;
        font-size: 0.6rem;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .esc-question {
        margin: 10px 0 0;
        color: var(--cif-dark);
        font-size: 0.82rem;
        font-weight: 700;
        line-height: 1.45;
    }

    .esc-ia-block {
        margin-top: 10px;
        padding: 10px 12px;
        border-radius: 10px;
        background: #F8FAFC;
        border: 1px solid var(--cif-border);
    }

    .esc-ia-label {
        display: block;
        color: var(--cif-muted);
        font-size: 0.58rem;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .esc-ia-text {
        margin: 0;
        color: var(--cif-text);
        font-size: 0.72rem;
        line-height: 1.5;
    }

    .esc-form {
        margin-top: 12px;
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .esc-textarea {
        width: 100%;
        min-height: 82px;
        padding: 10px 12px;
        border: 1px solid var(--cif-border);
        border-radius: 10px;
        background: var(--cif-white);
        color: var(--cif-text);
        font-family: inherit;
        font-size: 0.74rem;
        line-height: 1.5;
        resize: vertical;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .esc-textarea:focus {
        outline: none;
        border-color: var(--cif-accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .esc-textarea::placeholder {
        color: #94A3B8;
    }

    .esc-btn {
        align-self: flex-start;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        height: 34px;
        padding: 0 14px;
        border-radius: 9px;
        background: var(--cif-dark);
        color: #FFFFFF;
        font-family: inherit;
        font-size: 0.66rem;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: transform 0.15s ease, background 0.15s ease;
    }

    .esc-btn:hover {
        background: #1F262E;
        transform: translateY(-1px);
    }

    .esc-btn i {
        font-size: 0.68rem;
    }

    .esc-empty {
        padding: 30px 20px;
        text-align: center;
        color: #94A3B8;
        font-size: 0.72rem;
        font-weight: 500;
    }
</style>
@endpush

@section('contenu')
    <div class="esc-grid">

        @if (session('statut'))
            <div class="esc-alert">
                <span class="esc-alert-icon">
                    <i class="fa-solid fa-check"></i>
                </span>
                <span>{{ session('statut') }}</span>
            </div>
        @endif

        <section class="esc-section">
            <div class="esc-section-head">
                <div class="esc-section-head-left">
                    <h2 class="esc-section-title">Questions en attente de réponse</h2>
                    <span class="esc-section-sub">
                        Demandes des agents transmises à l'assistant IA
                    </span>
                </div>
                <span class="esc-section-count">{{ $escalades->count() }}</span>
            </div>

            <div class="esc-list">
                @forelse ($escalades as $escalade)
                    <div class="esc-item">

                        <div class="esc-item-top">
                            <div class="esc-item-context">
                                <span class="esc-pill esc-pill-blue">
                                    {{ $escalade->role_agent }}
                                </span>
                                <span class="esc-context-text">
                                    {{ $escalade->contexte_ecran }}
                                </span>
                            </div>
                            <span class="esc-item-time">
                                {{ $escalade->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="esc-question">
                            {{ $escalade->question }}
                        </p>

                        @if ($escalade->reponse_ia)
                            <div class="esc-ia-block">
                                <span class="esc-ia-label">
                                    Réponse de l'assistant
                                </span>
                                <p class="esc-ia-text">
                                    {{ $escalade->reponse_ia }}
                                </p>
                            </div>
                        @endif

                        <form method="POST"
                              action="{{ route('responsable.assistance.escalades.repondre', $escalade) }}"
                              class="esc-form">
                            @csrf
                            <textarea
                                name="reponse_responsable"
                                required
                                rows="3"
                                class="esc-textarea"
                                placeholder="Votre réponse..."
                            ></textarea>
                            <button type="submit" class="esc-btn">
                                <i class="fa-solid fa-paper-plane"></i>
                                Répondre et ajouter à la base de connaissances
                            </button>
                        </form>

                    </div>
                @empty
                    <p class="esc-empty">Aucune question en attente.</p>
                @endforelse
            </div>
        </section>

    </div>
@endsection