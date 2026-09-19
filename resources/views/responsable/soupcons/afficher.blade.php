@extends('layouts.responsable')

@section('titre', 'Dossier '.$dossier->reference())
@section('sous-titre', "Fiche d'analyse de soupçon transmise par le contrôleur permanent — lecture seule, puis votre validation.")

@section('contenu')

@php
    $traite = $dossier->statut === \App\Enums\StatutDossierSoupcon::Traitee;
    $coches = $dossier->indicateurs ?? [];
@endphp

<style>
    .fr { max-width: 920px; display: grid; gap: 18px; }
    .fr-section { background: #fff; border: 1px solid var(--cif-border); border-radius: 18px; padding: 20px 22px; box-shadow: var(--cif-shadow); }
    .fr-section h2 { margin: 0 0 14px; font-size: .95rem; font-weight: 800; color: var(--cif-text); display: flex; align-items: center; gap: 10px; }
    .fr-num { width: 26px; height: 26px; border-radius: 8px; background: var(--cif-accent); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .78rem; }
    .fr-grille { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
    .fr-lib { display: block; font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--cif-muted); margin-bottom: 5px; }
    .fr-val { font-size: .88rem; font-weight: 600; color: var(--cif-text); white-space: pre-line; line-height: 1.55; }
    .fr-cases { display: grid; gap: 8px; }
    .fr-case { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border: 1px solid var(--cif-border); border-radius: 12px; font-size: .84rem; color: #94A3B8; }
    .fr-case.coche { color: var(--cif-text); border-color: var(--cif-accent); background: var(--cif-accent-soft); font-weight: 700; }
    .fr-options { display: flex; flex-wrap: wrap; gap: 8px 18px; }
    .fr-option { display: inline-flex; align-items: center; gap: 8px; font-size: .86rem; font-weight: 600; }
    .fr-signature { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--cif-border); font-size: .82rem; }
    .fr-signature b { color: var(--cif-muted); font-size: .66rem; text-transform: uppercase; display: block; }
    .fr-btn { border: 0; border-radius: 12px; padding: 12px 20px; font: inherit; font-weight: 800; font-size: .84rem; cursor: pointer; text-decoration: none; display: inline-block; }
    .fr-btn.principal { background: var(--cif-accent); color: #fff; }
    .fr-btn.secondaire { background: #fff; color: var(--cif-text); border: 1px solid var(--cif-border); }
    .fr-erreur { color: #B91C1C; font-size: .78rem; font-weight: 700; margin-top: 6px; }
    .fr-aide { margin-top: 12px; padding: 14px 16px; border-radius: 12px; background: #F8FAFC; border: 1px solid var(--cif-border); font-size: .86rem; line-height: 1.55; }
    .fr-aide small { display: block; margin-top: 6px; color: var(--cif-muted); font-style: italic; }
    .fr-ops { width: 100%; border-collapse: collapse; font-size: .78rem; margin-top: 12px; }
    .fr-ops th, .fr-ops td { text-align: left; padding: 6px 8px; border-bottom: 1px solid var(--cif-border); }
    .fr-ops th { color: var(--cif-muted); font-size: .66rem; text-transform: uppercase; }
</style>

<div class="fr" x-data="{ resume: null, chargement: false, confirmer: false,
    async resumer() {
        this.chargement = true; this.resume = null;
        try {
            const r = await fetch(@js(route('responsable.soupcons.resumer', $dossier->id)), { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
            this.resume = r.ok ? await r.json() : { texte: 'Le résumé n\'est pas disponible pour le moment. Lisez la fiche ci-dessous.', source: 'indisponible' };
        } catch (e) { this.resume = { texte: 'Le résumé n\'est pas disponible pour le moment. Lisez la fiche ci-dessous.', source: 'indisponible' }; }
        this.chargement = false;
    } }">

    <div>
        <button type="button" class="fr-btn secondaire" @click="resumer()" :disabled="chargement">
            <i class="fa-solid fa-wand-magic-sparkles"></i> <span x-text="chargement ? 'Résumé en cours…' : 'Résumer ce dossier'">Résumer ce dossier</span>
        </button>
        <div class="fr-aide" x-show="resume" x-cloak role="status">
            <div x-text="resume?.texte"></div>
            <small x-text="resume?.source === 'analyse_assistee' ? 'Résumé assisté' : (resume?.source === 'controle_local' ? 'Résumé simple — extraction des champs structurés de la fiche' : '')"></small>
        </div>
    </div>

    <section class="fr-section">
        <h2><span class="fr-num">1</span> Informations sur le client</h2>
        <div class="fr-grille">
            <div><span class="fr-lib">Nom et prénom / raison sociale</span><div class="fr-val">{{ $prefill['nom'] }}</div></div>
            <div><span class="fr-lib">Numéro de compte</span><div class="fr-val">{{ $prefill['numero_compte'] ?? '—' }}</div></div>
            <div><span class="fr-lib">Date d'ouverture</span><div class="fr-val">{{ $prefill['date_ouverture'] ?? '—' }}</div></div>
            <div><span class="fr-lib">Type de client</span><div class="fr-val">{{ $dossier->type_client->libelle() }}</div></div>
            <div><span class="fr-lib">Niveau de risque attribué</span><div class="fr-val">{{ $dossier->niveau_risque->libelle() }}</div></div>
        </div>
    </section>

    <section class="fr-section">
        <h2><span class="fr-num">2</span> Description de l'opération suspecte</h2>
        <div class="fr-grille">
            <div><span class="fr-lib">Date(s) de l'opération</span><div class="fr-val">{{ implode(', ', $dossier->dates_operations ?? []) ?: '—' }}</div></div>
            <div><span class="fr-lib">Montant(s) concerné(s)</span><div class="fr-val">{{ collect($dossier->montants_concernes ?? [])->map(fn ($m) => number_format($m, 0, ',', ' ').' XOF')->implode(', ') ?: '—' }}</div></div>
            <div><span class="fr-lib">Canal utilisé</span><div class="fr-val">{{ $dossier->canal?->libelle() ?? '—' }}</div></div>
        </div>
        <div style="margin-top:14px"><span class="fr-lib">Résumé des faits</span><div class="fr-val">{{ $dossier->resume_faits ?: '—' }}</div></div>
    </section>

    <section class="fr-section">
        <h2><span class="fr-num">3</span> Analyse du caractère suspect — indicateurs de soupçon</h2>
        <div class="fr-cases">
            @foreach (\App\Enums\IndicateurSoupcon::cases() as $indicateur)
                <div class="fr-case {{ in_array($indicateur->value, $coches, true) ? 'coche' : '' }}">
                    <i class="fa-solid {{ in_array($indicateur->value, $coches, true) ? 'fa-square-check' : 'fa-square' }}"></i>
                    {{ $indicateur->libelle() }}
                    @if ($indicateur === \App\Enums\IndicateurSoupcon::Autres && $dossier->indicateur_autre_texte && in_array($indicateur->value, $coches, true))
                        — {{ $dossier->indicateur_autre_texte }}
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <section class="fr-section">
        <h2><span class="fr-num">4</span> Décision et suites à donner — Contrôleur permanent</h2>
        <span class="fr-lib">Résultats des enquêtes / investigations et analyse</span>
        <div class="fr-val">{{ $dossier->analyse_controleur ?: '—' }}</div>
        <div style="margin-top:14px">
            <span class="fr-lib">Avis technique</span>
            <div class="fr-options">
                @foreach ($avis as $a)
                    <span class="fr-option"><i class="fa-solid {{ $dossier->avis_technique_controleur === $a ? 'fa-circle-dot' : 'fa-circle' }}" style="color: {{ $dossier->avis_technique_controleur === $a ? 'var(--cif-accent)' : '#CBD5E1' }}"></i> {{ $a->libelle() }}</span>
                @endforeach
            </div>
        </div>
        <div class="fr-signature">
            <div><b>Nom (Contrôleur permanent)</b>{{ $dossier->controleur?->nom }}</div>
            <div><b>Signature</b>Apposée à la transmission</div>
            <div><b>Date</b>{{ $dossier->transmis_le?->format('d/m/Y H:i') }}</div>
        </div>
    </section>

    {{-- VALIDATION — responsable d'agence --}}
    <section class="fr-section" style="border-color: var(--cif-accent)">
        <h2><span class="fr-num">✓</span> Validation — Responsable d'agence</h2>

        @if ($traite)
            <span class="fr-lib">Avis technique retenu</span>
            <div class="fr-options">
                @foreach ($avis as $a)
                    <span class="fr-option"><i class="fa-solid {{ $dossier->avis_technique_responsable === $a ? 'fa-circle-dot' : 'fa-circle' }}" style="color: {{ $dossier->avis_technique_responsable === $a ? 'var(--cif-accent)' : '#CBD5E1' }}"></i> {{ $a->libelle() }}</span>
                @endforeach
            </div>
            <div class="fr-signature">
                <div><b>Nom (Responsable d'agence)</b>{{ $dossier->responsable?->nom }}</div>
                <div><b>Signature</b>Apposée à la validation</div>
                <div><b>Date</b>{{ $dossier->decide_le?->format('d/m/Y H:i') }}</div>
            </div>
        @else
            <form method="POST" action="{{ route('responsable.soupcons.decider', $dossier->id) }}">
                @csrf
                <span class="fr-lib">Avis technique</span>
                <div class="fr-options">
                    @foreach ($avis as $a)
                        <label class="fr-option"><input type="radio" name="avis_technique_responsable" value="{{ $a->value }}" @checked(old('avis_technique_responsable') === $a->value)> {{ $a->libelle() }}</label>
                    @endforeach
                </div>
                @error('avis_technique_responsable') <div class="fr-erreur">{{ $message }}</div> @enderror

                <div class="fr-signature">
                    <div><b>Nom (Responsable d'agence)</b>{{ $agent->nom }}</div>
                    <div><b>Signature</b>Apposée automatiquement</div>
                    <div><b>Date</b>À la validation</div>
                </div>

                <div style="margin-top:16px">
                    <button type="button" class="fr-btn principal" @click="confirmer = true" x-show="!confirmer">Valider ma décision</button>
                    <div x-show="confirmer" x-cloak>
                        <strong>Confirmer votre décision ?</strong> Elle est définitive et sera consignée à votre nom.
                        <div style="margin-top:10px; display:flex; gap:10px">
                            <button type="submit" class="fr-btn principal">Oui, valider</button>
                            <button type="button" class="fr-btn secondaire" @click="confirmer = false">Annuler</button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </section>

    <div><a href="{{ route('responsable.soupcons.index') }}" class="fr-btn secondaire">← Retour aux dossiers reçus</a></div>
</div>

@endsection
