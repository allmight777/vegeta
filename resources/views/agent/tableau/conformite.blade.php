@extends('layouts.agent')

@section('titre', 'Tableau de bord conformité')

@section('contenu')
    <div class="space-y-5">
        <section>
            <h2 class="mb-2 text-sm font-semibold text-gray-700">Dossiers à compléter</h2>
            <div class="space-y-1.5">
                @forelse ($dossiersACompleter as $client)
                    <a href="{{ route('agent.clients.completer', $client) }}" class="flex items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">
                        <span>{{ $client->nomAffichage() }}</span>
                        <span class="text-amber-600">{{ $client->score_completude_kyc }} %</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-400">Aucun dossier en attente.</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="mb-2 text-sm font-semibold text-gray-700">Alertes du jour</h2>
            <div class="space-y-1.5">
                @forelse ($alertesDuJour as $alerte)
                    <div class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">
                        <div class="flex items-center justify-between">
                            <x-badge-gravite :gravite="$alerte->gravite" />
                            <span class="text-xs text-gray-400">{{ $alerte->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-gray-700">{{ $alerte->explication_texte }}</p>
                        @if ($alerte->type->value === 'filtrage_sanction' || $alerte->type->value === 'filtrage_ppe')
                            <a href="{{ route('agent.filtrage.index') }}" class="mt-1 inline-block text-xs text-emerald-700 hover:underline">Traiter dans Filtrage →</a>
                        @elseif ($alerte->type->value === 'npi_invalide_apres_verification')
                            <form method="POST" action="{{ route('agent.conformite.alertes-npi.traiter', $alerte) }}" class="mt-1">
                                @csrf
                                <button type="submit" class="text-xs text-emerald-700 hover:underline">Marquer comme traité</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucune alerte en attente.</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="mb-2 text-sm font-semibold text-gray-700">Seuils et fractionnements</h2>
            <div class="space-y-1.5">
                @forelse ($seuilsEtFractionnements as $alerte)
                    <div class="rounded-md border {{ $alerte->type->value === 'fractionnement_multi_agences' ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50' }} px-3 py-2 text-sm">
                        <span class="text-xs font-semibold {{ $alerte->type->value === 'fractionnement_multi_agences' ? 'text-red-700' : 'text-amber-700' }}">
                            {{ $alerte->type->libelle() }}
                        </span>
                        <p class="mt-1 text-gray-700">{{ $alerte->explication_texte }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucun seuil approché ni fractionnement détecté.</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="mb-2 text-sm font-semibold text-gray-700">Déclarations CENTIF à venir</h2>
            <div class="space-y-1.5">
                @forelse ($declarationsAVenir as $declaration)
                    <div class="flex items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">
                        <div>
                            <span class="font-medium text-gray-900">{{ $declaration->periode }}</span>
                            <span class="text-gray-500"> — {{ number_format((float) $declaration->montant_cumule, 0, ',', ' ') }} XOF</span>
                        </div>
                        <form method="POST" action="{{ route('agent.conformite.declarations-centif.generer', $declaration) }}">
                            @csrf
                            <button type="submit" class="rounded-md bg-slate-800 px-2 py-1 text-xs font-semibold text-white">Générer le PDF</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucune déclaration à préparer.</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="mb-2 text-sm font-semibold text-gray-700">NPI en attente de vérification</h2>
            <div class="space-y-1.5">
                @forelse ($npiEnAttente as $client)
                    <a href="{{ route('agent.clients.completer', $client) }}" class="flex items-center justify-between rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm">
                        <span>{{ $client->nomAffichage() }}</span>
                        <span class="text-amber-700 text-xs font-semibold">En attente de connexion</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-400">Aucun NPI en attente de vérification.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
