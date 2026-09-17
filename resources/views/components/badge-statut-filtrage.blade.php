@props(['statut'])

@php
    $valeur = $statut instanceof \App\Enums\StatutFiltrage ? $statut->value : $statut;
    $libelle = \App\Enums\StatutFiltrage::from($valeur)->libelle();
    $couleurs = match ($valeur) {
        'a_verifier' => 'bg-amber-100 text-amber-800',
        'confirme' => 'bg-red-100 text-red-800',
        'ecarte' => 'bg-gray-100 text-gray-600',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp

<span class="ml-1 inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium {{ $couleurs }}">
    {{ $libelle }}
</span>
