@extends('layouts.responsable')


@section('sous-titre', 'PPE de votre agence — déclarées à l\'adhésion ou détectées par le filtrage')

@section('contenu')

<style>

    /* =========================================================
       PAGE PPE — RESPONSABLE
       Palette : jaune signature + bleu nuit + icônes bleu foncé
    ========================================================= */

    .ppe-page {

        --yellow: #F0E535;
        --yellow-soft: rgba(240, 229, 53, 0.12);
        --yellow-light: #FFFDE7;

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --dark: #2C343D;
        --dark-soft: #3C4650;

        --accent: #2563EB;
        --accent-dark: #1D4ED8;
        --accent-soft: rgba(37, 99, 235, 0.10);

        --text: #1E293B;
        --muted: #64748B;
        --muted-light: #94A3B8;

        --border: #E8EDF2;
        --white: #FFFFFF;
        --background: #F8FAFC;

        --danger: #DC2626;
        --danger-soft: rgba(220, 38, 38, 0.08);

        --shadow-sm: 0 4px 15px rgba(44, 52, 61, 0.04);
        --shadow:    0 12px 35px rgba(44, 52, 61, 0.07);

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    /* =========================================================
       HERO
    ========================================================= */

    .ppe-hero {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        flex-wrap: wrap;

        padding: 18px 22px;

        border-radius: 20px;

        background: linear-gradient(135deg, #2C343D 0%, #3A4650 65%, #303A43 100%);

        position: relative;

        overflow: hidden;

        box-shadow: var(--shadow);
    }


    .ppe-hero::before {

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


    .ppe-hero-left {

        display: flex;

        align-items: center;

        gap: 15px;

        position: relative;

        z-index: 2;

        min-width: 0;
    }


    .ppe-hero-icon {

        width: 46px;

        height: 46px;

        flex-shrink: 0;

        border-radius: 13px;

        background: rgba(240, 229, 53, 0.16);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: #1D4ED8;   /* icône bleu foncé */

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.05rem;
    }


    .ppe-hero-text {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .ppe-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .ppe-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    .ppe-hero-compteur {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        padding: 8px 14px;

        border-radius: 999px;

        background: rgba(240, 229, 53, 0.14);

        border: 1px solid rgba(240, 229, 53, 0.30);

        color: #1D4ED8;   /* icône bleu foncé */

        font-size: 0.68rem;

        font-weight: 800;

        position: relative;

        z-index: 2;

        white-space: nowrap;
    }


    .ppe-hero-compteur i {

        font-size: 0.72rem;

        color: #1D4ED8;   /* icône bleu foncé */
    }


    /* =========================================================
       BARRE DE FILTRES
    ========================================================= */

    .ppe-filtres {

        display: flex;

        flex-wrap: wrap;

        gap: 10px;

        align-items: flex-end;

        padding: 16px 20px;

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        box-shadow: var(--shadow-sm);
    }


    .ppe-filtre {

        display: flex;

        flex-direction: column;

        gap: 6px;

        min-width: 0;
    }


    .ppe-filtre-label {

        font-size: 0.56rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted-light);

        display: flex;

        align-items: center;

        gap: 6px;
    }


    .ppe-filtre-label i {

        font-size: 0.62rem;

        color: #1D4ED8;   /* icône bleu foncé */
    }


    .ppe-filtre-input {

        height: 42px;

        padding: 0 13px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #F8FAFC;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.75rem;

        font-weight: 600;

        outline: none;

        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }


    .ppe-filtre-input:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .ppe-filtre-input:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow: 0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    /* =========================================================
       BOUTONS DE FILTRE
    ========================================================= */

    .ppe-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        height: 42px;

        padding: 0 16px;

        border-radius: 11px;

        font-family: inherit;

        font-size: 0.68rem;

        font-weight: 800;

        text-decoration: none;

        border: none;

        cursor: pointer;

        white-space: nowrap;

        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }


    .ppe-btn i {

        font-size: 0.7rem;

        color: #1D4ED8;   /* icône bleu foncé par défaut */
    }


    .ppe-btn.principal {

        background: var(--dark);

        color: #FFFFFF;

        box-shadow: 0 6px 16px rgba(44, 52, 61, 0.18);
    }


    .ppe-btn.principal i {

        color: #93C5FD;
    }


    .ppe-btn.principal:hover {

        background: var(--dark-soft);

        transform: translateY(-1px);

        box-shadow: 0 10px 22px rgba(44, 52, 61, 0.24);
    }


    .ppe-btn.secondaire {

        background: #FFFFFF;

        color: var(--dark);

        border: 1px solid var(--border);
    }


    .ppe-btn.secondaire:hover {

        background: var(--yellow-light);

        border-color: rgba(240, 229, 53, 0.45);

        transform: translateY(-1px);
    }


    .ppe-btn.danger {

        background: #FFFFFF;

        color: var(--danger);

        border: 1px solid rgba(220, 38, 38, 0.22);
    }


    .ppe-btn.danger i {

        color: #1D4ED8;   /* icône bleu foncé */
    }


    .ppe-btn.danger:hover {

        background: #FEF2F2;

        border-color: rgba(220, 38, 38, 0.45);

        transform: translateY(-1px);
    }


    .ppe-btn.excel {

        background: #FFFFFF;

        color: #166534;

        border: 1px solid rgba(48, 195, 26, 0.28);
    }


    .ppe-btn.excel i {

        color: #1D4ED8;   /* icône bleu foncé */
    }


    .ppe-btn.excel:hover {

        background: var(--green-soft);

        border-color: rgba(48, 195, 26, 0.50);

        transform: translateY(-1px);
    }


    .ppe-btn.accent {

        background: var(--accent);

        color: #FFFFFF;

        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.24);
    }


    .ppe-btn.accent i {

        color: #FFFFFF;
    }


    .ppe-btn.accent:hover {

        background: #1D4ED8;

        transform: translateY(-1px);

        box-shadow: 0 10px 22px rgba(37, 99, 235, 0.30);
    }


    /* =========================================================
       CARTE TABLEAU
    ========================================================= */

    .ppe-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        overflow: hidden;

        box-shadow: var(--shadow-sm);

        width: 100%;
    }


    .ppe-card-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        padding: 16px 22px;

        border-bottom: 1px solid var(--border);

        background: linear-gradient(135deg, #FFFDF7 0%, #FFFCE8 100%);

        position: relative;

        overflow: hidden;
    }


    .ppe-card-header::before {

        content: "";

        position: absolute;

        left: 0;

        top: 0;

        bottom: 0;

        width: 4px;

        background: linear-gradient(180deg, #F0E535 0%, #E6D91C 100%);
    }


    .ppe-card-header-left {

        display: flex;

        align-items: center;

        gap: 12px;

        min-width: 0;
    }


    .ppe-card-header-icon {

        width: 40px;

        height: 40px;

        flex-shrink: 0;

        border-radius: 11px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.35);

        color: #1D4ED8;   /* icône bleu foncé */

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.95rem;
    }


    .ppe-card-header-text {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .ppe-card-header-text strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;

        letter-spacing: -0.2px;
    }


    .ppe-card-header-text span {

        color: var(--muted);

        font-size: 0.66rem;

        font-weight: 500;
    }


    .ppe-card-compteur {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 6px 12px;

        border-radius: 999px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.35);

        color: #1D4ED8;   /* icône bleu foncé */

        font-size: 0.62rem;

        font-weight: 800;

        white-space: nowrap;
    }


    .ppe-card-compteur i {

        font-size: 0.62rem;

        color: #1D4ED8;   /* icône bleu foncé */
    }


    /* =========================================================
       TABLEAU
    ========================================================= */

    .ppe-table {

        width: 100%;

        border-collapse: collapse;

        font-size: 0.78rem;
    }


    .ppe-table thead {

        background: #FFFFFF;

        border-bottom: 1px solid var(--border);
    }


    .ppe-table th {

        padding: 13px 16px;

        text-align: left;

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted);

        white-space: nowrap;
    }


    .ppe-table td {

        padding: 14px 16px;

        border-bottom: 1px solid var(--border);

        color: var(--dark);

        vertical-align: middle;
    }


    .ppe-table tbody tr {

        transition: background 0.2s ease;
    }


    .ppe-table tbody tr:hover {

        background: var(--yellow-light);
    }


    .ppe-table tbody tr:last-child td {

        border-bottom: none;
    }


    /* =========================================================
       CELLULE CLIENT
    ========================================================= */

    .ppe-client {

        display: flex;

        align-items: center;

        gap: 11px;

        min-width: 0;
    }


    .ppe-client-avatar {

        width: 36px;

        height: 36px;

        flex-shrink: 0;

        border-radius: 10px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.35);

        color: #1D4ED8;   /* icône bleu foncé */

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.82rem;
    }


    .ppe-client-avatar.morale {

        background: var(--accent-soft);

        border-color: rgba(37, 99, 235, 0.22);

        color: #1D4ED8;
    }


    .ppe-client-nom {

        color: var(--dark);

        font-size: 0.76rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    /* =========================================================
       BADGES
    ========================================================= */

    .ppe-badge {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 11px;

        border-radius: 999px;

        font-size: 0.6rem;

        font-weight: 800;

        white-space: nowrap;

        border: 1px solid transparent;
    }


    .ppe-badge i {

        font-size: 0.6rem;

        color: #1D4ED8;   /* icône bleu foncé par défaut */
    }


    .ppe-badge.confirme {

        background: var(--green-soft);

        border-color: rgba(48, 195, 26, 0.28);

        color: #249C13;
    }


    .ppe-badge.verifier {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.35);

        color: #8A7C00;
    }


    .ppe-badge.neutre {

        background: var(--background);

        border-color: var(--border);

        color: var(--muted);
    }


    .ppe-badge.physique {

        background: var(--accent-soft);

        border-color: rgba(37, 99, 235, 0.22);

        color: #1D4ED8;
    }


    .ppe-badge.morale {

        background: rgba(168, 85, 247, 0.10);

        border-color: rgba(168, 85, 247, 0.22);

        color: #7E22CE;
    }


    /* =========================================================
       BADGE DOCUMENTS
    ========================================================= */

    .ppe-doc-badge {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 4px 10px;

        border-radius: 8px;

        background: var(--background);

        border: 1px solid var(--border);

        color: var(--muted);

        font-size: 0.62rem;

        font-weight: 800;

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    .ppe-doc-badge i {

        font-size: 0.6rem;

        color: #1D4ED8;   /* icône bleu foncé */
    }


    .ppe-doc-badge.avec {

        background: rgba(37, 99, 235, 0.08);

        border-color: rgba(37, 99, 235, 0.22);

        color: #1D4ED8;
    }


    .ppe-doc-badge.avec i {

        color: #1D4ED8;
    }


    /* =========================================================
       TEXTE MUTED
    ========================================================= */

    .ppe-muted {

        color: var(--muted);

        font-size: 0.72rem;

        font-weight: 600;
    }


    .ppe-muted-light {

        color: var(--muted-light);

        font-size: 0.68rem;

        font-weight: 600;
    }


    /* =========================================================
       BOUTON DETAIL
    ========================================================= */

    .ppe-btn-detail {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        height: 32px;

        padding: 0 12px;

        border-radius: 9px;

        background: var(--dark);

        color: #FFFFFF;

        font-family: inherit;

        font-size: 0.62rem;

        font-weight: 800;

        text-decoration: none;

        white-space: nowrap;

        transition: background 0.2s ease, transform 0.2s ease;
    }


    .ppe-btn-detail i {

        color: #93C5FD;

        font-size: 0.6rem;
    }


    .ppe-btn-detail:hover {

        background: var(--dark-soft);

        transform: translateY(-1px);
    }


    /* =========================================================
       ÉTAT VIDE
    ========================================================= */

    .ppe-empty {

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 10px;

        padding: 55px 25px;

        text-align: center;

        color: var(--muted);
    }


    .ppe-empty-icon {

        width: 58px;

        height: 58px;

        border-radius: 50%;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.35);

        color: #1D4ED8;   /* icône bleu foncé */

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.4rem;
    }


    .ppe-empty strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;
    }


    .ppe-empty span {

        font-size: 0.68rem;

        color: var(--muted-light);

        max-width: 340px;

        line-height: 1.5;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 700px) {

        .ppe-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .ppe-hero-text strong {

            font-size: 0.9rem;
        }


        .ppe-filtres {

            padding: 14px 16px;

            border-radius: 16px;
        }


        .ppe-filtre {

            flex: 1;

            min-width: 130px;
        }


        .ppe-btn {

            flex: 1;

            min-width: 130px;
        }


        .ppe-table th,
        .ppe-table td {

            padding: 10px 12px;
        }
    }

