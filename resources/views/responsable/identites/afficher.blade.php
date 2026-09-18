@extends('layouts.responsable')

@section('titre', 'Vue consolidée de la personne')
@section('sous-titre', 'Tous les comptes de la même personne, toutes agences confondues')

@section('contenu')

@include('responsable.identites._styles')

<div class="identite-page">

    {{-- Aucun nom en clair ici : l'identité se lit par ses rattachements. --}}
    <div class="identite-header">

        <div class="identite-avatar">
            <i class="fa-solid fa-fingerprint"></i>
        </div>

        <div style="min-width: 0;">

            <h2>Personne physique consolidée</h2>

            <div class="identite-chips">

                <span class="chip-dark">
                    <i class="fa-solid fa-folder-open"></i>
                    {{ $clients->count() }} fiche{{ $clients->count() > 1 ? 's' : '' }}
                </span>

                <span class="chip-dark">
                    <i class="fa-solid fa-wallet"></i>
                    {{ $comptes->count() }} compte{{ $comptes->count() > 1 ? 's' : '' }}
                </span>

                <span class="chip-dark">
                    <i class="fa-solid fa-location-dot"></i>
                    {{ $comptes->pluck('agence_id')->unique()->count() }} agence{{ $comptes->pluck('agence_id')->unique()->count() > 1 ? 's' : '' }}
                </span>

                @if ($identite->rapprocheeParNpi())
                    <span class="chip-dark npi">
                        <i class="fa-solid fa-id-card"></i>
                        Rapprochement par NPI vérifié
                    </span>
                @else
                    <span class="chip-dark empreinte">
                        <i class="fa-solid fa-wave-square"></i>
                        Rapprochement par empreinte — à confirmer
                    </span>
                @endif

            </div>

        </div>

    </div>

    <div class="bloc">

        <div class="bloc-titre">
            <i class="fa-solid fa-money-bill-wave"></i>
            Plafond quotidien espèces
        </div>

        <div class="bloc-corps">

            @if ((float) $identite->plafond_quotidien_especes > 0)

                @php
                    $classeJauge = $pourcentagePlafond >= 100 ? 'danger' : ($pourcentagePlafond >= 80 ? 'warn' : '');
                @endphp

                <div class="jauge-chiffres">
                    <span class="jauge-cumul">
                        {{ number_format($cumul?->totalRetenu() ?? 0, 0, ',', ' ') }} XOF
                    </span>
                    <span class="jauge-plafond">
                        plafond {{ number_format((float) $identite->plafond_quotidien_especes, 0, ',', ' ') }} XOF
                        &middot; {{ $pourcentagePlafond }} %
                    </span>
                </div>

                <div class="jauge-bar">
                    <span class="{{ $classeJauge }}" style="width: {{ $pourcentagePlafond }}%"></span>
                </div>

                <p class="jauge-note">
                    Calculé sur : {{ $identite->base_calcul_plafond }}
                    &middot; source {{ $identite->source_plafond->libelle() }}
                    @if ($cumul !== null)
                        <br>
                        {{ $cumul->nb_operations }} opération{{ $cumul->nb_operations > 1 ? 's' : '' }}
                        sur {{ $cumul->nb_comptes }} compte{{ $cumul->nb_comptes > 1 ? 's' : '' }},
                        dans {{ $cumul->nb_agences }} agence{{ $cumul->nb_agences > 1 ? 's' : '' }}.
                    @endif
                </p>

            @else
                <p class="vide">Aucun plafond calculé : profil client à compléter.</p>
            @endif

        </div>

    </div>

    <div class="bloc">

        <div class="bloc-titre">
            <i class="fa-solid fa-building-columns"></i>
            Comptes de la personne
        </div>

        <div class="bloc-corps">

            @forelse ($comptes as $compte)
                <div class="ligne">
                    <div class="ligne-gauche">
                        <div class="ligne-icone"><i class="fa-solid fa-wallet"></i></div>
                        <div>
                            <div class="ligne-titre">{{ $compte->agence->nom }}</div>
                            <div class="ligne-sous">{{ $compte->statut->libelle() }}</div>
                        </div>
                    </div>
                    <span class="ligne-droite">
                        {{ $compte->derniere_operation_le?->diffForHumans() ?? 'aucune opération' }}
                    </span>
                </div>
            @empty
                <p class="vide">Aucun compte ouvert.</p>
            @endforelse

        </div>

    </div>

    <div class="bloc">

        <div class="bloc-titre">
            <i class="fa-solid fa-diagram-project"></i>
            Pourquoi ces comptes sont-ils liés ?
        </div>

        <div class="bloc-corps">

            @foreach ($rattachements as $rattachement)
                <div class="ligne">
                    <div class="ligne-gauche">
                        <div class="ligne-icone"><i class="fa-solid fa-link"></i></div>
                        <div>
                            <div class="ligne-titre">
                                <a href="{{ route('agent.clients.completer', $rattachement->client) }}">
                                    {{ $rattachement->client->nomAffichage() }}
                                </a>
                            </div>
                            <div class="ligne-sous">{{ $rattachement->justification() }}</div>
                        </div>
                    </div>
                    <span class="ligne-droite">{{ $rattachement->created_at->diffForHumans() }}</span>
                </div>
            @endforeach

        </div>

    </div>

    <div class="bloc">

        <div class="bloc-titre">
            <i class="fa-solid fa-bell"></i>
            Alertes en cours sur cette personne
        </div>

        <div class="bloc-corps">

            @forelse ($alertes as $alerte)
                <div class="alerte {{ $alerte->gravite->value }}">
                    <div class="alerte-tete">
                        <span class="alerte-type">{{ $alerte->type->libelle() }}</span>
                        <span class="alerte-date">{{ $alerte->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="alerte-texte">{{ $alerte->explication_texte }}</p>
                </div>
            @empty
                <p class="vide">Aucune alerte en cours.</p>
            @endforelse

        </div>

    </div>

</div>

@endsection