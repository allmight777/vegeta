@extends('layouts.agent')



@section('sous-titre', 'Dépôts et retraits que vous avez enregistrés.')

@section('contenu')

<style>

    /* =========================================================
       PAGE HISTORIQUE
    ========================================================= */

    .history-page {

        --yellow: #F0E535;
        --yellow-soft: rgba(240, 229, 53, 0.12);
        --yellow-light: #FFFDE7;

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --amber: #B45309;
        --amber-soft: rgba(245, 158, 11, 0.10);

        --danger: #DC2626;
        --danger-soft: rgba(220, 38, 38, 0.08);

        --dark: #2C343D;

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
       BARRE HAUT DE PAGE
    ========================================================= */

    .history-head {

        display: flex;

        align-items: center;

        justify-content: flex-end;

        gap: 12px;
    }


    .btn-new {

        display: inline-flex;

        align-items: center;

        gap: 9px;

        height: 46px;

        padding: 0 22px;

        border: none;

        border-radius: 12px;

        background: var(--yellow);

        color: var(--dark);

        font-family: inherit;

        font-size: 0.78rem;

        font-weight: 800;

        text-decoration: none;

        box-shadow: 0 8px 20px rgba(240, 229, 53, 0.30);

        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }


    .btn-new i {

        font-size: 0.78rem;
    }


    .btn-new:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow: 0 12px 26px rgba(240, 229, 53, 0.36);
    }


    /* =========================================================
       FILTRES
    ========================================================= */

    .filter-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        padding: 22px 24px;

        box-shadow: var(--shadow-sm);

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    .filter-card-head {

        display: flex;

        align-items: center;

        gap: 10px;

        padding-bottom: 14px;

        border-bottom: 1px solid var(--border);
    }


    .filter-card-head-icon {

        width: 34px;

        height: 34px;

        flex-shrink: 0;

        border-radius: 10px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.30);

        color: #A08F00;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.85rem;
    }


    .filter-card-head strong {

        color: var(--dark);

        font-size: 0.82rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .filter-card-head span {

        color: var(--muted);

        font-size: 0.62rem;

        font-weight: 500;

        margin-left: 4px;
    }


    .filters {

        display: grid;

        grid-template-columns: repeat(4, minmax(0, 1fr));

        gap: 16px 18px;
    }


    .filter-field {

        display: flex;

        flex-direction: column;

        gap: 7px;

        min-width: 0;
    }


    .filter-field label {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        color: var(--dark);

        font-size: 0.62rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;
    }


    .filter-field label i {

        color: var(--muted);

        font-size: 0.7rem;
    }


    .filter-field input,
    .filter-field select {

        width: 100%;

        min-height: 46px;

        padding: 11px 14px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #F8FAFC;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.78rem;

        font-weight: 600;

        outline: none;

        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }


    .filter-field input:hover,
    .filter-field select:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .filter-field input:focus,
    .filter-field select:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow: 0 0 0 3px var(--yellow-soft);
    }


    .filter-field input::placeholder {

        color: var(--muted-light);

        font-weight: 400;
    }


    .filter-actions {

        display: flex;

        justify-content: flex-end;

        gap: 10px;

        padding-top: 6px;

        border-top: 1px solid var(--border);
    }


    .btn-filter {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        height: 44px;

        padding: 0 20px;

        border: none;

        border-radius: 11px;

        font-family: inherit;

        font-size: 0.74rem;

        font-weight: 800;

        cursor: pointer;

        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        text-decoration: none;
    }


    .btn-filter.primary {

        background: var(--dark);

        color: #FFFFFF;

        box-shadow: 0 8px 20px rgba(44, 52, 61, 0.20);
    }


    .btn-filter.primary i {

        color: #93C5FD;

        font-size: 0.74rem;
    }


    .btn-filter.primary:hover {

        background: #3A4650;

        transform: translateY(-2px);

        box-shadow: 0 12px 26px rgba(44, 52, 61, 0.26);
    }


    .btn-filter.secondary {

        background: #FFFFFF;

        color: var(--muted);

        border: 1px solid var(--border);
    }


    .btn-filter.secondary:hover {

        background: var(--background);

        border-color: #CBD5E1;

        transform: translateY(-1px);
    }


    /* =========================================================
       CARTE TABLEAU
    ========================================================= */

    .history-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);
    }


    .history-summary {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        padding: 16px 22px;

        border-bottom: 1px solid var(--border);

        background: linear-gradient(180deg, #FFFFFF 0%, #FBFCFE 100%);
    }


    .history-summary-text {

        display: inline-flex;

        align-items: center;

        gap: 10px;

        color: var(--dark);

        font-size: 0.78rem;

        font-weight: 700;
    }


    .history-summary-text i {

        width: 30px;

        height: 30px;

        flex-shrink: 0;

        border-radius: 9px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: #A08F00;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.75rem;
    }


    .history-summary-count {

        padding: 5px 11px;

        border-radius: 999px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.30);

        color: #8A7C00;

        font-size: 0.62rem;

        font-weight: 800;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    /* =========================================================
       TABLEAU
    ========================================================= */

    .history-table {

        width: 100%;

        border-collapse: collapse;

        font-size: 0.78rem;
    }


    .history-table thead {

        background: #FFFFFF;

        border-bottom: 1px solid var(--border);
    }


    .history-table th {

        padding: 13px 16px;

        text-align: left;

        color: var(--muted);

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        white-space: nowrap;
    }


    .history-table td {

        padding: 15px 16px;

        border-bottom: 1px solid var(--border);

        color: var(--dark);

        vertical-align: middle;
    }


    .history-table tbody tr {

        transition: background 0.2s ease;
    }


    .history-table tbody tr:hover {

        background: var(--yellow-light);
    }


    .history-table tbody tr:last-child td {

        border-bottom: none;
    }


    /* Colonnes spécifiques */

    .history-date {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        color: var(--muted);

        font-size: 0.72rem;

        font-weight: 700;

        white-space: nowrap;
    }


    .history-date i {

        color: var(--muted-light);

        font-size: 0.7rem;
    }


    .client-cell {

        display: flex;

        align-items: center;

        gap: 10px;
    }


    .client-avatar {

        width: 34px;

        height: 34px;

        flex-shrink: 0;

        border-radius: 10px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: var(--dark);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.72rem;
    }


    .client-info {

        display: flex;

        flex-direction: column;

        gap: 2px;

        min-width: 0;
    }


    .client-name {

        color: var(--dark);

        font-size: 0.76rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .client-account {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        color: var(--muted-light);

        font-size: 0.6rem;

        font-weight: 600;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    .client-account i {

        font-size: 0.58rem;
    }


    /* Badges type */

    .badge {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 10px;

        border-radius: 999px;

        font-size: 0.6rem;

        font-weight: 800;

        white-space: nowrap;
        border: 1px solid transparent;
    }


    .badge i {

        font-size: 0.62rem;
    }


    .badge-depot {

        background: var(--green-soft);

        border-color: rgba(48, 195, 26, 0.20);

        color: #238E15;
    }


    .badge-retrait {

        background: var(--amber-soft);

        border-color: rgba(245, 158, 11, 0.22);

        color: var(--amber);
    }


    /* Mode de paiement */

    .mode-cell {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        color: var(--muted);

        font-size: 0.7rem;

        font-weight: 600;
    }


    .mode-cell i {

        color: var(--muted-light);

        font-size: 0.68rem;
    }


    /* Montant */

    .amount {

        color: var(--dark);

        font-size: 0.78rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        white-space: nowrap;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    .amount .devise {

        color: var(--muted-light);

        font-size: 0.62rem;

        font-weight: 700;

        margin-left: 3px;
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .empty {

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 12px;

        padding: 60px 25px;

        text-align: center;

        color: var(--muted);
    }


    .empty-icon {

        width: 62px;

        height: 62px;

        border-radius: 50%;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.30);

        color: #A08F00;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.4rem;
    }


    .empty strong {

        color: var(--dark);

        font-size: 0.88rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .empty span {

        font-size: 0.68rem;

        color: var(--muted-light);

        max-width: 340px;

        line-height: 1.55;
    }


    /* =========================================================
       PAGINATION
    ========================================================= */

    .pagination-wrapper {

        display: flex;

        justify-content: center;

        padding: 16px 22px;

        border-top: 1px solid var(--border);

        background: #FBFCFE;
    }


    .pagination-wrapper svg {

        width: 14px;

        height: 14px;
    }


    .pagination-wrapper a,
    .pagination-wrapper span[aria-current="page"] > span,
    .pagination-wrapper span[aria-disabled="true"] > span {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-width: 34px;

        height: 34px;

        padding: 0 10px;

        margin: 0 2px;

        border-radius: 10px;

        border: 1px solid var(--border);

        background: #FFFFFF;

        color: var(--muted);

        font-size: 0.68rem;

        font-weight: 700;

        text-decoration: none;

        transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }


    .pagination-wrapper a:hover {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.45);

        color: var(--dark);
    }


    .pagination-wrapper span[aria-current="page"] > span {

        background: var(--dark);

        border-color: var(--dark);

        color: #FFFFFF;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1100px) {

        .filters {

            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }


    @media (max-width: 700px) {

        .history-head {

            justify-content: stretch;
        }


        .btn-new {

            width: 100%;

            justify-content: center;
        }


        .filter-card {

            padding: 18px 16px;
        }


        .filters {

            grid-template-columns: 1fr;
        }


        .filter-actions {

            flex-direction: column-reverse;
        }


        .filter-actions .btn-filter {

            width: 100%;
        }


        .history-card {

            overflow-x: auto;
        }


        .history-table {

            min-width: 700px;
        }


        .pagination-wrapper {

            padding: 14px 16px;
        }
    }

</style>


<div class="history-page">


    {{-- =====================================================
         BOUTON NOUVELLE OPÉRATION
    ====================================================== --}}

    <div class="history-head">

        <a
            class="btn-new"
            href="{{ route('agent.operations.creer') }}"
        >

            <i class="fa-solid fa-plus"></i>

            Nouvelle opération

        </a>

    </div>



    {{-- =====================================================
         FILTRES
    ====================================================== --}}

    <form
        class="filter-card"
        method="GET"
        action="{{ route('agent.operations.historique') }}"
    >


        <div class="filter-card-head">

            <div class="filter-card-head-icon">

                <i class="fa-solid fa-filter"></i>

            </div>


            <div>

                <strong>
                    Filtres de recherche
                </strong>

                <span>
                    Affinez les résultats par date, client, type ou montant.
                </span>

            </div>

        </div>



        <div class="filters">


            {{-- DATE DÉBUT --}}

            <div class="filter-field">

                <label for="date_debut">

                    <i class="fa-solid fa-calendar-day"></i>

                    Du

                </label>

                <input
                    id="date_debut"
                    name="date_debut"
                    type="date"
                    value="{{ $filtres['date_debut'] ?? '' }}"
                >

            </div>



            {{-- DATE FIN --}}

            <div class="filter-field">

                <label for="date_fin">

                    <i class="fa-solid fa-calendar-day"></i>

                    Au

                </label>

                <input
                    id="date_fin"
                    name="date_fin"
                    type="date"
                    value="{{ $filtres['date_fin'] ?? '' }}"
                >

            </div>



            {{-- CLIENT --}}

            <div class="filter-field">

                <label for="client">

                    <i class="fa-solid fa-user"></i>

                    Client (nom ou prénom)

                </label>

                <input
                    id="client"
                    name="client"
                    value="{{ $filtres['client'] ?? '' }}"
                    placeholder="Ex. ADOU ou Marie"
                >

            </div>



            {{-- COMPTE --}}

            <div class="filter-field">

                <label for="compte">

                    <i class="fa-solid fa-wallet"></i>

                    N° de compte

                </label>

                <input
                    id="compte"
                    name="compte"
                    value="{{ $filtres['compte'] ?? '' }}"
                    placeholder="Ex. CPT-001"
                >

            </div>



            {{-- TYPE --}}

            <div class="filter-field">

                <label for="type">

                    <i class="fa-solid fa-right-left"></i>

                    Type d'opération

                </label>

                <select id="type" name="type">

                    <option value="">Tous</option>

                    <option value="depot" @selected(($filtres['type'] ?? '') === 'depot')>
                        Dépôt
                    </option>

                    <option value="retrait" @selected(($filtres['type'] ?? '') === 'retrait')>
                        Retrait
                    </option>

                </select>

            </div>



            {{-- MODE DE PAIEMENT --}}

            <div class="filter-field">

                <label for="mode_paiement">

                    <i class="fa-solid fa-money-bill-wave"></i>

                    Mode de paiement

                </label>

                <select id="mode_paiement" name="mode_paiement">

                    <option value="">Tous</option>

                    <option value="especes" @selected(($filtres['mode_paiement'] ?? '') === 'especes')>
                        Espèces
                    </option>

                    <option value="virement" @selected(($filtres['mode_paiement'] ?? '') === 'virement')>
                        Virement
                    </option>

                    <option value="mobile_money" @selected(($filtres['mode_paiement'] ?? '') === 'mobile_money')>
                        Mobile money
                    </option>

                </select>

            </div>



            {{-- MONTANT MIN --}}

            <div class="filter-field">

                <label for="montant_min">

                    <i class="fa-solid fa-coins"></i>

                    Montant minimum (XOF)

                </label>

                <input
                    id="montant_min"
                    name="montant_min"
                    type="number"
                    min="0"
                    value="{{ $filtres['montant_min'] ?? '' }}"
                >

            </div>



            {{-- MONTANT MAX --}}

            <div class="filter-field">

                <label for="montant_max">

                    <i class="fa-solid fa-coins"></i>

                    Montant maximum (XOF)

                </label>

                <input
                    id="montant_max"
                    name="montant_max"
                    type="number"
                    min="0"
                    value="{{ $filtres['montant_max'] ?? '' }}"
                >

            </div>


        </div>



        {{-- ACTIONS --}}

        <div class="filter-actions">

            <a
                class="btn-filter secondary"
                href="{{ route('agent.operations.historique') }}"
            >

                <i class="fa-solid fa-rotate-left"></i>

                Réinitialiser

            </a>


            <button
                class="btn-filter primary"
                type="submit"
            >

                <i class="fa-solid fa-filter"></i>

                Filtrer

            </button>

        </div>


    </form>



    {{-- =====================================================
         RÉSULTATS
    ====================================================== --}}

    <section class="history-card" aria-label="Résultats de l'historique">


        <div class="history-summary">

            <div class="history-summary-text">

                <i class="fa-solid fa-list-check"></i>

                Résultats de la recherche

            </div>


            <span class="history-summary-count">

                {{ $historique->total() }}
                opération{{ $historique->total() > 1 ? 's' : '' }}

            </span>

        </div>



        @if ($historique->isEmpty())


            <div class="empty">

                <div class="empty-icon">

                    <i class="fa-solid fa-clock-rotate-left"></i>

                </div>


                <strong>
                    Aucune opération trouvée
                </strong>


                <span>
                    Aucune opération ne correspond à ces critères.
                    Modifiez les filtres ou réinitialisez la recherche.
                </span>

            </div>


        @else


            <table class="history-table">

                <thead>

                    <tr>

                        <th>Date et heure</th>

                        <th>Client / compte</th>

                        <th>Opération</th>

                        <th>Mode</th>

                        <th>Montant</th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($historique as $operation)


                        <tr>


                            {{-- DATE --}}

                            <td>

                                <span class="history-date">

                                    <i class="fa-regular fa-clock"></i>

                                    {{ $operation->effectuee_le->format('d/m/Y H:i') }}

                                </span>

                            </td>



                            {{-- CLIENT + COMPTE --}}

                            <td>

                                <div class="client-cell">

                                    <div class="client-avatar">

                                        <i class="fa-solid fa-user"></i>

                                    </div>


                                    <div class="client-info">

                                        <span class="client-name">

                                            {{ $operation->compte?->client?->nomAffichage() ?: 'Client non disponible' }}

                                        </span>


                                        <span class="client-account">

                                            <i class="fa-solid fa-wallet"></i>

                                            {{ $operation->compte?->numero }}

                                        </span>

                                    </div>

                                </div>

                            </td>



                            {{-- TYPE D'OPÉRATION --}}

                            <td>

                                <span class="badge {{ $operation->type->value === 'depot' ? 'badge-depot' : 'badge-retrait' }}">

                                    <i class="fa-solid {{ $operation->type->value === 'depot' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>

                                    {{ $operation->type->libelle() }}

                                </span>

                            </td>



                            {{-- MODE DE PAIEMENT --}}

                            <td>

                                <span class="mode-cell">

                                    <i class="fa-solid fa-money-bill-wave"></i>

                                    {{ $operation->mode_paiement?->libelle() ?? 'Non renseigné' }}

                                </span>

                            </td>



                            {{-- MONTANT --}}

                            <td>

                                <span class="amount">

                                    {{ number_format((float) $operation->montant, 0, ',', ' ') }}

                                    <span class="devise">

                                        {{ $operation->devise_code }}

                                    </span>

                                </span>

                            </td>


                        </tr>

                    @endforeach

                </tbody>

            </table>



            @if ($historique->hasPages())

                <div class="pagination-wrapper">

                    {{ $historique->links() }}

                </div>

            @endif


        @endif


    </section>


</div>

@endsection
