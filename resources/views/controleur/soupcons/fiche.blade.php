@extends('layouts.controleur')

@section('titre', "Fiche d'analyse de soupçon")
@section('sous-titre', 'Service Conformité et LBC/FT — vérifiez, complétez, puis transmettez au responsable d\'agence.')

@section('contenu')

@php
    $val = fn (string $champ, $defaut = null) => old($champ, $defaut);
    $indicateursCoches = old('indicateurs', $dossier?->indicateurs ?? $prefill['indicateurs']);
    $dates = old('dates_operations', implode(', ', $dossier?->dates_operations ?? $prefill['dates_operations']));
    $montants = old('montants_concernes', implode(', ', $dossier?->montants_concernes ?? $prefill['montants_concernes']));
    $typeClient = old('type_client', $dossier?->type_client?->value ?? $prefill['type_client']->value);
    $niveau = old('niveau_risque', $dossier?->niveau_risque?->value ?? $prefill['niveau_risque']->value);
    $canal = old('canal', $dossier?->canal?->value ?? $prefill['canal']->value);
    $avisChoisi = old('avis_technique_controleur', $dossier?->avis_technique_controleur?->value);
    $disabled = $lecture ? 'disabled' : '';
@endphp

<style>
    .fs { max-width: 920px; display: grid; gap: 18px; }
    .fs-section { background: #fff; border: 1px solid var(--cif-border); border-radius: 18px; padding: 20px 22px; box-shadow: var(--cif-shadow); }
    .fs-section h2 { margin: 0 0 14px; font-size: .95rem; font-weight: 800; color: var(--cif-text); display: flex; align-items: center; gap: 10px; }
    .fs-num { width: 26px; height: 26px; border-radius: 8px; background: var(--cif-accent); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .78rem; }
    .fs-grille { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; }
    .fs label.fs-lib { display: block; font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--cif-muted); margin-bottom: 6px; }
    .fs-fixe { padding: 10px 12px; border-radius: 10px; background: #F8FAFC; border: 1px solid var(--cif-border); font-weight: 700; font-size: .88rem; }
    .fs input[type=text], .fs select, .fs textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--cif-border); border-radius: 10px; font: inherit; font-size: .86rem; background: #fff; }
    .fs textarea { min-height: 110px; resize: vertical; }
    .fs [disabled] { background: #F8FAFC; color: #334155; }
    .fs-options { display: flex; flex-wrap: wrap; gap: 8px 18px; }
    .fs-option { display: inline-flex; align-items: center; gap: 8px; font-size: .84rem; font-weight: 600; }
    .fs-cases { display: grid; gap: 10px; }
    .fs-case { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid var(--cif-border); border-radius: 12px; font-size: .86rem; font-weight: 600; }
    .fs-case.detecte { border-color: var(--cif-accent); background: var(--cif-accent-soft); }
    .fs-auto { margin-left: auto; font-size: .64rem; font-weight: 800; color: var(--cif-accent); text-transform: uppercase; letter-spacing: .05em; }
    .fs-ops { width: 100%; border-collapse: collapse; font-size: .78rem; margin-top: 12px; }
    .fs-ops th, .fs-ops td { text-align: left; padding: 6px 8px; border-bottom: 1px solid var(--cif-border); }
    .fs-ops th { color: var(--cif-muted); font-size: .66rem; text-transform: uppercase; }
    .fs-signature { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--cif-border); font-size: .82rem; }
    .fs-signature b { color: var(--cif-muted); font-size: .66rem; text-transform: uppercase; display: block; }
    .fs-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .fs-btn { border: 0; border-radius: 12px; padding: 12px 20px; font: inherit; font-weight: 800; font-size: .84rem; cursor: pointer; }
    .fs-btn.principal { background: var(--cif-accent); color: #fff; }
    .fs-btn.secondaire { background: #fff; color: var(--cif-text); border: 1px solid var(--cif-border); }
    .fs-btn.danger { background: #fff; color: #B91C1C; border: 1px solid #FECACA; }
    .fs-erreur { color: #B91C1C; font-size: .76rem; font-weight: 700; margin-top: 4px; }
    .fs-alerte { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 16px; border-radius: 12px; font-weight: 700; }
    .fs-succes { background: #ECFDF3; border: 1px solid #A6E9BD; color: #166534; padding: 12px 16px; border-radius: 12px; font-weight: 600; }
    .fs-info { background: var(--cif-accent-soft); border: 1px solid var(--cif-border); padding: 12px 16px; border-radius: 12px; font-size: .82rem; }
    .fs-aide { margin-top: 12px; padding: 14px 16px; border-radius: 12px; background: #F8FAFC; border: 1px solid var(--cif-border); font-size: .84rem; line-height: 1.55; }
    .fs-aide small { display: block; margin-top: 6px; color: var(--cif-muted); font-style: italic; }
</style>

<div class="fs" x-data="{ aide: null, chargement: false, confirmer: false,
    async demanderAide() {
        this.chargement = true; this.aide = null;
        try {
            const r = await fetch(@js(route('controleur.soupcons.aide', $suggestion)), { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
            this.aide = r.ok ? await r.json() : { texte: 'L\'aide à l\'analyse n\'est pas disponible pour le moment. Vous pouvez poursuivre la fiche normalement.', source: 'indisponible' };
        } catch (e) { this.aide = { texte: 'L\'aide à l\'analyse n\'est pas disponible pour le moment. Vous pouvez poursuivre la fiche normalement.', source: 'indisponible' }; }
        this.chargement = false;
    } }">

    @if (session('statut'))
        <div class="fs-succes" role="status">{{ session('statut') }}</div>
    @endif

    @if ($errors->any())
        <div class="fs-alerte" role="alert">La fiche n'a pas pu être enregistrée : corrigez les champs signalés.</div>
    @endif

    @if ($lecture)
        <div class="fs-info">
            <strong>Fiche en lecture seule</strong> — statut : {{ $suggestion->statut->libelle() }}.
            @if ($suggestion->controleur_id && $suggestion->controleur_id !== $agent->id)
                Ce dossier est pris en charge par un autre contrôleur.
            @endif
        </div>
    @endif

    <form method="POST" id="fiche" class="fs" novalidate>
        @csrf

        {{-- 1. INFORMATIONS SUR LE CLIENT --}}
        <section class="fs-section">
            <h2><span class="fs-num">1</span> Informations sur le client</h2>
            <div class="fs-grille">
                <div><label class="fs-lib">Nom et prénom / raison sociale</label><div class="fs-fixe">{{ $prefill['nom'] }}</div></div>
                <div><label class="fs-lib">Numéro de compte</label><div class="fs-fixe">{{ $prefill['numero_compte'] ?? '—' }}</div></div>
                <div><label class="fs-lib">Date d'ouverture</label><div class="fs-fixe">{{ $prefill['date_ouverture'] ?? '—' }}</div></div>
            </div>
            <div class="fs-grille" style="margin-top:14px">
                <div>
                    <label class="fs-lib">Type de client</label>
                    <div class="fs-options">
                        @foreach ($types as $t)
                            <label class="fs-option"><input type="radio" name="type_client" value="{{ $t->value }}" @checked($typeClient === $t->value) {{ $disabled }}> {{ $t->libelle() }}</label>
                        @endforeach
                    </div>
                    @error('type_client') <div class="fs-erreur">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="fs-lib">Niveau de risque attribué</label>
                    <div class="fs-options">
                        @foreach ($niveaux as $n)
                            <label class="fs-option"><input type="radio" name="niveau_risque" value="{{ $n->value }}" @checked($niveau === $n->value) {{ $disabled }}> {{ $n->libelle() }}</label>
                        @endforeach
                    </div>
                    @error('niveau_risque') <div class="fs-erreur">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

        {{-- 2. DESCRIPTION DE L'OPÉRATION SUSPECTE --}}
        <section class="fs-section">
            <h2><span class="fs-num">2</span> Description de l'opération suspecte</h2>
            <div class="fs-grille">
                <div>
                    <label class="fs-lib" for="dates_operations">Date(s) de l'opération</label>
                    <input type="text" id="dates_operations" name="dates_operations" value="{{ $dates }}" placeholder="AAAA-MM-JJ, AAAA-MM-JJ" {{ $disabled }}>
                    @error('dates_operations.*') <div class="fs-erreur">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="fs-lib" for="montants_concernes">Montant(s) concerné(s) (XOF)</label>
                    <input type="text" id="montants_concernes" name="montants_concernes" value="{{ $montants }}" placeholder="480000, 490000" {{ $disabled }}>
                    @error('montants_concernes.*') <div class="fs-erreur">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="fs-lib" for="canal">Canal utilisé</label>
                    <select id="canal" name="canal" {{ $disabled }}>
                        @foreach ($canaux as $c)
                            <option value="{{ $c->value }}" @selected($canal === $c->value)>{{ $c->libelle() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if (! empty($prefill['operations']))
                <table class="fs-ops">
                    <thead><tr><th>Opérations relevées par le système</th><th>Type</th><th>Montant</th><th>Agence</th></tr></thead>
                    <tbody>
                        @foreach ($prefill['operations'] as $op)
                            <tr><td>{{ $op['date'] }}</td><td>{{ $op['type'] }}</td><td>{{ number_format($op['montant'], 0, ',', ' ') }} XOF</td><td>{{ $op['agence'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div style="margin-top:14px">
                <label class="fs-lib" for="resume_faits">Résumé des faits</label>
                <textarea id="resume_faits" name="resume_faits" {{ $disabled }}>{{ old('resume_faits', $dossier?->resume_faits) }}</textarea>
                @error('resume_faits') <div class="fs-erreur">{{ $message }}</div> @enderror
            </div>
        </section>

        {{-- 3. ANALYSE DU CARACTÈRE SUSPECT --}}
        <section class="fs-section">
            <h2><span class="fs-num">3</span> Analyse du caractère suspect — indicateurs de soupçon</h2>
            <div class="fs-cases">
                @foreach ($indicateursListe as $ind)
                    <label class="fs-case {{ in_array($ind->value, $detectes, true) ? 'detecte' : '' }}">
                        <input type="checkbox" name="indicateurs[]" value="{{ $ind->value }}" @checked(in_array($ind->value, $indicateursCoches, true)) {{ $disabled }}>
                        {{ $ind->libelle() }}
                        @if (in_array($ind->value, $detectes, true))
                            <span class="fs-auto"><i class="fa-solid fa-wand-magic-sparkles"></i> détecté par le système</span>
                        @endif
                    </label>
                @endforeach
            </div>
            <div style="margin-top:12px">
                <label class="fs-lib" for="indicateur_autre_texte">Autres — précisez</label>
                <input type="text" id="indicateur_autre_texte" name="indicateur_autre_texte" value="{{ old('indicateur_autre_texte', $dossier?->indicateur_autre_texte) }}" {{ $disabled }}>
                @error('indicateur_autre_texte') <div class="fs-erreur">{{ $message }}</div> @enderror
            </div>
            @error('indicateurs') <div class="fs-erreur">{{ $message }}</div> @enderror
        </section>

        {{-- 4. DÉCISION ET SUITES À DONNER --}}
        <section class="fs-section">
            <h2><span class="fs-num">4</span> Décision et suites à donner — Contrôleur permanent</h2>
            <label class="fs-lib" for="analyse_controleur">Résultats des enquêtes / investigations et analyse</label>
            <textarea id="analyse_controleur" name="analyse_controleur" {{ $disabled }}>{{ old('analyse_controleur', $dossier?->analyse_controleur) }}</textarea>
            @error('analyse_controleur') <div class="fs-erreur">{{ $message }}</div> @enderror

            <div style="margin-top:14px">
                <label class="fs-lib">Avis technique</label>
                <div class="fs-options">
                    @foreach ($avis as $a)
                        <label class="fs-option"><input type="radio" name="avis_technique_controleur" value="{{ $a->value }}" @checked($avisChoisi === $a->value) {{ $disabled }}> {{ $a->libelle() }}</label>
                    @endforeach
                </div>
                @error('avis_technique_controleur') <div class="fs-erreur">{{ $message }}</div> @enderror
            </div>

            <div class="fs-signature">
                <div><b>Nom (Contrôleur permanent)</b>{{ $dossier?->controleur?->nom ?? $agent->nom }}</div>
                <div><b>Signature</b>Apposée automatiquement à la transmission</div>
                <div><b>Date</b>{{ $dossier?->transmis_le?->format('d/m/Y H:i') ?? 'À la transmission' }}</div>
            </div>
        </section>

        @unless ($lecture)
            <div class="fs-actions">
                <button type="submit" class="fs-btn secondaire" formaction="{{ route('controleur.soupcons.brouillon', $suggestion) }}">
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer le brouillon
                </button>
                <button type="button" class="fs-btn secondaire" @click="demanderAide()" :disabled="chargement">
                    <i class="fa-solid fa-lightbulb"></i> <span x-text="chargement ? 'Analyse en cours…' : 'Aide à l\'analyse'">Aide à l'analyse</span>
                </button>
                <button type="button" class="fs-btn principal" @click="confirmer = true">
                    <i class="fa-solid fa-paper-plane"></i> Transmettre au responsable d'agence
                </button>
                <button type="submit" class="fs-btn danger" formaction="{{ route('controleur.soupcons.ecarter', $suggestion) }}"
                        onclick="return confirm('Écarter cette suggestion ? Elle ne sera pas transmise.');">
                    Écarter la suggestion
                </button>
            </div>

            <div class="fs-info" x-show="confirmer" x-cloak>
                <strong>Confirmer la transmission ?</strong> La fiche sera envoyée au responsable de l'agence du client et ne sera plus modifiable.
                <div class="fs-actions" style="margin-top:10px">
                    <button type="submit" class="fs-btn principal" formaction="{{ route('controleur.soupcons.transmettre', $suggestion) }}">Oui, transmettre</button>
                    <button type="button" class="fs-btn secondaire" @click="confirmer = false">Annuler</button>
                </div>
            </div>
        @endunless
    </form>

    <div class="fs-aide" x-show="aide" x-cloak role="status">
        <strong><i class="fa-solid fa-lightbulb"></i> Pourquoi le système a suspecté ce profil</strong>
        <div x-text="aide?.texte" style="margin-top:6px"></div>
        <small x-text="aide?.source === 'analyse_assistee' ? 'Analyse assistée' : (aide?.source === 'controle_local' ? 'Contrôle local — reformulation des indicateurs détectés' : '')"></small>
    </div>

    <div>
        <a href="{{ route('controleur.tableau-de-bord.index') }}" class="fs-btn secondaire" style="text-decoration:none;display:inline-block">← Retour aux clients suspectés</a>
    </div>
</div>

@endsection
