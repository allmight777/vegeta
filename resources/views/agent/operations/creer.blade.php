@extends('layouts.agent')



@section('sous-titre', 'Enregistrez un dépôt ou un retrait sur un compte client.')

@section('contenu')

<style>

    .operation-page {

        --yellow: #F0E535;
        --yellow-soft: rgba(240, 229, 53, 0.12);
        --yellow-light: #FFFDE7;

        --dark: #2C343D;
        --dark-soft: #3A4650;

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


    /* HERO */

    .operation-hero {

        display: flex;
        align-items: center;
        gap: 15px;
        padding: 18px 22px;
        border-radius: 20px;
        background: linear-gradient(135deg, #2C343D 0%, #3A4650 65%, #303A43 100%);
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow);
    }


    .operation-hero::before {

        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        right: -80px;
        top: -130px;
        border-radius: 50%;
        background: var(--yellow);
        opacity: 0.12;
    }


    .operation-hero-icon {

        width: 46px;
        height: 46px;
        flex-shrink: 0;
        border-radius: 13px;
        background: rgba(240, 229, 53, 0.16);
        border: 1px solid rgba(240, 229, 53, 0.30);
        color: var(--yellow);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        position: relative;
        z-index: 2;
    }


    .operation-hero-text {

        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }


    .operation-hero-text strong {

        color: #FFFFFF;
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: -0.4px;
    }


    .operation-hero-text span {

        color: rgba(255, 255, 255, 0.62);
        font-size: 0.68rem;
        font-weight: 500;
    }


    /* ALERTES */

    .operation-alert {

        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 14px;
        background: #FEF2F2;
        border: 1px solid #FECACA;
        color: var(--danger);
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.5;
        box-shadow: var(--shadow-sm);
    }


    .operation-alert-icon {

        width: 32px;
        height: 32px;
        flex-shrink: 0;
        border-radius: 10px;
        background: rgba(220, 38, 38, 0.10);
        color: var(--danger);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }


    .operation-alert strong {

        display: block;
        font-weight: 800;
        font-size: 0.78rem;
    }


    /* CARTE FORMULAIRE */

    .operation-card {

        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 26px 30px;
        box-shadow: var(--shadow-sm);
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 22px;
    }


    /* GRILLE CHAMPS */

    .operation-grid {

        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px 22px;
    }


    .champ-operation {

        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }


    .champ-operation.full {

        grid-column: 1 / -1;
    }


    .champ-operation-label {

        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.64rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--dark);
    }


    .champ-operation-label i {

        color: var(--muted);
        font-size: 0.74rem;
    }


    .champ-operation-label .obligatoire {

        color: var(--danger);
        margin-left: 2px;
    }


    .champ-operation-input,
    .champ-operation select {

        width: 100%;
        min-height: 48px;
        padding: 12px 14px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #F8FAFC;
        color: var(--dark);
        font-family: inherit;
        font-size: 0.82rem;
        font-weight: 600;
        outline: none;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }


    .champ-operation-input:hover:not(:disabled),
    .champ-operation select:hover:not(:disabled) {

        border-color: #CBD5E1;
        background: #FFFFFF;
    }


    .champ-operation-input:focus:not(:disabled),
    .champ-operation select:focus:not(:disabled) {

        border-color: var(--yellow);
        background: #FFFFFF;
        box-shadow: 0 0 0 3px var(--yellow-soft);
    }


    .champ-operation-input:disabled,
    .champ-operation select:disabled {

        background: var(--background);
        color: var(--muted-light);
        cursor: not-allowed;
        opacity: 0.6;
    }


    /* PANNEAU INFOS CLIENT */

    .client-panel {

        display: none;
        flex-direction: column;
        gap: 16px;
        padding: 20px 22px;
        background: linear-gradient(180deg, #FFFFFF 0%, #FFFDF7 100%);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        animation: fadeIn 0.3s ease-out;
    }


    .client-panel.visible {

        display: flex;
    }


    @keyframes fadeIn {

        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }


    .client-panel-header {

        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border);
    }


    .client-panel-avatar {

        width: 42px;
        height: 42px;
        flex-shrink: 0;
        border-radius: 12px;
        background: var(--yellow-soft);
        color: var(--dark);
        border: 1px solid rgba(240, 229, 53, 0.30);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }


    .client-panel-title {

        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
        flex: 1;
    }


    .client-panel-title strong {

        color: var(--dark);
        font-size: 0.86rem;
        font-weight: 800;
        letter-spacing: -0.2px;
    }


    .client-panel-title span {

        color: var(--muted);
        font-size: 0.62rem;
        font-weight: 600;
    }


    .client-panel-chips {

        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }


    .client-chip {

        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 9px;
        border-radius: 999px;
        background: var(--background);
        border: 1px solid var(--border);
        color: var(--muted);
        font-size: 0.58rem;
        font-weight: 700;
        white-space: nowrap;
    }


    .client-chip i {

        font-size: 0.6rem;
        color: var(--muted-light);
    }


    .client-chip.danger {

        background: var(--danger-soft);
        border-color: rgba(220, 38, 38, 0.20);
        color: var(--danger);
    }


    .client-chip.danger i {

        color: var(--danger);
    }


    .client-chip.ok {

        background: var(--green-soft);
        border-color: rgba(48, 195, 26, 0.20);
        color: #249C13;
    }


    .client-chip.ok i {

        color: var(--green);
    }


    .client-chip.warn {

        background: var(--amber-soft);
        border-color: rgba(245, 158, 11, 0.22);
        color: var(--amber);
    }


    .client-chip.warn i {

        color: #F59E0B;
    }


    /* GRILLE INFO CLIENT */

    .client-grid {

        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px 20px;
    }


    .client-info {

        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }


    .client-info-label {

        color: var(--muted-light);
        font-size: 0.56rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }


    .client-info-value {

        color: var(--dark);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: -0.2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }


    /* BANDEAU PIÈCE */

    .piece-alerte {

        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 12px;
        font-size: 0.74rem;
        font-weight: 700;
        line-height: 1.5;
    }


    .piece-alerte.expiree {

        background: #FEF2F2;
        border: 1px solid #FECACA;
        color: var(--danger);
    }


    .piece-alerte.bientot {

        background: #FFFBEB;
        border: 1px solid #FDE68A;
        color: var(--amber);
    }


    .piece-alerte-icon {

        width: 32px;
        height: 32px;
        flex-shrink: 0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }


    .piece-alerte.expiree .piece-alerte-icon {

        background: rgba(220, 38, 38, 0.10);
    }


    .piece-alerte.bientot .piece-alerte-icon {

        background: rgba(245, 158, 11, 0.10);
    }


    /* ÉTAT VIDE — AUCUN COMPTE SÉLECTIONNÉ */

    .operation-empty {

        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        padding: 40px 25px;
        text-align: center;
        background: var(--background);
        border: 1px dashed var(--border);
        border-radius: 16px;
        color: var(--muted);
    }


    .operation-empty-icon {

        width: 58px;
        height: 58px;
        border-radius: 50%;
        background: var(--yellow-soft);
        border: 1px solid rgba(240, 229, 53, 0.28);
        color: var(--dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }


    .operation-empty strong {

        color: var(--dark);
        font-size: 0.86rem;
        font-weight: 800;
    }


    .operation-empty span {

        font-size: 0.68rem;
        color: var(--muted-light);
        max-width: 360px;
        line-height: 1.55;
    }


    /* MONTANT */

    .montant-wrapper {

        position: relative;
        display: flex;
        align-items: center;
    }


    .montant-wrapper input {

        padding-right: 78px;
    }


    .montant-devise {

        position: absolute;
        right: 14px;
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--dark);
        background: var(--yellow-soft);
        padding: 4px 9px;
        border-radius: 8px;
        border: 1px solid rgba(240, 229, 53, 0.30);
        pointer-events: none;
    }


    /* BOUTON */

    .operation-actions {

        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 6px;
        border-top: 1px solid var(--border);
    }


    .btn-primary {

        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        height: 50px;
        padding: 0 30px;
        border: none;
        border-radius: 12px;
        background: var(--yellow);
        color: var(--dark);
        font-family: inherit;
        font-size: 0.80rem;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 8px 20px rgba(240, 229, 53, 0.30);
        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }


    .btn-primary i {

        color: var(--dark);
        font-size: 0.80rem;
        transition: transform 0.2s ease;
    }


    .btn-primary:hover:not(:disabled) {

        background: #E6DC28;
        transform: translateY(-2px);
        box-shadow: 0 12px 26px rgba(240, 229, 53, 0.36);
    }


    .btn-primary:hover:not(:disabled) i {

        transform: translateX(3px);
    }


    .btn-primary:disabled {

        background: #E8EDF2;
        color: var(--muted-light);
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
        opacity: 0.85;
    }


    .btn-primary:disabled i {

        color: var(--muted-light);
    }


    /* RESPONSIVE */

    @media (max-width: 1100px) {

        .operation-grid,
        .client-grid {

            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }


    @media (max-width: 700px) {

        .operation-grid,
        .client-grid {

            grid-template-columns: 1fr;
        }


        .operation-card {

            padding: 18px 16px;
        }


        .operation-actions {

            justify-content: stretch;
        }


        .btn-primary {

            width: 100%;
        }
    }

</style>


<div
    class="operation-page"
    x-data="operationForm({{ Illuminate\Support\Js::from($urlLookupClient) }})"
>


    {{-- HERO --}}

    <div class="operation-hero">

        <div class="operation-hero-icon">

            <i class="fa-solid fa-money-bill-transfer"></i>

        </div>

        <div class="operation-hero-text">

            <strong>Nouvelle opération</strong>

            <span>Enregistrez un dépôt ou un retrait sur un compte client.</span>

        </div>

    </div>



    {{-- ERREURS SERVEUR --}}

    @if ($errors->any())

        <div class="operation-alert">

            <div class="operation-alert-icon">

                <i class="fa-solid fa-triangle-exclamation"></i>

            </div>

            <div>

                <strong>Opération refusée</strong>

                {{ $errors->first() }}

            </div>

        </div>

    @endif



    {{-- FORMULAIRE --}}

    <form
        method="POST"
        action="{{ route('agent.operations.stocker') }}"
        class="operation-card"
        @submit="if (! formulaireValide) { $event.preventDefault(); }"
    >

        @csrf


        {{-- ÉTAPE 1 : sélection du compte --}}

        <div class="operation-grid">

            <div class="champ-operation full">

                <label class="champ-operation-label" for="compte_id">

                    <i class="fa-solid fa-wallet"></i>

                    Compte client

                    <span class="obligatoire">*</span>

                </label>

                <select
                    name="compte_id"
                    id="compte_id"
                    required
                    class="champ-operation-input"
                    x-model="compteId"
                    @change="chargerClient()"
                >

                    <option value="">— Sélectionner un compte —</option>

                    @foreach ($comptes as $compte)

                        <option value="{{ $compte->id }}">

                            {{ $compte->numero }} —

                            {{ $compte->client->type->value === 'personne_morale'
                                ? $compte->client->personneMorale?->raison_sociale
                                : trim(
                                    ($compte->client->personnePhysique?->prenoms ?? '')
                                    . ' '
                                    . ($compte->client->personnePhysique?->nom ?? '')
                                ) }}

                            @if ($compte->agence)
                                ({{ $compte->agence->nom }})
                            @endif

                        </option>

                    @endforeach

                </select>

            </div>

        </div>



        {{-- ÉTAT VIDE : aucun compte sélectionné --}}

        <template x-if="! compteId">

            <div class="operation-empty">

                <div class="operation-empty-icon">

                    <i class="fa-solid fa-hand-pointer"></i>

                </div>

                <strong>

                    Sélectionnez un compte pour continuer

                </strong>

                <span>

                    Choisissez d'abord le compte du client ci-dessus.
                    Les informations du titulaire et les paramètres de
                    l'opération s'afficheront automatiquement.

                </span>

            </div>

        </template>



        {{-- ÉTAPE 2 : panneau info client --}}

        <div class="client-panel" x-show="client" x-cloak>

            <div class="client-panel-header">

                <div class="client-panel-avatar">

                    <i class="fa-solid" :class="client?.type === 'personne_morale' ? 'fa-building' : 'fa-user'"></i>

                </div>

                <div class="client-panel-title">

                    <strong x-text="client?.nom_complet || client?.raison_sociale || ''"></strong>

                    <span x-text="(client?.type === 'personne_morale' ? 'Personne morale' : 'Personne physique') + (client?.compte_numero ? ' · compte ' + client.compte_numero : '')"></span>

                </div>

                <div class="client-panel-chips">

                    <template x-if="client?.statut_conformite === 'ok'">

                        <span class="client-chip ok">

                            <i class="fa-solid fa-circle-check"></i>

                            Conformité à jour

                        </span>

                    </template>

                    <template x-if="client?.statut_conformite === 'verification'">

                        <span class="client-chip warn">

                            <i class="fa-solid fa-hourglass-half"></i>

                            Vérification en cours

                        </span>

                    </template>

                </div>

            </div>



            {{-- Bandeau pièce expirée --}}

            <template x-if="client?.alerte_piece">

                <div class="piece-alerte" :class="client.alerte_piece.niveau">

                    <div class="piece-alerte-icon">

                        <i class="fa-solid" :class="client.alerte_piece.niveau === 'expiree' ? 'fa-circle-xmark' : 'fa-triangle-exclamation'"></i>

                    </div>

                    <span x-text="client.alerte_piece.message"></span>

                </div>

            </template>



            {{-- Grille info client --}}

            <div class="client-grid">

                <template x-if="client?.type === 'personne_physique'">

                    <div class="client-info">

                        <span class="client-info-label">Nom</span>

                        <span class="client-info-value" x-text="client.nom || '—'"></span>

                    </div>

                </template>

                <template x-if="client?.type === 'personne_physique'">

                    <div class="client-info">

                        <span class="client-info-label">Prénoms</span>

                        <span class="client-info-value" x-text="client.prenoms || '—'"></span>

                    </div>

                </template>

                <template x-if="client?.type === 'personne_physique'">

                    <div class="client-info">

                        <span class="client-info-label">Date de naissance</span>

                        <span class="client-info-value" x-text="client.date_naissance || '—'"></span>

                    </div>

                </template>

                <template x-if="client?.type === 'personne_physique'">

                    <div class="client-info">

                        <span class="client-info-label">Lieu de naissance</span>

                        <span class="client-info-value" x-text="client.lieu_naissance || '—'"></span>

                    </div>

                </template>

                <template x-if="client?.type === 'personne_physique'">

                    <div class="client-info">

                        <span class="client-info-label">Nationalité</span>

                        <span class="client-info-value" x-text="client.nationalite || '—'"></span>

                    </div>

                </template>

                <template x-if="client?.type === 'personne_physique'">

                    <div class="client-info">

                        <span class="client-info-label">Père</span>

                        <span class="client-info-value" x-text="client.pere || '—'"></span>

                    </div>

                </template>

                <template x-if="client?.type === 'personne_physique'">

                    <div class="client-info">

                        <span class="client-info-label">Mère</span>

                        <span class="client-info-value" x-text="client.mere || '—'"></span>

                    </div>

                </template>

                <template x-if="client?.type === 'personne_physique'">

                    <div class="client-info">

                        <span class="client-info-label">Pièce d'identité</span>

                        <span class="client-info-value" x-text="(client.piece_type || '—') + ' · ' + (client.piece_numero_masque || '—')"></span>

                    </div>

                </template>

                <template x-if="client?.type === 'personne_physique'">

                    <div class="client-info">

                        <span class="client-info-label">Expiration pièce</span>

                        <span class="client-info-value" x-text="client.piece_expiration || '—'"></span>

                    </div>

                </template>

                <template x-if="client?.type === 'personne_morale'">

                    <div class="client-info">

                        <span class="client-info-label">Raison sociale</span>

                        <span class="client-info-value" x-text="client.raison_sociale || '—'"></span>

                    </div>

                </template>

                <template x-if="client?.type === 'personne_morale'">

                    <div class="client-info">

                        <span class="client-info-label">RCCM</span>

                        <span class="client-info-value" x-text="client.numero_rccm || '—'"></span>

                    </div>

                </template>

                <template x-if="client?.type === 'personne_morale'">

                    <div class="client-info">

                        <span class="client-info-label">IFU</span>

                        <span class="client-info-value" x-text="client.numero_ifu || '—'"></span>

                    </div>

                </template>

            </div>

        </div>



        {{-- ÉTAPE 3 : paramètres de l'opération — désactivés tant qu'aucun compte --}}

        <div class="operation-grid">

            <div class="champ-operation">

                <label class="champ-operation-label" for="type">

                    <i class="fa-solid fa-right-left"></i>

                    Type d'opération

                    <span class="obligatoire">*</span>

                </label>

                <select
                    name="type"
                    id="type"
                    class="champ-operation-input"
                    required
                    :disabled="! compteId"
                >

                    <option value="depot">Dépôt</option>

                    <option value="retrait">Retrait</option>

                </select>

            </div>

            <div class="champ-operation">

                <label class="champ-operation-label" for="mode_paiement">

                    <i class="fa-solid fa-money-bill-wave"></i>

                    Mode de paiement

                    <span class="obligatoire">*</span>

                </label>

                <select
                    name="mode_paiement"
                    id="mode_paiement"
                    class="champ-operation-input"
                    required
                    :disabled="! compteId"
                >

                    <option value="especes">Espèces</option>

                    <option value="virement">Virement</option>

                    <option value="mobile_money">Mobile money</option>

                </select>

            </div>

            <div class="champ-operation">

                <label class="champ-operation-label" for="montant">

                    <i class="fa-solid fa-coins"></i>

                    Montant

                    <span class="obligatoire">*</span>

                </label>

                <div class="montant-wrapper">

                    <input
                        type="number"
                        step="0.01"
                        name="montant"
                        id="montant"
                        required
                        min="1"
                        placeholder="0.00"
                        class="champ-operation-input"
                        :disabled="! compteId"
                    >

                    <span class="montant-devise">FCFA</span>

                </div>

            </div>

        </div>



        {{-- ACTIONS --}}

        <div class="operation-actions">

            <button
                type="submit"
                class="btn-primary"
                :disabled="! formulaireValide"
            >

                <span x-text="texteBouton"></span>

                <i class="fa-solid" :class="iconeBouton"></i>

            </button>

        </div>

    </form>

</div>



@push('scripts')

<script>

function operationForm(urlLookup) {

    return {

        compteId: '',

        client: null,

        enChargement: false,


        get pieceExpiree() {

            return this.client?.alerte_piece?.niveau === 'expiree';

        },


        get formulaireValide() {

            return Boolean(this.compteId) && Boolean(this.client) && ! this.pieceExpiree;

        },


        get texteBouton() {

            if (! this.compteId) {

                return 'Sélectionnez d\'abord un compte';

            }

            if (this.pieceExpiree) {

                return 'Pièce expirée — opération bloquée';

            }

            if (! this.client) {

                return 'Chargement des informations…';

            }

            return 'Enregistrer l\'opération';

        },


        get iconeBouton() {

            if (! this.compteId) {

                return 'fa-hand-pointer';

            }

            if (this.pieceExpiree) {

                return 'fa-ban';

            }

            return 'fa-arrow-right';

        },


        async chargerClient() {

            this.client = null;

            if (! this.compteId) {

                return;

            }

            this.enChargement = true;

            try {

                const reponse = await fetch(urlLookup + '?compte_id=' + encodeURIComponent(this.compteId), {

                    headers: {

                        'Accept': 'application/json',

                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',

                    },

                });

                if (! reponse.ok) {

                    const erreur = await reponse.json().catch(() => ({}));

                    throw new Error(erreur.erreur || 'Erreur de chargement.');

                }

                this.client = await reponse.json();

            } catch (e) {

                console.error(e);

                this.client = null;

            } finally {

                this.enChargement = false;

            }

        },

    };

}

</script>

@endpush

@endsection
