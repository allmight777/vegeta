@extends('layouts.agent')

@section('titre', 'Historique des opérations')
@section('sous-titre', 'Dépôts et retraits que vous avez enregistrés.')

@section('contenu')
    <style>
        .history-page { font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        .history-head { display: flex; justify-content: flex-end; gap: 16px; align-items: center; margin-bottom: 20px; }
        .filter-card, .history-card { background: #fff; border: 1px solid #e7ebef; border-radius: 16px; box-shadow: 0 8px 25px rgba(44,52,61,.05); }
        .filter-card { padding: 20px; margin-bottom: 20px; }
        .filters { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
        .filter-field { display: flex; flex-direction: column; gap: 6px; }
        .filter-field label { color: #475569; font-size: .75rem; font-weight: 700; }
        .filter-field input, .filter-field select { min-width: 0; width: 100%; border: 1px solid #dbe3ec; border-radius: 9px; padding: 10px 11px; background: #fff; color: #1e293b; font: inherit; font-size: .82rem; }
        .filter-actions { display: flex; align-items: end; gap: 10px; }
        .button { display: inline-flex; justify-content: center; align-items: center; min-height: 39px; border-radius: 9px; padding: 0 14px; text-decoration: none; font-size: .8rem; font-weight: 700; cursor: pointer; }
        .button-primary { border: 0; background: #2c343d; color: #fff; }
        .button-secondary { border: 1px solid #dbe3ec; color: #475569; background: #fff; }
        .history-card { overflow: hidden; }
        .history-summary { padding: 15px 20px; color: #64748b; font-size: .82rem; border-bottom: 1px solid #e7ebef; }
        .history-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
        .history-table th { padding: 12px 16px; color: #64748b; background: #f8fafc; text-align: left; font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; }
        .history-table td { padding: 14px 16px; border-top: 1px solid #eef2f6; vertical-align: middle; }
        .client-name { display: block; font-weight: 700; }
        .account { display: block; margin-top: 3px; color: #64748b; font-size: .74rem; }
        .badge { display: inline-block; border-radius: 999px; padding: 5px 9px; font-size: .7rem; font-weight: 800; }
        .badge-depot { color: #16803a; background: #eaf8ef; }
        .badge-retrait { color: #b45309; background: #fff7e6; }
        .amount { font-weight: 800; white-space: nowrap; }
        .empty { padding: 42px 20px; text-align: center; color: #64748b; }
        .pagination { padding: 16px 20px; border-top: 1px solid #e7ebef; }
        @media (max-width: 1050px) { .filters { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 650px) { .history-head { align-items: flex-start; flex-direction: column; } .filters { grid-template-columns: 1fr; } .filter-actions { align-items: stretch; } .button { flex: 1; } .history-card { overflow-x: auto; } .history-table { min-width: 700px; } }
    </style>

    <div class="history-page">
        <div class="history-head">
            <a class="button button-primary" href="{{ route('agent.operations.creer') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>&nbsp; Nouvelle opération
            </a>
        </div>

        <form class="filter-card" method="GET" action="{{ route('agent.operations.historique') }}">
            <div class="filters">
                <div class="filter-field"><label for="date_debut">Du</label><input id="date_debut" name="date_debut" type="date" value="{{ $filtres['date_debut'] ?? '' }}"></div>
                <div class="filter-field"><label for="date_fin">Au</label><input id="date_fin" name="date_fin" type="date" value="{{ $filtres['date_fin'] ?? '' }}"></div>
                <div class="filter-field"><label for="client">Client (nom ou prénom)</label><input id="client" name="client" value="{{ $filtres['client'] ?? '' }}" placeholder="Ex. ADOU ou Marie"></div>
                <div class="filter-field"><label for="compte">N° de compte</label><input id="compte" name="compte" value="{{ $filtres['compte'] ?? '' }}" placeholder="Ex. CPT-001"></div>
                <div class="filter-field"><label for="type">Type d’opération</label><select id="type" name="type"><option value="">Tous</option><option value="depot" @selected(($filtres['type'] ?? '') === 'depot')>Dépôt</option><option value="retrait" @selected(($filtres['type'] ?? '') === 'retrait')>Retrait</option></select></div>
                <div class="filter-field"><label for="mode_paiement">Mode de paiement</label><select id="mode_paiement" name="mode_paiement"><option value="">Tous</option><option value="especes" @selected(($filtres['mode_paiement'] ?? '') === 'especes')>Espèces</option><option value="virement" @selected(($filtres['mode_paiement'] ?? '') === 'virement')>Virement</option><option value="mobile_money" @selected(($filtres['mode_paiement'] ?? '') === 'mobile_money')>Mobile money</option></select></div>
                <div class="filter-field"><label for="montant_min">Montant minimum (XOF)</label><input id="montant_min" name="montant_min" type="number" min="0" value="{{ $filtres['montant_min'] ?? '' }}"></div>
                <div class="filter-field"><label for="montant_max">Montant maximum (XOF)</label><input id="montant_max" name="montant_max" type="number" min="0" value="{{ $filtres['montant_max'] ?? '' }}"></div>
                <div class="filter-actions">
                    <button class="button button-primary" type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i>&nbsp; Filtrer</button>
                    <a class="button button-secondary" href="{{ route('agent.operations.historique') }}">Réinitialiser</a>
                </div>
            </div>
        </form>

        <section class="history-card" aria-label="Résultats de l’historique">
            <div class="history-summary">{{ $historique->total() }} opération{{ $historique->total() > 1 ? 's' : '' }} trouvée{{ $historique->total() > 1 ? 's' : '' }}</div>
            @if ($historique->isEmpty())
                <div class="empty"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i><p>Aucune opération ne correspond à ces critères.</p></div>
            @else
                <table class="history-table">
                    <thead><tr><th>Date et heure</th><th>Client / compte</th><th>Opération</th><th>Mode</th><th>Montant</th></tr></thead>
                    <tbody>
                        @foreach ($historique as $operation)
                            <tr>
                                <td>{{ $operation->effectuee_le->format('d/m/Y H:i') }}</td>
                                <td><span class="client-name">{{ $operation->compte?->client?->nomAffichage() ?: 'Client non disponible' }}</span><span class="account">{{ $operation->compte?->numero }}</span></td>
                                <td><span class="badge {{ $operation->type->value === 'depot' ? 'badge-depot' : 'badge-retrait' }}">{{ $operation->type->libelle() }}</span></td>
                                <td>{{ $operation->mode_paiement?->libelle() ?? 'Non renseigné' }}</td>
                                <td class="amount">{{ number_format((float) $operation->montant, 0, ',', ' ') }} {{ $operation->devise_code }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($historique->hasPages())<div class="pagination">{{ $historique->links() }}</div>@endif
            @endif
        </section>
    </div>
@endsection
