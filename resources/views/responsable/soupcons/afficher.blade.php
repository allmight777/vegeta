@extends('layouts.responsable')


@section('sous-titre', "Fiche d'analyse de soupçon transmise par le contrôleur permanent — lecture seule, puis votre validation.")

@section('contenu')

@php
    $traite = $dossier->statut === \App\Enums\StatutDossierSoupcon::Traitee;
    $coches = $dossier->indicateurs ?? [];
@endphp

<style>

    /* =========================================================
       FICHE DOSSIER SOUPÇON — MÊME LANGAGE VISUEL QUE LE DASHBOARD
       --dark   : #2C343D (bleu nuit, base identité)
       --accent : #2563EB (bleu royal, accent)
    ========================================================= */

    .fr-detail {

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

        --shadow-sm: 0 4px 15px rgba(44, 52, 61, 0.04);
        --shadow:    0 12px 35px rgba(44, 52, 61, 0.07);
        --shadow-lg: 0 20px 55px rgba(44, 52, 61, 0.10);

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        max-width: 100%;

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    /* =========================================================
       BOUTON RÉSUMÉ ASSISTÉ
    ========================================================= */

    .fr-outils {

        display: flex;

        flex-direction: column;

        gap: 10px;

        width: 100%;
    }


    .fr-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 7px;

        height: 38px;

        padding: 0 16px;

        border-radius: 10px;

        font-family: inherit;

        font-size: 0.68rem;

        font-weight: 800;

        text-decoration: none;

        border: none;

        cursor: pointer;

        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;

        white-space: nowrap;

        align-self: flex-start;
    }


    .fr-btn i {

        font-size: 0.68rem;
    }


    .fr-btn.principal {

        background: var(--dark);

        color: #FFFFFF;
    }


    .fr-btn.principal i {

        color: #93C5FD;
    }


    .fr-btn.principal:hover {

        background: var(--dark-soft);

        transform: translateY(-1px);
    }


    .fr-btn.accent {

        background: var(--accent);

        color: #FFFFFF;
    }


    .fr-btn.accent i {

        color: #FFFFFF;
    }


    .fr-btn.accent:hover {

        background: var(--accent-dark);

        transform: translateY(-1px);
    }


    .fr-btn.secondaire {

        background: #FFFFFF;

        color: var(--dark);

        border: 1px solid var(--border);
    }


    .fr-btn.secondaire i {

        color: var(--accent);
    }


    .fr-btn.secondaire:hover {

        background: var(--accent-light);

        border-color: rgba(37, 99, 235, 0.45);

        transform: translateY(-1px);
    }


    .fr-btn:disabled {

        opacity: 0.55;

        cursor: not-allowed;

        transform: none;
    }


    /* =========================================================
       BLOC RÉSUMÉ ASSISTÉ
    ========================================================= */

    .fr-aide {

        padding: 14px 16px;

        border-radius: 14px;

        background: var(--accent-light);

        border: 1px solid rgba(37, 99, 235, 0.25);

        font-size: 0.74rem;

        line-height: 1.6;

        color: var(--text);

        font-weight: 500;

        width: 100%;
    }


    .fr-aide small {

        display: block;

        margin-top: 8px;

        color: var(--muted);

        font-style: italic;

        font-size: 0.6rem;

        font-weight: 600;
    }


    /* =========================================================
       SECTION (carte)
    ========================================================= */

    .fr-section {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        padding: 20px 22px;

        box-shadow: var(--shadow-sm);

        display: flex;

        flex-direction: column;

        gap: 14px;

        width: 100%;
    }


    .fr-section.validation {

        border-color: rgba(37, 99, 235, 0.45);

        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.08);
    }


    .fr-section h2 {

        margin: 0;

        padding-bottom: 12px;

        border-bottom: 1px solid var(--border);

        font-size: 0.85rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        color: var(--dark);

        display: flex;

        align-items: center;

        gap: 10px;
    }


    .fr-num {

        width: 26px;

        height: 26px;

        border-radius: 8px;

        background: var(--accent);

        color: #FFFFFF;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        font-size: 0.72rem;

        font-weight: 800;

        flex-shrink: 0;
    }


    .fr-num.valider {

        background: var(--green);
    }


    /* =========================================================
       GRILLE D'INFORMATIONS — s'étire sur toute la largeur
    ========================================================= */

    .fr-grille {

        display: grid;

        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));

        gap: 16px;

        width: 100%;
    }


    .fr-lib {

        display: block;

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted-light);

        margin-bottom: 5px;
    }


    .fr-val {

        font-size: 0.78rem;

        font-weight: 700;

        color: var(--dark);

        white-space: pre-line;

        line-height: 1.55;

        letter-spacing: -0.2px;
    }


    /* =========================================================
       INDICATEURS DE SOUPÇON (cases) — pleine largeur
    ========================================================= */

    .fr-cases {

        display: grid;

        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));

        gap: 8px;

        width: 100%;
    }


    .fr-case {

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 10px 14px;

        border: 1px solid var(--border);

        border-radius: 12px;

        font-size: 0.72rem;

        font-weight: 600;

        color: var(--muted-light);

        background: #FFFFFF;

        transition: border-color 0.18s ease, background 0.18s ease;
    }


    .fr-case i {

        font-size: 0.75rem;
    }


    .fr-case.coche {

        color: var(--dark);

        font-weight: 800;

        border-color: rgba(37, 99, 235, 0.45);

        background: var(--accent-light);
    }


    .fr-case.coche i {

        color: var(--accent);
    }


    /* =========================================================
       OPTIONS (radios avis technique)
    ========================================================= */

    .fr-options {

        display: flex;

        flex-wrap: wrap;

        gap: 8px 10px;

        width: 100%;
    }


    .fr-option {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        padding: 8px 14px;

        border: 1px solid var(--border);

        border-radius: 999px;

        background: #FFFFFF;

        font-size: 0.7rem;

        font-weight: 700;

        color: var(--muted);

        cursor: pointer;

        transition: border-color 0.18s ease, background 0.18s ease, color 0.18s ease;
    }


    .fr-option:hover {

        border-color: rgba(37, 99, 235, 0.45);

        color: var(--dark);
    }


    .fr-option i {

        font-size: 0.72rem;
        color: #CBD5E1;
    }


    .fr-option.actif {

        border-color: rgba(37, 99, 235, 0.45);

        background: var(--accent-light);

        color: var(--accent-dark);
    }


    .fr-option.actif i {

        color: var(--accent);
    }


    .fr-option input {

        accent-color: var(--accent);

        margin: 0;
    }


    /* =========================================================
       SIGNATURE (bloc bas de section)
    ========================================================= */

    .fr-signature {

        display: flex;

        flex-wrap: wrap;

        gap: 22px;

        margin-top: 4px;

        padding-top: 14px;

        border-top: 1px dashed var(--border);

        font-size: 0.72rem;

        font-weight: 700;

        color: var(--dark);

        width: 100%;
    }


    .fr-signature b {

        display: block;

        color: var(--muted-light);

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        margin-bottom: 3px;
    }


    /* =========================================================
       ERREUR
    ========================================================= */

    .fr-erreur {

        color: var(--danger);

        font-size: 0.68rem;

        font-weight: 700;

        margin-top: 6px;
    }


    /* =========================================================
       TABLEAU OPÉRATIONS
    ========================================================= */

    .fr-ops {

        width: 100%;

        border-collapse: collapse;

        font-size: 0.72rem;

        margin-top: 8px;
    }


    .fr-ops th,
    .fr-ops td {

        text-align: left;

        padding: 8px 10px;

        border-bottom: 1px solid var(--border);
    }


    .fr-ops th {

        color: var(--muted-light);

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;
    }


    /* =========================================================
       BANDEAU CONFIRMATION
    ========================================================= */

    .fr-confirmation {

        padding: 14px 16px;

        border-radius: 14px;

        background: var(--amber-soft);

        border: 1px solid rgba(245, 158, 11, 0.25);

        font-size: 0.74rem;

        font-weight: 700;

        color: var(--amber);

        display: flex;

        flex-direction: column;

        gap: 10px;

        width: 100%;
    }


    .fr-confirmation-actions {

        display: flex;

        gap: 10px;

        flex-wrap: wrap;
    }


    /* =========================================================
       RETOUR
    ========================================================= */

    .fr-retour {

        display: flex;

        width: 100%;
    }


    /* =========================================================
       FORMULAIRE — pleine largeur
    ========================================================= */

    .fr-detail form {

        width: 100%;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 640px) {

        .fr-section {

            padding: 16px 16px;

            border-radius: 17px;
        }


        .fr-signature {

            flex-direction: column;

            gap: 12px;
        }


        .fr-grille {

            grid-template-columns: 1fr;
        }


        .fr-cases {

            grid-template-columns: 1fr;
        }
    }

