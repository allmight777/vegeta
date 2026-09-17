@extends('layouts.agent')

@section('titre', 'Revoir un document importé')

@section('contenu')

@include('agent.clients._styles')

<div class="clients-page">

    <div class="fiche-groupe" style="padding: 12px 18px; font-size: 0.78rem; color: var(--muted);">
        Champs extraits automatiquement de « {{ $document->nom_fichier_original }} » — vérifiez et
        corrigez avant de valider. Rien n'est enregistré tant que vous n'avez pas cliqué sur
        « Valider et créer le client ».
    </div>

    @if ($errors->any())
        <div class="fiche-groupe" style="padding: 12px 16px; border-color: var(--danger);">
            <strong style="color: var(--danger);">Corrigez les champs suivants :</strong>
            <ul style="margin: 6px 0 0 18px; font-size: 0.78rem;">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('agent.clients.import.valider', $document) }}" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="type" value="{{ $type }}">

        <details class="fiche-groupe" open>
            <summary>Type de client et relation</summary>
            <div class="fiche-groupe-corps">
                <div class="champ-fiche">
                    <label class="champ-fiche-label">Type détecté</label>
                    <input type="text" class="champ-fiche-input" value="{{ $type === 'personne_morale' ? 'Personne morale' : 'Personne physique' }}" disabled>
                </div>
                <div class="champ-fiche">
                    <label class="champ-fiche-label" for="nature_relation">Nature de la relation</label>
                    <select name="nature_relation" id="nature_relation" class="champ-fiche-input">
                        <option value="titulaire_compte">Titulaire de compte</option>
                        <option value="occasionnel">Client occasionnel</option>
                    </select>
                </div>
            </div>
        </details>

        @include('agent.clients._formulaire', ['type' => $type, 'valeurs' => $valeurs, 'confiances' => $confiances])

        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; height: 46px;">
            Valider et créer le client
        </button>
    </form>

</div>

@endsection
