@extends('layouts.admin')

@section('titre', 'Règles de détection')

@section('contenu')
    <div class="space-y-3">
        @foreach ($regles as $regle)
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-mono text-sm font-semibold text-gray-900">{{ $regle->code }}</span>
                        <span class="ml-2 text-sm text-gray-600">{{ $regle->libelle }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-badge-source :source="$regle->source" :reference="$regle->reference_texte" />
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-medium {{ $regle->actif ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $regle->actif ? 'Active' : 'Désactivée' }}
                        </span>
                    </div>
                </div>
                <dl class="mt-2 grid grid-cols-2 gap-2 text-xs text-gray-600 sm:grid-cols-4">
                    @foreach ($regle->parametres as $cle => $valeur)
                        <div>
                            <dt class="font-medium text-gray-400">{{ $cle }}</dt>
                            <dd>{{ is_numeric($valeur) ? number_format((float) $valeur, 0, ',', ' ') : $valeur }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endforeach
    </div>
@endsection
