@props(['gravite'])

@php
    $valeur = $gravite instanceof \App\Enums\GraviteAlerte ? $gravite->value : $gravite;
    $libelle = \App\Enums\GraviteAlerte::from($valeur)->libelle();
    $couleurs = match ($valeur) {
        'critique' => 'bg-red-100 text-red-800',
        'attention' => 'bg-amber-100 text-amber-800',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp

<span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $couleurs }}">
    {{ $libelle }}
</span>
