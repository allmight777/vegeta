@extends('layouts.responsable')

@section('titre', 'Tableau de bord conformité')

@push('styles')
<style>
    .tb-grid {
        display: grid;
        gap: 16px;
    }

    .tb-section {
        background: var(--cif-white);
        border: 1px solid var(--cif-border);
        border-radius: 18px;
        box-shadow: var(--cif-shadow);
        overflow: hidden;
    }

    .tb-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--cif-border);
    }

    .tb-section-head-left {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .tb-section-title {
        margin: 0;
        color: var(--cif-dark);
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: -0.2px;
    }

    .tb-section-sub {
        color: var(--cif-muted);
        font-size: 0.63rem;
        font-weight: 500;
        margin-top: 3px;
    }

    .tb-section-count {
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

    .tb-list {
        display: flex;
        flex-direction: column;
    }

    .tb-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 20px;
        border-bottom: 1px solid var(--cif-border);
        text-decoration: none;
        color: inherit;
        transition: background 0.15s ease;
    }

    .tb-row:last-child {
        border-bottom: none;
    }

    .tb-row:hover {
        background: #F8FAFC;
    }

    .tb-row-label {
        color: var(--cif-text);
        font-size: 0.77rem;
        font-weight: 600;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tb-row-meta {
        color: var(--cif-muted);
        font-size: 0.63rem;
        font-weight: 600;
        margin-top: 3px;
    }

    .tb-empty {
        padding: 26px 20px;
        text-align: center;
        color: #94A3B8;
        font-size: 0.72rem;
        font-weight: 500;
    }

    .tb-alert-block {
        padding: 15px 20px;
        border-bottom: 1px solid var(--cif-border);
    }

    .tb-alert-block:last-child {
        border-bottom: none;
    }

    .tb-alert-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .tb-alert-time {
        color: #94A3B8;
        font-size: 0.6rem;
        font-weight: 600;
    }

    .tb-alert-text {
        margin: 9px 0 0;
        color: var(--cif-text);
        font-size: 0.74rem;
        line-height: 1.5;
    }

    .tb-alert-action {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 9px;
        color: var(--cif-accent);
        font-size: 0.66rem;
        font-weight: 800;
        text-decoration: none;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        font-family: inherit;
    }

    .tb-alert-action:hover {
        text-decoration: underline;
    }

    .tb-pill {
        display: inline-flex;
        align-items: center;
        height: 22px;
        padding: 0 9px;
        border-radius: 999px;
        font-size: 0.6rem;
        font-weight: 800;
        letter-spacing: 0.2px;
        border: 1px solid transparent;
    }

    .tb-pill-amber {
        background: rgba(245, 158, 11, 0.10);
        color: #B45309;
        border-color: rgba(245, 158, 11, 0.25);
    }

    .tb-pill-red {
        background: rgba(220, 38, 38, 0.09);
        color: #B91C1C;
        border-color: rgba(220, 38, 38, 0.22);
    }

    .tb-score {
        color: #B45309;
        font-size: 0.72rem;
        font-weight: 800;
    }

    .tb-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 30px;
        padding: 0 12px;
        border-radius: 8px;
        background: var(--cif-dark);
        color: #FFFFFF;
        font-family: inherit;
        font-size: 0.63rem;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: transform 0.15s ease, background 0.15s ease;
    }

    .tb-btn:hover {
        background: #1F262E;
        transform: translateY(-1px);
    }
</style>
@endpush

@section('contenu')
    <div class="tb-grid">

        {{-- Dossiers à compléter --}}
        <section class="tb-section">
            <div class="tb-section-head">
                <div class="tb-section-head-left">
                    <h2 class="tb-section-title">Dossiers à compléter</h2>
                    <span class="tb-section-sub">KYC incomplets dans votre agence</span>
                </div>
                <span class="tb-section-count">{{ $dossiersACompleter->count() }}</span>
            </div>
            <div class="tb-list">
                @forelse ($dossiersACompleter as $client)
                    <a href="{{ route('agent.clients.completer', $client) }}" class="tb-row">
                        <span class="tb-row-label">{{ $client->nomAffichage() }}</span>
                        <span class="tb-score">{{ $client->score_completude_kyc }} %</span>
                    </a>
                @empty
                    <p class="tb-empty">Aucun dossier en attente.</p>
                @endforelse
            </div>
        </section>

        {{-- Alertes du jour --}}
        <section class="tb-section">
            <div class="tb-section-head">
                <div class="tb-section-head-left">
                    <h2 class="tb-section-title">Alertes du jour</h2>
                    <span class="tb-section-sub">Filtrage et vérifications NPI</span>
                </div>
                <span class="tb-section-count">{{ $alertesDuJour->count() }}</span>
            </div>
            <div class="tb-list">
                @forelse ($alertesDuJour as $alerte)
                    <div class="tb-alert-block">
                        <div class="tb-alert-top">
                            <x-badge-gravite :gravite="$alerte->gravite" />
                            <span class="tb-alert-time">{{ $alerte->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="tb-alert-text">{{ $alerte->explication_texte }}</p>

                        @if (in_array($alerte->type->value, ['filtrage_sanction', 'filtrage_ppe']))
                            <a href="{{ route('responsable.filtrage.index') }}" class="tb-alert-action">
                                Traiter dans Filtrage
                                <i class="fa-solid fa-arrow-right" style="font-size:0.58rem;"></i>
                            </a>
                        @elseif ($alerte->type->value === 'npi_invalide_apres_verification')
                            <form method="POST"
                                  action="{{ route('responsable.conformite.alertes-npi.traiter', $alerte) }}"
                                  style="margin:0;">
                                @csrf
                                <button type="submit" class="tb-alert-action">
                                    Marquer comme traité
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="tb-empty">Aucune alerte en attente.</p>
                @endforelse
            </div>
        </section>

        {{-- Seuils et fractionnements --}}
        <section class="tb-section">
            <div class="tb-section-head">
                <div class="tb-section-head-left">
                    <h2 class="tb-section-title">Seuils et fractionnements</h2>
                    <span class="tb-section-sub">Dépassements et opérations fractionnées</span>
                </div>
                <span class="tb-section-count">{{ $seuilsEtFractionnements->count() }}</span>
            </div>
            <div class="tb-list">
                @forelse ($seuilsEtFractionnements as $alerte)
                    @php $critique = $alerte->type->value === 'fractionnement_multi_agences'; @endphp
                    <div class="tb-alert-block">
                        <span class="tb-pill {{ $critique ? 'tb-pill-red' : 'tb-pill-amber' }}">
                            {{ $alerte->type->libelle() }}
                        </span>
                        <p class="tb-alert-text">{{ $alerte->explication_texte }}</p>
                        @if (($alerte->faits['identite_id'] ?? null) !== null)
                            <a href="{{ route('responsable.identites.afficher', $alerte->faits['identite_id']) }}"
                               class="tb-alert-action">
                                Voir la vue consolidée de la personne
                                <i class="fa-solid fa-arrow-right" style="font-size:0.58rem;"></i>
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="tb-empty">Aucun seuil approché ni fractionnement détecté.</p>
                @endforelse
            </div>
        </section>

        {{-- Déclarations CENTIF --}}
        <section class="tb-section">
            <div class="tb-section-head">
                <div class="tb-section-head-left">
                    <h2 class="tb-section-title">Déclarations CENTIF à venir</h2>
                    <span class="tb-section-sub">Périodes à générer</span>
                </div>
                <span class="tb-section-count">{{ $declarationsAVenir->count() }}</span>
            </div>
            <div class="tb-list">
                @forelse ($declarationsAVenir as $declaration)
                    <div class="tb-row" style="cursor:default;">
                        <div>
                            <span class="tb-row-label">{{ $declaration->periode }}</span>
                            <div class="tb-row-meta">
                                {{ number_format((float) $declaration->montant_cumule, 0, ',', ' ') }} XOF
                            </div>
                        </div>
                        <form method="POST"
                              action="{{ route('responsable.conformite.declarations-centif.generer', $declaration) }}"
                              style="margin:0;">
                            @csrf
                            <button type="submit" class="tb-btn">
                                <i class="fa-solid fa-file-pdf" style="font-size:0.66rem;"></i>
                                Générer le PDF
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="tb-empty">Aucune déclaration à préparer.</p>
                @endforelse
            </div>
        </section>

        {{-- NPI en attente --}}
        <section class="tb-section">
            <div class="tb-section-head">
                <div class="tb-section-head-left">
                    <h2 class="tb-section-title">NPI en attente de vérification</h2>
                    <span class="tb-section-sub">En attente de connexion au service ANIP</span>
                </div>
                <span class="tb-section-count">{{ $npiEnAttente->count() }}</span>
            </div>
            <div class="tb-list">
                @forelse ($npiEnAttente as $client)
                    <a href="{{ route('agent.clients.completer', $client) }}" class="tb-row">
                        <span class="tb-row-label">{{ $client->nomAffichage() }}</span>
                        <span class="tb-pill tb-pill-amber">En attente</span>
                    </a>
                @empty
                    <p class="tb-empty">Aucun NPI en attente de vérification.</p>
                @endforelse
            </div>
        </section>

    </div>
@endsection