@extends('layouts.agent')

@section('titre', 'Correspondances à vérifier')

@section('contenu')
    <div class="space-y-3">
        @forelse ($resultats as $resultat)
            @php
                $cible = $resultat->filtrable;
                $nomCible = $cible instanceof \App\Models\Signataire ? $cible->nom : $cible->nomAffichage();
            @endphp
            <div class="rounded-lg border border-gray-200 bg-white p-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-900">{{ $nomCible }}</span>
                    <span class="text-xs font-semibold text-amber-700">{{ round($resultat->score_similarite * 100) }} %</span>
                </div>
                <p class="mt-1 text-xs text-gray-600">
                    Liste : {{ $resultat->entreeListe->source->libelle() }} — {{ $resultat->entreeListe->nom }}
                    ({{ $resultat->entreeListe->categorie ?? 'sans catégorie' }})
                </p>

                <form method="POST" action="{{ route('agent.filtrage.decider', $resultat) }}" class="mt-3 space-y-2">
                    @csrf @method('PUT')
                    <textarea name="motif" required minlength="5" rows="2" placeholder="Justification (obligatoire)"
                              class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                    <div class="flex gap-2">
                        <button type="submit" name="statut" value="confirme" class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white">
                            Confirmer la correspondance
                        </button>
                        <button type="submit" name="statut" value="ecarte" class="rounded-md bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800">
                            Faux positif
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucune correspondance en attente de vérification.</p>
        @endforelse
    </div>
@endsection
