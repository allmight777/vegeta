@extends('layouts.responsable')

@section('titre', 'Vue consolidée de la personne')

@section('contenu')
    <div class="space-y-5">

        {{-- Aucun nom en clair dans cet en-tête : l'identité se lit par ses rattachements. --}}
        <section class="rounded-md border border-gray-200 bg-white px-4 py-3">
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">
                    {{ $clients->count() }} fiche(s) client
                </span>
                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">
                    {{ $comptes->count() }} compte(s)
                </span>
                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">
                    {{ $comptes->pluck('agence_id')->unique()->count() }} agence(s)
                </span>
                @if ($identite->rapprocheeParNpi())
                    <span class="rounded bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                        Rapprochement par NPI vérifié
                    </span>
                @else
                    <span class="rounded bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">
                        Rapprochement par empreinte — à confirmer
                    </span>
                @endif
            </div>
        </section>

        <section>
            <h2 class="mb-2 text-sm font-semibold text-gray-700">Plafond quotidien espèces</h2>
            <div class="rounded-md border border-gray-200 bg-white px-3 py-3 text-sm">
                @if ((float) $identite->plafond_quotidien_especes > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700">
                            {{ number_format($cumul?->totalRetenu() ?? 0, 0, ',', ' ') }} XOF aujourd'hui
                        </span>
                        <span class="text-gray-400">
                            plafond {{ number_format((float) $identite->plafond_quotidien_especes, 0, ',', ' ') }} XOF
                        </span>
                    </div>

                    <div class="mt-2 h-2 w-full rounded bg-gray-100">
                        <div class="h-2 rounded {{ $pourcentagePlafond >= 100 ? 'bg-red-500' : ($pourcentagePlafond >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                             style="width: {{ $pourcentagePlafond }}%"></div>
                    </div>

                    <p class="mt-2 text-xs text-gray-400">
                        {{ $pourcentagePlafond }} % du plafond &middot; calculé sur : {{ $identite->base_calcul_plafond }}
                        &middot; source {{ $identite->source_plafond->libelle() }}
                    </p>

                    @if ($cumul !== null)
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $cumul->nb_operations }} opération(s) sur {{ $cumul->nb_comptes }} compte(s),
                            dans {{ $cumul->nb_agences }} agence(s).
                        </p>
                    @endif
                @else
                    <p class="text-gray-400">Aucun plafond calculé : profil client à compléter.</p>
                @endif
            </div>
        </section>

        <section>
            <h2 class="mb-2 text-sm font-semibold text-gray-700">Comptes de la personne</h2>
            <div class="space-y-1.5">
                @forelse ($comptes as $compte)
                    <div class="flex items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">
                        <span class="text-gray-700">{{ $compte->agence->nom }}</span>
                        <span class="text-xs text-gray-400">
                            {{ $compte->derniere_operation_le?->diffForHumans() ?? 'aucune opération' }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucun compte ouvert.</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="mb-2 text-sm font-semibold text-gray-700">Pourquoi ces comptes sont-ils liés ?</h2>
            <div class="space-y-1.5">
                @foreach ($rattachements as $rattachement)
                    <div class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('agent.clients.completer', $rattachement->client) }}" class="text-emerald-700 hover:underline">
                                {{ $rattachement->client->nomAffichage() }}
                            </a>
                            <span class="text-xs text-gray-400">{{ $rattachement->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ $rattachement->justification() }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section>
            <h2 class="mb-2 text-sm font-semibold text-gray-700">Alertes en cours sur cette personne</h2>
            <div class="space-y-1.5">
                @forelse ($alertes as $alerte)
                    <div class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">
                        <div class="flex items-center justify-between">
                            <x-badge-gravite :gravite="$alerte->gravite" />
                            <span class="text-xs text-gray-400">{{ $alerte->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-gray-700">{{ $alerte->explication_texte }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucune alerte en cours.</p>
                @endforelse
            </div>
        </section>

    </div>
@endsection