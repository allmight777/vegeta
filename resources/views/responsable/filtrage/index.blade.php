@extends('layouts.responsable')

@section('titre', 'Correspondances à vérifier')
@section('sous-titre', 'Chaque décision est mémorisée : une correspondance tranchée ne revient plus pour cette personne')

@section('contenu')

@include('responsable.filtrage._styles')

<div class="filtrage-page">

    <div class="filtrage-stats">

        <div class="stat-card">
            <div class="stat-icon warn">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <div>
                <div class="stat-value">{{ $resultats->count() }}</div>
                <div class="stat-label">À examiner maintenant</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fa-solid fa-filter-circle-xmark"></i>
            </div>
            <div>
                <div class="stat-value">{{ $alertesEvitees }}</div>
                <div class="stat-label">Alertes évitées par vos décisions</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-brain"></i>
            </div>
            <div>
                <div class="stat-value">{{ $decisionsConnues }}</div>
                <div class="stat-label">Cas mémorisés</div>
            </div>
        </div>

    </div>

    @if ($resultats->count())

        <div class="filtrage-list">

            @foreach ($resultats as $resultat)

                @php
                    $cible = $resultat->filtrable;
                    $nomCible = $cible instanceof \App\Models\Signataire ? $cible->nom : $cible->nomAffichage();
                    $pourcentage = (int) round($resultat->score_similarite * 100);
                    $eleve = $pourcentage >= 85;
                @endphp

                <div class="match-card">

                    <div class="match-head">

                        <div class="match-identity">

                            <div class="match-avatar">
                                <i class="fa-solid {{ $cible instanceof \App\Models\Signataire ? 'fa-pen-nib' : 'fa-user' }}"></i>
                            </div>

                            <div>

                                <div class="match-name">{{ $nomCible }}</div>

                                <div class="match-meta">

                                    <span class="meta-chip source">
                                        <i class="fa-solid fa-list-check"></i>
                                        {{ $resultat->entreeListe->source->libelle() }}
                                    </span>

                                    <span class="meta-chip">
                                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                        {{ $resultat->entreeListe->nom }}
                                    </span>

                                    @if ($resultat->entreeListe->categorie)
                                        <span class="meta-chip">
                                            <i class="fa-solid fa-tag"></i>
                                            {{ $resultat->entreeListe->categorie }}
                                        </span>
                                    @endif

                                    @if ($cible instanceof \App\Models\Client && $cible->identite_id)
                                        <span class="meta-chip">
                                            <i class="fa-solid fa-link"></i>
                                            Décision valable pour tous ses comptes
                                        </span>
                                    @endif

                                </div>

                            </div>

                        </div>

                        <div class="score-block">
                            <div class="score-value {{ $eleve ? 'high' : '' }}">{{ $pourcentage }} %</div>
                            <div class="score-label">similarité du nom</div>
                            <div class="score-bar">
                                <span class="{{ $eleve ? 'high' : '' }}" style="width: {{ $pourcentage }}%"></span>
                            </div>
                        </div>

                    </div>

                    {{-- Arbre de justification : le responsable choisit un motif codé, --}}
                    {{-- le texte d'audit réglementaire est assemblé automatiquement. --}}
                    <div class="match-decision" x-data="{ motif: '' }">

                        <div class="decision-title">Motif de la décision</div>

                        <select name="motif_code" class="decision-select"
                                x-model="motif"
                                form="decision-{{ $resultat->id }}">
                            <option value="">— Choisir un motif —</option>
                            @foreach ($motifs as $m)
                                <option value="{{ $m->value }}">{{ $m->libelle() }}</option>
                            @endforeach
                        </select>

                        <div x-show="motif === 'autre'" x-cloak>
                            <textarea name="motif" rows="2" class="decision-textarea"
                                      placeholder="Précisez le motif (obligatoire pour « Autre »)"
                                      form="decision-{{ $resultat->id }}"></textarea>
                        </div>

                        <div class="decision-hint">
                            <i class="fa-solid fa-circle-info" style="margin-top: 2px;"></i>
                            <span>
                                Le texte d'audit est rédigé automatiquement à partir du motif choisi.
                                La correspondance restera journalisée comme contrôlée, même si vous l'écartez.
                            </span>
                        </div>

                        <form id="decision-{{ $resultat->id }}" method="POST"
                              action="{{ route('responsable.filtrage.decider', $resultat) }}">
                            @csrf @method('PUT')

                            <div class="decision-actions">

                                <button type="submit" name="statut" value="confirme" class="btn-decision btn-confirmer">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    Confirmer la correspondance
                                </button>

                                <button type="submit" name="statut" value="ecarte" class="btn-decision btn-ecarter">
                                    <i class="fa-solid fa-user-check"></i>
                                    Faux positif
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="filtrage-vide">
            <i class="fa-solid fa-circle-check"></i>
            <p>Aucune correspondance en attente</p>
            <small>
                {{ $alertesEvitees }} alerte{{ $alertesEvitees > 1 ? 's' : '' }} déjà évitée{{ $alertesEvitees > 1 ? 's' : '' }}
                grâce aux décisions enregistrées.
            </small>
        </div>

    @endif

</div>

@endsection