@props(['name', 'id', 'definition', 'value' => null, 'confiance' => null, 'manquant' => false])

@php
    $typeSaisie = $definition['type_saisie'] ?? 'text';
    $saisissable = $definition['saisissable'] ?? true;
@endphp

<div class="champ-fiche {{ $manquant ? 'champ-manquant' : '' }}">
    <label for="{{ $id }}" class="champ-fiche-label">
        {{ $definition['libelle'] }}
        @if ($definition['bloquant'] ?? false)
            <span class="champ-obligatoire" title="Obligatoire">*</span>
        @endif
        @isset($definition['source'])
            <x-badge-source :source="$definition['source']" :reference="$definition['reference_texte'] ?? null" />
        @endisset
        @if ($confiance !== null)
            <span class="champ-confiance {{ $confiance >= 0.8 ? 'champ-confiance-haute' : 'champ-confiance-basse' }}">
                {{ (int) round($confiance * 100) }} %
            </span>
        @endif
    </label>

    @if ($typeSaisie === 'select')
        <select name="{{ $name }}" id="{{ $id }}" class="champ-fiche-input" @disabled(!$saisissable)>
            <option value="">—</option>
            @foreach ($definition['options'] ?? [] as $optionValeur => $optionLibelle)
                <option value="{{ $optionValeur }}" @selected((string) $value === (string) $optionValeur)>{{ $optionLibelle }}</option>
            @endforeach
        </select>
    @elseif ($typeSaisie === 'checkbox')
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" id="{{ $id }}" value="1"
            @checked((bool) $value)>
    @elseif ($typeSaisie === 'file')
        <input type="file" name="{{ $name }}" id="{{ $id }}" class="champ-fiche-input">
    @elseif ($typeSaisie === 'npi')
        <div class="champ-npi">
            <input type="text" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}"
                class="champ-fiche-input" x-data x-on:blur="await window.verifierNpi($event.target)">
            <span class="champ-npi-statut" data-npi-statut></span>
        </div>
    @elseif ($typeSaisie === 'date')
        <input type="date" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}"
            class="champ-fiche-input" @disabled(!$saisissable)>
    @elseif ($typeSaisie === 'number')
        <input type="number" step="0.01" name="{{ $name }}" id="{{ $id }}"
            value="{{ $value }}" class="champ-fiche-input" @disabled(!$saisissable)>
    @else
        <input type="text" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}"
            class="champ-fiche-input" @disabled(!$saisissable)>
    @endif
</div>
