@extends('layouts.agent')

@section('titre', 'Rapports journaliers')
@section('sous-titre', 'Générez, téléchargez et partagez les rapports de votre agence.')

@section('contenu')
<style>
    [x-cloak] { display: none; }

    .reports {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--cif-text);
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .report-hero {
        background: linear-gradient(135deg, #2C343D 0%, #3A4650 65%, #303A43 100%);
        border-radius: 20px;
        padding: 22px 26px;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--cif-shadow);
    }

    .report-hero::before {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        right: -80px;
        top: -130px;
        border-radius: 50%;
        background: var(--cif-yellow);
        opacity: 0.10;
    }

    .report-hero-text { position: relative; z-index: 2; display: flex; align-items: center; gap: 15px; }

    .report-hero-icon {
        width: 46px;
        height: 46px;
        flex-shrink: 0;
        border-radius: 13px;
        background: rgba(240, 229, 53, 0.16);
        border: 1px solid rgba(240, 229, 53, 0.28);
        color: var(--cif-yellow);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .report-hero h1 { margin: 0; font-size: 1.15rem; font-weight: 800; letter-spacing: -0.3px; }
    .report-hero p { color: rgba(255, 255, 255, 0.65); margin: 4px 0 0; font-size: 0.78rem; }

    .report-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 13px; }

    .metric {
        background: #fff;
        border: 1px solid var(--cif-border);
        border-radius: 16px;
        box-shadow: var(--cif-shadow);
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .metric-icon {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        background: var(--cif-yellow-soft);
        color: var(--cif-dark);
    }

    .metric-icon.green { background: var(--cif-green-soft); color: #187137; }

    .metric span { display: block; color: var(--cif-muted); font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
    .metric strong { display: block; margin-top: 3px; font-size: 1.05rem; font-weight: 800; color: var(--cif-dark); }

    .panel {
        background: #fff;
        border: 1px solid var(--cif-border);
        border-radius: 18px;
        box-shadow: var(--cif-shadow);
        padding: 20px 22px 24px;
    }

    .panel h2 {
        font-size: 0.85rem;
        font-weight: 800;
        margin: 0 0 15px;
        color: var(--cif-dark);
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .panel h2 i { color: var(--cif-yellow); font-size: 0.8rem; }

    .period-form { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; align-items: end; }

    .field { display: flex; flex-direction: column; gap: 6px; }
    .field label { font-size: 0.68rem; font-weight: 800; color: var(--cif-muted); text-transform: uppercase; letter-spacing: 0.3px; }

    .field input {
        border: 1px solid var(--cif-border);
        border-radius: 10px;
        background: var(--cif-bg);
        padding: 11px 12px;
        font: inherit;
        font-size: 0.8rem;
        transition: border-color 0.2s ease, background 0.2s ease;
    }

    .field input:focus { outline: none; border-color: var(--cif-yellow); background: #fff; box-shadow: 0 0 0 3px var(--cif-yellow-soft); }

    .button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 0;
        border-radius: 10px;
        padding: 11px 16px;
        text-decoration: none;
        cursor: pointer;
        font: inherit;
        font-size: 0.78rem;
        font-weight: 800;
        text-align: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    }

    .button:hover { transform: translateY(-1px); }

    .primary { background: var(--cif-dark); color: #fff; box-shadow: 0 6px 16px rgba(44, 52, 61, 0.18); }
    .secondary { background: var(--cif-yellow); color: var(--cif-dark); box-shadow: 0 6px 16px rgba(240, 229, 53, 0.28); }
    .outline { background: #fff; color: var(--cif-text); border: 1px solid var(--cif-border); }

    .generer-form { margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--cif-border); }

    .chart-wrap { height: 230px; }

    .report-card { display: grid; grid-template-columns: 1.3fr 1fr; gap: 18px; }

    .content-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
    .content-list li { font-size: 0.78rem; color: var(--cif-text); display: flex; align-items: center; gap: 9px; }
    .content-list li i { color: var(--cif-green); font-size: 0.72rem; }

    .help { font-size: 0.72rem; color: var(--cif-muted); line-height: 1.5; margin: 12px 0 0; }

    .history-table { width: 100%; border-collapse: collapse; font-size: 0.79rem; }
    .history-table th, .history-table td { padding: 12px 10px; border-bottom: 1px solid var(--cif-border); text-align: left; }
    .history-table th { color: var(--cif-muted); font-size: 0.66rem; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 800; }

    .badge-statut {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 800;
    }

    .badge-statut.disponible { background: var(--cif-yellow-soft); color: #7a6f0a; }
    .badge-statut.envoye { background: var(--cif-green-soft); color: #187137; }

    .actions { display: flex; gap: 7px; flex-wrap: wrap; }

    .share-panel {
        margin-top: 10px;
        padding: 16px;
        border-radius: 14px;
        background: var(--cif-bg);
        border: 1px solid var(--cif-border);
    }

    .share-form { display: grid; grid-template-columns: 2fr 1.6fr 1.6fr auto; gap: 10px; align-items: end; }

    .share-header { display: flex; align-items: center; gap: 8px; font-size: 0.76rem; font-weight: 800; color: var(--cif-dark); margin-bottom: 12px; }
    .share-header i { color: #b45309; }

    .alert-statut {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 12px;
        background: var(--cif-green-soft);
        color: #187137;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .empty-state { text-align: center; padding: 30px 10px; color: var(--cif-muted); font-size: 0.8rem; }
    .empty-state i { font-size: 1.6rem; color: var(--cif-border); display: block; margin-bottom: 8px; }

    @media (max-width: 900px) {
        .report-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .period-form, .report-card { grid-template-columns: 1fr; }
        .share-form { grid-template-columns: 1fr; }
    }

    @media (max-width: 520px) {
        .report-grid { grid-template-columns: 1fr; }
        .report-hero { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="reports">

    @if (session('statut'))
        <div class="alert-statut"><i class="fa-solid fa-circle-check"></i> {{ session('statut') }}</div>
    @endif

    <div class="report-hero">
        <div class="report-hero-text">
            <div class="report-hero-icon"><i class="fa-solid fa-file-invoice"></i></div>
            <div>
                <h1>Rapports d'activité sécurisés</h1>
                <p>Format quotidien inspiré des bordereaux FÉCECAM : opérations, comptes réactivés et ouvertures.</p>
            </div>
        </div>
        <a class="button secondary" href="#generation"><i class="fa-solid fa-plus"></i> Générer un rapport</a>
    </div>

    <div class="report-grid">
        <div class="metric">
            <div class="metric-icon"><i class="fa-solid fa-arrow-right-arrow-left"></i></div>
            <div><span>Opérations</span><strong>{{ $donnees['statistiques']['operations'] }}</strong></div>
        </div>
        <div class="metric">
            <div class="metric-icon green"><i class="fa-solid fa-arrow-down"></i></div>
            <div><span>Dépôts</span><strong>{{ number_format((float) $donnees['statistiques']['depots'], 0, ',', ' ') }} XOF</strong></div>
        </div>
        <div class="metric">
            <div class="metric-icon"><i class="fa-solid fa-arrow-up"></i></div>
            <div><span>Retraits</span><strong>{{ number_format((float) $donnees['statistiques']['retraits'], 0, ',', ' ') }} XOF</strong></div>
        </div>
        <div class="metric">
            <div class="metric-icon green"><i class="fa-solid fa-user-plus"></i></div>
            <div><span>Nouveaux clients</span><strong>{{ $donnees['statistiques']['clients'] }}</strong></div>
        </div>
    </div>

    <section id="generation" class="panel">
        <h2><i class="fa-solid fa-calendar-days"></i> 1. Choisir la période</h2>

        <form class="period-form" method="GET">
            <div class="field">
                <label for="date_debut">Date de début</label>
                <input id="date_debut" type="date" name="date_debut" value="{{ $debut->toDateString() }}">
            </div>
            <div class="field">
                <label for="date_fin">Date de fin</label>
                <input id="date_fin" type="date" name="date_fin" value="{{ $fin->toDateString() }}">
            </div>
            <button class="button outline" type="submit"><i class="fa-solid fa-filter"></i> Appliquer le filtre</button>
        </form>

        <form class="generer-form" method="POST" action="{{ route('agent.rapports.generer') }}">
            @csrf
            <input type="hidden" name="date_debut" value="{{ $debut->toDateString() }}">
            <input type="hidden" name="date_fin" value="{{ $fin->toDateString() }}">
            <button class="button primary" type="submit"><i class="fa-solid fa-file-pdf"></i> Générer le rapport de cette période</button>
        </form>
    </section>

    <section class="report-card">
        <div class="panel">
            <h2><i class="fa-solid fa-chart-pie"></i> Activité de la période</h2>
            <div class="chart-wrap"><canvas id="operationsChart"></canvas></div>
        </div>
        <div class="panel">
            <h2><i class="fa-solid fa-list-check"></i> Contenu du PDF</h2>
            <ul class="content-list">
                <li><i class="fa-solid fa-check"></i> Opérations inhabituelles (seuil ≥ {{ number_format($donnees['seuilInhabituel'], 0, ',', ' ') }} XOF)</li>
                <li><i class="fa-solid fa-check"></i> Dépôts par compte et plafond quotidien</li>
                <li><i class="fa-solid fa-check"></i> Comptes dormants réactivés</li>
                <li><i class="fa-solid fa-check"></i> Ouvertures personnes physiques et morales</li>
            </ul>
            <p class="help">Les données sont limitées à votre agence et restent stockées dans l'espace privé de l'application.</p>
        </div>
    </section>

    <section class="panel" style="overflow-x: auto;" x-data="{ ouvert: null }">
        <h2><i class="fa-solid fa-clock-rotate-left"></i> 2. Rapports générés</h2>

        @if ($rapports->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                Aucun rapport généré pour le moment — choisissez une période puis cliquez "Générer".
            </div>
        @else
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Période</th>
                        <th>Créé le</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rapports as $rapport)
                        <tr>
                            <td>{{ $rapport->date_debut->format('d/m/Y') }} — {{ $rapport->date_fin->format('d/m/Y') }}</td>
                            <td>{{ $rapport->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($rapport->envoye_le)
                                    <span class="badge-statut envoye"><i class="fa-solid fa-paper-plane"></i> Envoyé de façon sécurisée</span>
                                @else
                                    <span class="badge-statut disponible"><i class="fa-solid fa-circle"></i> Disponible</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="button outline" href="{{ route('agent.rapports.telecharger', $rapport) }}"><i class="fa-solid fa-download"></i> Télécharger</a>
                                    <button class="button primary" type="button" @click="ouvert = ouvert === '{{ $rapport->id }}' ? null : '{{ $rapport->id }}'">
                                        <i class="fa-solid fa-share-nodes"></i> Envoyer
                                    </button>
                                </div>

                                <div class="share-panel" x-show="ouvert === '{{ $rapport->id }}'" x-cloak>
                                    <div class="share-header"><i class="fa-solid fa-shield-halved"></i> Partage sécurisé par code d'accès</div>

                                    <form class="share-form" method="POST" action="{{ route('agent.rapports.envoyer', $rapport) }}">
                                        @csrf
                                        <div class="field">
                                            <label>E-mail du destinataire</label>
                                            <input name="email" type="email" required autocomplete="email">
                                        </div>
                                        <div class="field">
                                            <label>Code d'accès fort</label>
                                            <input name="code_acces" type="password" required autocomplete="new-password" placeholder="12 caractères minimum">
                                        </div>
                                        <div class="field">
                                            <label>Confirmer le code</label>
                                            <input name="code_acces_confirmation" type="password" required autocomplete="new-password">
                                        </div>
                                        <button class="button primary" type="submit"><i class="fa-solid fa-lock"></i> Envoyer</button>
                                    </form>

                                    <p class="help">Le destinataire reçoit un lien valable 7 jours. Il doit saisir ce code pour télécharger le PDF. Ne demandez jamais le mot de passe de sa messagerie.</p>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

</div>

<script>
    new Chart(document.getElementById('operationsChart'), {
        type: 'doughnut',
        data: {
            labels: ['Dépôts', 'Retraits'],
            datasets: [{
                data: [
                    {{ (float) $donnees['statistiques']['depots'] }},
                    {{ (float) $donnees['statistiques']['retraits'] }}
                ],
                backgroundColor: ['#30C31A', '#F0E535'],
                borderWidth: 0,
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
        },
    });
</script>
@endsection