</style>


<div class="ppe-page">


    {{-- =====================================================
         HERO
    ====================================================== --}}

    <div class="ppe-hero">

        <div class="ppe-hero-left">

            <div class="ppe-hero-icon">

                <i class="fa-solid fa-landmark"></i>

            </div>


            <div class="ppe-hero-text">

                <strong>
                    Personnes politiquement exposées
                </strong>

                <span>
                    PPE de votre agence — déclarées à l'adhésion ou détectées par le filtrage.
                </span>

            </div>

        </div>


        <span class="ppe-hero-compteur">

            <i class="fa-solid fa-user-shield"></i>

            {{ $clients->count() }} PPE

        </span>

    </div>



    {{-- =====================================================
         FILTRES
    ====================================================== --}}

    <form method="GET" class="ppe-filtres">


        <div class="ppe-filtre">

            <label class="ppe-filtre-label" for="du">

                <i class="fa-solid fa-calendar"></i>

                Créées du

            </label>

            <input
                type="date"
                name="du"
                id="du"
                value="{{ $du }}"
                class="ppe-filtre-input"
            >

        </div>


        <div class="ppe-filtre">

            <label class="ppe-filtre-label" for="au">

                <i class="fa-solid fa-calendar"></i>

                au

            </label>

            <input
                type="date"
                name="au"
                id="au"
                value="{{ $au }}"
                class="ppe-filtre-input"
            >

        </div>


        <button type="submit" class="ppe-btn principal">

            <i class="fa-solid fa-filter"></i>

            Filtrer

        </button>


        <a
            class="ppe-btn danger"
            href="{{ route('responsable.ppe.exporter', ['format' => 'pdf', 'du' => $du, 'au' => $au]) }}"
        >

            <i class="fa-solid fa-file-pdf"></i>

            Exporter en PDF

        </a>


        <a
            class="ppe-btn excel"
            href="{{ route('responsable.ppe.exporter', ['format' => 'xlsx', 'du' => $du, 'au' => $au]) }}"
        >

            <i class="fa-solid fa-file-excel"></i>

            Exporter en Excel

        </a>


        <a
            class="ppe-btn accent"
            href="{{ route('responsable.clients.index') }}"
        >

            <i class="fa-solid fa-paper-plane"></i>

            Envoyer par e-mail

        </a>


    </form>



    {{-- =====================================================
         TABLEAU DES PPE
    ====================================================== --}}

    <div class="ppe-card">


        <div class="ppe-card-header">

            <div class="ppe-card-header-left">

                <div class="ppe-card-header-icon">

                    <i class="fa-solid fa-list-check"></i>

                </div>


                <div class="ppe-card-header-text">

                    <strong>
                        Liste des PPE de l'agence
                    </strong>

                    <span>
                        Filtrez par période de création puis exportez le registre.
                    </span>

                </div>

            </div>


            <span class="ppe-card-compteur">

                <i class="fa-solid fa-layer-group"></i>

                {{ $clients->count() }} client(s)

            </span>

        </div>



        @if ($clients->count())


            <table class="ppe-table">

                <thead>

                    <tr>

                        <th>Client</th>

                        <th>Type</th>

                        <th>Statut</th>

                        <th>Déclarée</th>

                        <th>Pièces</th>

                        <th>Créée le</th>

                        <th></th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($clients as $c)

                        @php

                            $estMorale = $c->type->value === 'personne_morale';

                            $statutValue = $c->statut_ppe->value ?? '';
                            $statutClass = str_contains($statutValue, 'confirme')
                                ? 'confirme'
                                : (str_contains($statutValue, 'verifier') ? 'verifier' : 'neutre');

                            $statutIcon = $statutClass === 'confirme'
                                ? 'fa-circle-check'
                                : ($statutClass === 'verifier' ? 'fa-hourglass-half' : 'fa-circle-info');

                            $docCount = $c->documents_ppe_count ?? 0;

                        @endphp


                        <tr>

                            <td data-label="Client">

                                <div class="ppe-client">

                                    <div class="ppe-client-avatar {{ $estMorale ? 'morale' : '' }}">

                                        <i class="fa-solid {{ $estMorale ? 'fa-building' : 'fa-user-shield' }}"></i>

                                    </div>


                                    <span class="ppe-client-nom">

                                        {{ $c->nomAffichage() }}

                                    </span>

                                </div>

                            </td>


                            <td data-label="Type">

                                <span class="ppe-badge {{ $estMorale ? 'morale' : 'physique' }}">

                                    <i class="fa-solid {{ $estMorale ? 'fa-building' : 'fa-user' }}"></i>

                                    {{ $estMorale ? 'Personne morale' : 'Personne physique' }}

                                </span>

                            </td>


                            <td data-label="Statut">

                                <span class="ppe-badge {{ $statutClass }}">

                                    <i class="fa-solid {{ $statutIcon }}"></i>

                                    {{ $c->statut_ppe->libelle() }}

                                </span>

                            </td>


                            <td data-label="Déclarée">

                                @if ($c->ppe_declare)

                                    <span class="ppe-badge confirme">

                                        <i class="fa-solid fa-circle-check"></i>

                                        Oui

                                    </span>

                                @else

                                    <span class="ppe-badge neutre">

                                        <i class="fa-solid fa-circle-minus"></i>

                                        Non

                                    </span>

                                @endif

                            </td>


                            <td data-label="Pièces">

                                <span class="ppe-doc-badge {{ $docCount > 0 ? 'avec' : '' }}">

                                    <i class="fa-solid fa-paperclip"></i>

                                    {{ $docCount }}

                                </span>

                            </td>


                            <td data-label="Créée le">

                                <span class="ppe-muted-light">

                                    {{ $c->created_at->format('d/m/Y') }}

                                </span>

                            </td>


                            <td>

                                <a
                                    class="ppe-btn-detail"
                                    href="{{ route('responsable.ppe.detail', $c) }}"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                    Détail

                                </a>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>


        @else


            <div class="ppe-empty">

                <div class="ppe-empty-icon">

                    <i class="fa-solid fa-user-shield"></i>

                </div>


                <strong>
                    Aucune PPE sur cette période
                </strong>


                <span>
                    Ajustez les dates de création ou vérifiez que des PPE
                    ont bien été déclarées à l'adhésion.
                </span>

            </div>

        @endif


    </div>


</div>

@endsection
