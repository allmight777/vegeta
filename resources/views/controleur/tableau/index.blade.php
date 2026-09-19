@extends('layouts.controleur')

@section('titre', 'Clients suspectés')
@section('sous-titre', "Suggestions du système pour votre réseau — des faits à examiner, jamais des accusations.")

@section('contenu')

<style>
    .cs-liste { display: grid; gap: 14px; }
    .cs-carte { background: #fff; border: 1px solid var(--cif-border); border-radius: 18px; padding: 18px 20px; box-shadow: var(--cif-shadow); display: grid; grid-template-columns: 92px 1fr auto; gap: 18px; align-items: center; }
    .cs-score { text-align: center; }
    .cs-score strong { display: block; font-size: 1.7rem; font-weight: 800; color: var(--cif-accent); line-height: 1; }
    .cs-score span { font-size: .62rem; text-transform: uppercase; letter-spacing: .06em; color: var(--cif-muted); font-weight: 700; }
    .cs-barre { height: 6px; border-radius: 999px; background: #EEF2F7; margin-top: 8px; overflow: hidden; }
    .cs-barre i { display: block; height: 100%; background: var(--cif-accent); }
    .cs-nom { font-weight: 800; font-size: 1rem; color: var(--cif-text); }
    .cs-meta { font-size: .72rem; color: var(--cif-muted); margin-top: 2px; }
    .cs-faits { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .cs-fait { font-size: .7rem; font-weight: 600; padding: 4px 10px; border-radius: 999px; background: var(--cif-accent-soft); color: var(--cif-accent); }
    .cs-statut { font-size: .68rem; font-weight: 800; padding: 4px 10px; border-radius: 999px; background: #F1F5F9; color: #475569; white-space: nowrap; }
    .cs-statut.nouvelle { background: #FEF3C7; color: #92400E; }
    .cs-statut.en_analyse { background: #DBEAFE; color: #1E40AF; }
    .cs-statut.transmise { background: #EDE9FE; color: #5B21B6; }
    .cs-statut.traitee { background: #DCFCE7; color: #166534; }
    .cs-actions { display: grid; gap: 8px; justify-items: end; }
    .cs-btn { display: inline-flex; align-items: center; gap: 8px; background: var(--cif-accent); color: #fff; font-weight: 800; font-size: .78rem; padding: 9px 16px; border-radius: 12px; text-decoration: none; }
    .cs-vide { background: #fff; border: 1px dashed var(--cif-border); border-radius: 18px; padding: 38px; text-align: center; color: var(--cif-muted); }
    .cs-succes { background: #ECFDF3; border: 1px solid #A6E9BD; color: #166534; padding: 12px 16px; border-radius: 12px; font-weight: 600; margin-bottom: 14px; }
    @media (max-width: 720px) { .cs-carte { grid-template-columns: 1fr; } .cs-actions { justify-items: start; } }
</style>

@if (session('statut'))
    <div class="cs-succes" role="status">{{ session('statut') }}</div>
@endif

@if ($suggestions->isEmpty())
    <div class="cs-vide">
        <strong>Aucune suggestion pour le moment.</strong><br>
        Le système surveille en continu la cohérence des profils et des opérations de votre réseau ; les clients à examiner apparaîtront ici.
    </div>
@else
    <div class="cs-liste">
        @foreach ($suggestions as $suggestion)
            @php
                $client = $suggestion->client;
                $ouvrable = $suggestion->controleur_id === null || $suggestion->controleur_id === auth('agent')->id();
            @endphp
            <article class="cs-carte">
                <div class="cs-score">
                    <strong>{{ (int) round($suggestion->score) }}</strong>
                    <span>score / 100</span>
                    <div class="cs-barre"><i style="width: {{ min(100, (int) round($suggestion->score)) }}%"></i></div>
                </div>

                <div>
                    <div class="cs-nom">{{ $client->nomAffichage() }}</div>
                    <div class="cs-meta">
                        {{ $client->agenceCreation?->nom ?? 'Agence non renseignée' }}
                        · suggestion du {{ $suggestion->genere_le->format('d/m/Y H:i') }}
                    </div>
                    <div class="cs-faits">
                        @foreach ($suggestion->indicateurs() as $indicateur)
                            <span class="cs-fait">{{ $indicateur->libelle() }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="cs-actions">
                    <span class="cs-statut {{ $suggestion->statut->value }}">{{ $suggestion->statut->libelle() }}</span>
                    <a class="cs-btn" href="{{ route('controleur.soupcons.ouvrir', $suggestion) }}">
                        <i class="fa-solid fa-folder-open"></i>
                        {{ in_array($suggestion->statut->value, ['nouvelle', 'en_analyse'], true) && $ouvrable ? 'Analyser' : 'Consulter' }}
                    </a>
                </div>
            </article>
        @endforeach
    </div>
@endif

@endsection
