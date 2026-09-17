@props(['source', 'reference' => null])

@php
    $valeur = $source instanceof \App\Enums\SourceValeur ? $source->value : $source;
    $libelle = \App\Enums\SourceValeur::from($valeur)->libelle();
    $couleurs = match ($valeur) {
        'reglementaire' => 'bg-emerald-50 text-emerald-700',
        'briefing_cif' => 'bg-amber-50 text-amber-700',
        'politique_interne' => 'bg-blue-50 text-blue-700',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp

<span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium {{ $couleurs }}" title="{{ $reference }}">
    {{ $libelle }}{{ $reference ? ' · '.$reference : '' }}
</span>
