@extends('layouts.agent')

@section('titre', 'Compléter la fiche')

@section('contenu')
    @php
        $manquants = $client->type->value === 'personne_morale'
            ? ($client->personneMorale?->champs_manquants ?? [])
            : ($client->personnePhysique?->champs_manquants ?? []);
    @endphp

    <div class="mb-4 rounded-lg border border-gray-200 bg-white p-3">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-900">Statut conformité</span>
            <span class="text-sm font-semibold {{ $client->statutConformiteAffichable() ? 'text-emerald-600' : 'text-amber-600' }}">
                {{ $client->statutConformiteAffichable() ? 'À jour' : 'Vérification complémentaire requise' }}
            </span>
        </div>
        @unless ($client->statutConformiteAffichable())
            <p class="mt-1 text-xs text-gray-500">Dossier transmis au responsable conformité.</p>
        @endunless
    </div>

    <div class="mb-4 rounded-lg border border-gray-200 bg-white p-3">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-900">Complétude</span>
            <span class="text-sm font-semibold {{ $client->score_completude_kyc >= 100 ? 'text-emerald-600' : 'text-amber-600' }}">
                {{ $client->score_completude_kyc }} %
            </span>
        </div>
        @if ($manquants !== [])
            <p class="mt-1 text-xs text-gray-500">
                À compléter : {{ collect($manquants)->map(fn ($c) => $champsKyc[$c]['libelle'] ?? $c)->implode(', ') }}
            </p>
        @endif
    </div>

    <div class="mb-4 rounded-lg border border-gray-200 bg-white p-3">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-900">Comptes</span>
            <form method="POST" action="{{ route('agent.comptes.stocker', $client) }}">
                @csrf
                <button type="submit" class="rounded-md bg-slate-800 px-2 py-1 text-xs font-semibold text-white">Ouvrir un compte</button>
            </form>
        </div>
        <ul class="mt-2 divide-y divide-gray-100 text-sm">
            @forelse ($client->comptes as $compte)
                <li class="flex items-center justify-between py-1.5">
                    <span>{{ $compte->numero }} — {{ $compte->statut->libelle() }}</span>
                    <a href="{{ route('agent.operations.creer') }}" class="text-xs text-emerald-700 hover:underline">Saisir une opération</a>
                </li>
            @empty
                <li class="py-1.5 text-gray-400">Aucun compte.</li>
            @endforelse
        </ul>
    </div>

    @if ($client->type->value === 'personne_physique')
        <form method="POST" action="{{ route('agent.clients.mettre-a-jour', $client) }}" class="space-y-3 rounded-lg border border-gray-200 bg-white p-4">
            @csrf @method('PUT')

            @foreach ($champsKyc as $code => $definition)
                @php $manquant = in_array($code, $manquants, true); @endphp
                <div class="{{ $manquant ? 'rounded-md bg-amber-50 p-2' : '' }}">
                    <label class="flex items-center gap-1 text-xs font-medium text-gray-600">
                        {{ $definition['libelle'] }}
                        <x-badge-source :source="$definition['source']" :reference="$definition['reference_texte']" />
                        @if ($definition['bloquant']) <span class="text-red-500">*</span> @endif
                    </label>

                    @if ($code === 'date_naissance')
                        {{-- Chiffré en texte libre (format AAAA-MM-JJ attendu par le moteur d'empreinte), pas un cast date. --}}
                        <input type="date" name="{{ $code }}" value="{{ old($code, $client->personnePhysique?->{$code}) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                    @elseif ($code === 'piece_identite_expiration')
                        <input type="date" name="{{ $code }}" value="{{ old($code, $client->personnePhysique?->{$code}?->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                    @elseif ($code === 'revenus_mensuels_estimes')
                        <input type="number" step="0.01" name="{{ $code }}" value="{{ old($code, $client->personnePhysique?->{$code}) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                    @else
                        <input type="text" name="{{ $code }}" value="{{ old($code, $client->personnePhysique?->{$code}) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                    @endif
                </div>
            @endforeach

            <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">
                Enregistrer
            </button>
        </form>
    @else
        <form method="POST" action="{{ route('agent.clients.mettre-a-jour', $client) }}" class="space-y-3 rounded-lg border border-gray-200 bg-white p-4">
            @csrf @method('PUT')

            <div>
                <label class="text-xs font-medium text-gray-600">Raison sociale</label>
                <input type="text" name="raison_sociale" value="{{ old('raison_sociale', $client->personneMorale?->raison_sociale) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 text-sm">
            </div>

            @foreach (['forme_juridique' => 'Forme juridique', 'rccm' => 'RCCM', 'ifu' => 'IFU'] as $code => $libelle)
                @php $manquant = in_array($code, $manquants, true); @endphp
                <div class="{{ $manquant ? 'rounded-md bg-amber-50 p-2' : '' }}">
                    <label class="text-xs font-medium text-gray-600">{{ $libelle }}</label>
                    <input type="text" name="{{ $code }}" value="{{ old($code, $client->personneMorale?->{$code}) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                </div>
            @endforeach

            <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">
                Enregistrer
            </button>
        </form>

        <div class="mt-4 rounded-lg border border-gray-200 bg-white p-4">
            <h2 class="text-sm font-medium text-gray-900">Signataires, mandataires, bénéficiaire effectif</h2>
            <p class="mt-1 text-xs text-gray-500">
                Chaque personne ajoutée ici est contrôlée individuellement contre les listes (problème 4).
                Aucune validation de la fiche tant qu'un signataire reste « à vérifier ».
            </p>

            <ul class="mt-3 divide-y divide-gray-100">
                @forelse ($client->personneMorale?->signataires ?? [] as $signataire)
                    <li class="flex items-center justify-between py-2 text-sm">
                        <div>
                            <span class="font-medium text-gray-900">{{ $signataire->nom }}</span>
                            <span class="text-gray-500">— {{ $signataire->role->libelle() }}</span>
                            @if ($signataire->pourcentage_detention)
                                <span class="text-gray-500">({{ $signataire->pourcentage_detention }} %)</span>
                            @endif
                            @unless (auth('agent')->user()->estGuichet())
                                <x-badge-statut-filtrage :statut="$signataire->statut_filtrage" />
                            @endunless
                        </div>
                        <form method="POST" action="{{ route('agent.clients.signataires.detruire', $signataire) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Retirer</button>
                        </form>
                    </li>
                @empty
                    <li class="py-2 text-sm text-gray-400">Aucun signataire enregistré.</li>
                @endforelse
            </ul>

            <form method="POST" action="{{ route('agent.clients.signataires.stocker', $client->personneMorale) }}" class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-4">
                @csrf
                <input type="text" name="nom" placeholder="Nom complet" required class="rounded-md border-gray-300 text-sm sm:col-span-2">
                <select name="role" class="rounded-md border-gray-300 text-sm">
                    <option value="signataire">Signataire</option>
                    <option value="beneficiaire_effectif">Bénéficiaire effectif</option>
                    <option value="mandataire">Mandataire</option>
                </select>
                <input type="number" step="0.01" name="pourcentage_detention" placeholder="% détention" class="rounded-md border-gray-300 text-sm">
                <button type="submit" class="sm:col-span-4 rounded-md bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white">
                    Ajouter
                </button>
            </form>
        </div>
    @endif
@endsection
