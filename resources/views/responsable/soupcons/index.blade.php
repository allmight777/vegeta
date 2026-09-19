@extends('layouts.responsable')

@section('titre', 'Clients suspectés — dossiers reçus')
@section('sous-titre', "Fiches d'analyse transmises par le contrôleur permanent pour les clients de votre agence : à vous de décider.")

@section('contenu')

<style>
    .ds-liste { display: grid; gap: 14px; }
    .ds-carte { background: #fff; border: 1px solid var(--cif-border); border-radius: 18px; padding: 18px 20px; display: grid; grid-template-columns: 1fr auto; gap: 14px; align-items: center; box-shadow: var(--cif-shadow); }
    .ds-ref { font-size: .68rem; font-weight: 800; letter-spacing: .06em; color: var(--cif-muted); }
    .ds-nom { font-weight: 800; color: var(--cif-text); margin-top: 2px; }
    .ds-meta { font-size: .74rem; color: var(--cif-muted); margin-top: 3px; }
    .ds-badge { font-size: .68rem; font-weight: 800; padding: 4px 10px; border-radius: 999px; background: #EDE9FE; color: #5B21B6; }
    .ds-badge.traitee { background: #DCFCE7; color: #166534; }
    .ds-btn { display: inline-block; background: var(--cif-accent); color: #fff; font-weight: 800; font-size: .78rem; padding: 9px 16px; border-radius: 12px; text-decoration: none; margin-top: 8px; }
    .ds-vide { background: #fff; border: 1px dashed var(--cif-border); border-radius: 18px; padding: 38px; text-align: center; color: var(--cif-muted); }
    .ds-succes { background: #ECFDF3; border: 1px solid #A6E9BD; color: #166534; padding: 12px 16px; border-radius: 12px; font-weight: 600; margin-bottom: 14px; }
</style>

@if (session('statut'))
    <div class="ds-succes" role="status">{{ session('statut') }}</div>
@endif

@if ($dossiers->isEmpty())
    <div class="ds-vide">
        <strong>Aucun dossier reçu pour le moment.</strong><br>
        Lorsque le contrôleur permanent vous transmettra une fiche d'analyse concernant un client de votre agence, elle apparaîtra ici.
    </div>
@else
    <div class="ds-liste">
        @foreach ($dossiers as $dossier)
            <article class="ds-carte">
                <div>
                    <div class="ds-ref">{{ $dossier->reference() }}</div>
                    <div class="ds-nom">{{ $dossier->client->nomAffichage() }}</div>
                    <div class="ds-meta">
                        Transmis le {{ $dossier->transmis_le?->format('d/m/Y H:i') }} · risque {{ $dossier->niveau_risque->libelle() }}
                        · avis du contrôleur : {{ $dossier->avis_technique_controleur?->libelle() }}
                    </div>
                    <a class="ds-btn" href="{{ route('responsable.soupcons.afficher', $dossier->id) }}">
                        {{ $dossier->statut->value === 'transmise' ? 'Examiner et décider' : 'Consulter' }}
                    </a>
                </div>
                <span class="ds-badge {{ $dossier->statut->value }}">{{ $dossier->statut->value === 'transmise' ? 'À traiter' : 'Traité' }}</span>
            </article>
        @endforeach
    </div>
@endif

@endsection