</style>


<div class="fr-detail" x-data="{ resume: null, chargement: false, confirmer: false,
    async resumer() {
        this.chargement = true; this.resume = null;
        try {
            const r = await fetch(@js(route('responsable.soupcons.resumer', $dossier->id)), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            });
            this.resume = r.ok
                ? await r.json()
                : { texte: 'Le résumé n\'est pas disponible pour le moment. Lisez la fiche ci-dessous.', source: 'indisponible' };
        } catch (e) {
            this.resume = { texte: 'Le résumé n\'est pas disponible pour le moment. Lisez la fiche ci-dessous.', source: 'indisponible' };
        }
        this.chargement = false;
    } }">


    {{-- =====================================================
         OUTIL RÉSUMÉ ASSISTÉ
    ====================================================== --}}

    <div class="fr-outils">

        <div>

            <button
                type="button"
                class="fr-btn secondaire"
                @click="resumer()"
                :disabled="chargement"
            >

                <i class="fa-solid fa-wand-magic-sparkles"></i>

                <span x-text="chargement ? 'Résumé en cours…' : 'Résumer ce dossier'">
                    Résumer ce dossier
                </span>

            </button>

        </div>


        <div class="fr-aide" x-show="resume" x-cloak role="status">

            <div x-text="resume?.texte"></div>

            <small x-text="resume?.source === 'analyse_assistee'
                ? 'Résumé assisté'
                : (resume?.source === 'controle_local'
                    ? 'Résumé simple — extraction des champs structurés de la fiche'
                    : '')"></small>

        </div>

    </div>



    {{-- =====================================================
         1. INFORMATIONS CLIENT
    ====================================================== --}}

    <section class="fr-section">

        <h2>

            <span class="fr-num">1</span>

            Informations sur le client

        </h2>


        <div class="fr-grille">

            <div>

                <span class="fr-lib">Nom et prénom / raison sociale</span>

                <div class="fr-val">{{ $prefill['nom'] }}</div>

            </div>


            <div>

                <span class="fr-lib">Numéro de compte</span>

                <div class="fr-val">{{ $prefill['numero_compte'] ?? '—' }}</div>

            </div>


            <div>

                <span class="fr-lib">Date d'ouverture</span>

                <div class="fr-val">{{ $prefill['date_ouverture'] ?? '—' }}</div>

            </div>


            <div>

                <span class="fr-lib">Type de client</span>

                <div class="fr-val">{{ $dossier->type_client->libelle() }}</div>

            </div>


            <div>

                <span class="fr-lib">Niveau de risque attribué</span>

                <div class="fr-val">{{ $dossier->niveau_risque->libelle() }}</div>

            </div>

        </div>

    </section>



    {{-- =====================================================
         2. DESCRIPTION DE L'OPÉRATION
    ====================================================== --}}

    <section class="fr-section">

        <h2>

            <span class="fr-num">2</span>

            Description de l'opération suspecte

        </h2>


        <div class="fr-grille">

            <div>

                <span class="fr-lib">Date(s) de l'opération</span>

                <div class="fr-val">
                    {{ implode(', ', $dossier->dates_operations ?? []) ?: '—' }}
                </div>

            </div>


            <div>

                <span class="fr-lib">Montant(s) concerné(s)</span>

                <div class="fr-val">
                    {{ collect($dossier->montants_concernes ?? [])->map(fn ($m) => number_format($m, 0, ',', ' ').' XOF')->implode(', ') ?: '—' }}
                </div>

            </div>


            <div>

                <span class="fr-lib">Canal utilisé</span>

                <div class="fr-val">{{ $dossier->canal?->libelle() ?? '—' }}</div>

            </div>

        </div>


        <div>

            <span class="fr-lib">Résumé des faits</span>

            <div class="fr-val">{{ $dossier->resume_faits ?: '—' }}</div>

        </div>

    </section>



    {{-- =====================================================
         3. INDICATEURS DE SOUPÇON
    ====================================================== --}}

    <section class="fr-section">

        <h2>

            <span class="fr-num">3</span>

            Analyse du caractère suspect — indicateurs de soupçon

        </h2>


        <div class="fr-cases">

            @foreach (\App\Enums\IndicateurSoupcon::cases() as $indicateur)

                @php $coche = in_array($indicateur->value, $coches, true); @endphp

                <div class="fr-case {{ $coche ? 'coche' : '' }}">

                    <i class="fa-solid {{ $coche ? 'fa-square-check' : 'fa-square' }}"></i>

                    {{ $indicateur->libelle() }}

                    @if ($indicateur === \App\Enums\IndicateurSoupcon::Autres
                        && $dossier->indicateur_autre_texte
                        && $coche)

                        — {{ $dossier->indicateur_autre_texte }}

                    @endif

                </div>

            @endforeach

        </div>

    </section>



    {{-- =====================================================
         4. ANALYSE DU CONTRÔLEUR
    ====================================================== --}}

    <section class="fr-section">

        <h2>

            <span class="fr-num">4</span>

            Décision et suites à donner — Contrôleur permanent

        </h2>


        <div>

            <span class="fr-lib">Résultats des enquêtes / investigations et analyse</span>

            <div class="fr-val">{{ $dossier->analyse_controleur ?: '—' }}</div>

        </div>


        <div>

            <span class="fr-lib">Avis technique</span>

            <div class="fr-options">

                @foreach ($avis as $a)

                    @php $actif = $dossier->avis_technique_controleur === $a; @endphp

                    <span class="fr-option {{ $actif ? 'actif' : '' }}">

                        <i class="fa-solid {{ $actif ? 'fa-circle-dot' : 'fa-circle' }}"></i>

                        {{ $a->libelle() }}

                    </span>

                @endforeach

            </div>

        </div>


        <div class="fr-signature">

            <div>

                <b>Nom (Contrôleur permanent)</b>

                {{ $dossier->controleur?->nom }}

            </div>


            <div>

                <b>Signature</b>

                Apposée à la transmission

            </div>


            <div>

                <b>Date</b>

                {{ $dossier->transmis_le?->format('d/m/Y H:i') }}

            </div>

        </div>

    </section>



    {{-- =====================================================
         5. VALIDATION RESPONSABLE
    ====================================================== --}}

    <section class="fr-section validation">

        <h2>

            <span class="fr-num valider">✓</span>

            Validation — Responsable d'agence

        </h2>


        @if ($traite)

            <div>

                <span class="fr-lib">Avis technique retenu</span>

                <div class="fr-options">

                    @foreach ($avis as $a)

                        @php $actif = $dossier->avis_technique_responsable === $a; @endphp

                        <span class="fr-option {{ $actif ? 'actif' : '' }}">

                            <i class="fa-solid {{ $actif ? 'fa-circle-dot' : 'fa-circle' }}"></i>

                            {{ $a->libelle() }}

                        </span>

                    @endforeach

                </div>

            </div>


            <div class="fr-signature">

                <div>

                    <b>Nom (Responsable d'agence)</b>

                    {{ $dossier->responsable?->nom }}

                </div>


                <div>

                    <b>Signature</b>

                    Apposée à la validation

                </div>


                <div>

                    <b>Date</b>

                    {{ $dossier->decide_le?->format('d/m/Y H:i') }}

                </div>

            </div>

        @else

            <form
                method="POST"
                action="{{ route('responsable.soupcons.decider', $dossier->id) }}"
                style="display:flex; flex-direction:column; gap:14px; width:100%;"
            >

                @csrf


                <div>

                    <span class="fr-lib">Avis technique</span>

                    <div class="fr-options">

                        @foreach ($avis as $a)

                            <label class="fr-option">

                                <input
                                    type="radio"
                                    name="avis_technique_responsable"
                                    value="{{ $a->value }}"
                                    @checked(old('avis_technique_responsable') === $a->value)
                                >

                                {{ $a->libelle() }}

                            </label>

                        @endforeach

                    </div>

                    @error('avis_technique_responsable')

                        <div class="fr-erreur">{{ $message }}</div>

                    @enderror

                </div>


                <div class="fr-signature">

                    <div>

                        <b>Nom (Responsable d'agence)</b>

                        {{ $agent->nom }}

                    </div>


                    <div>

                        <b>Signature</b>

                        Apposée automatiquement

                    </div>


                    <div>

                        <b>Date</b>

                        À la validation

                    </div>

                </div>


                <div>

                    <button
                        type="button"
                        class="fr-btn accent"
                        @click="confirmer = true"
                        x-show="!confirmer"
                    >

                        <i class="fa-solid fa-gavel"></i>

                        Valider ma décision

                    </button>


                    <div class="fr-confirmation" x-show="confirmer" x-cloak>

                        <strong>Confirmer votre décision ?</strong>

                        Elle est définitive et sera consignée à votre nom.


                        <div class="fr-confirmation-actions">

                            <button type="submit" class="fr-btn principal">

                                <i class="fa-solid fa-check"></i>

                                Oui, valider

                            </button>


                            <button
                                type="button"
                                class="fr-btn secondaire"
                                @click="confirmer = false"
                            >

                                Annuler

                            </button>

                        </div>

                    </div>

                </div>

            </form>

        @endif

    </section>



    {{-- =====================================================
         RETOUR
    ====================================================== --}}

    <div class="fr-retour">

        <a href="{{ route('responsable.soupcons.index') }}" class="fr-btn secondaire">

            <i class="fa-solid fa-arrow-left"></i>

            Retour aux dossiers reçus

        </a>

    </div>


</div>

@endsection
