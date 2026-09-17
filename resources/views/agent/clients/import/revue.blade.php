@extends('layouts.agent')

@section('titre', 'Revue des documents importés')

@section('contenu')

@include('agent.clients._styles')

<div class="clients-page">

    <div class="clients-toolbar">
        <div class="clients-toolbar-left">
            <span class="clients-count">
                <i class="fa-solid fa-file-lines"></i>
                {{ $documents->count() }} document{{ $documents->count() > 1 ? 's' : '' }} dans ce lot
            </span>
        </div>
    </div>

    <div class="clients-list">
        @foreach ($documents as $document)
            <div class="client-card" style="cursor: default;">
                <div>
                    <strong>{{ $document->nomDetecte() }}</strong>
                    <div style="font-size: 0.72rem; color: var(--muted); margin-top: 2px;">
                        {{ $document->nom_fichier_original }}
                    </div>
                    <div style="margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap;">
                        <span class="meta-chip">{{ $document->statut_extraction->libelle() }}</span>
                        @if ($document->methode_extraction)
                            <span class="meta-chip">{{ $document->methode_extraction->libelle() }}</span>
                        @endif
                        @if ($document->client_id)
                            <span class="meta-chip" style="background: var(--green-soft); color: var(--green);">Client créé</span>
                        @endif
                    </div>
                    @if ($document->erreur_message)
                        <div style="font-size: 0.72rem; color: var(--danger); margin-top: 6px;">
                            {{ $document->erreur_message }}
                        </div>
                    @endif
                </div>

                @unless ($document->client_id)
                    <a href="{{ route('agent.clients.import.revoir', $document) }}" class="btn-secondary" style="margin-top: 10px;">
                        Revoir et valider
                    </a>
                @endunless
            </div>
        @endforeach
    </div>

</div>

@endsection
