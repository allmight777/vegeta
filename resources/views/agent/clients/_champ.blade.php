@props(['name', 'id', 'definition', 'value' => null, 'confiance' => null, 'manquant' => false])

@php
    $typeSaisie = $definition['type_saisie'] ?? 'text';
    $saisissable = $definition['saisissable'] ?? true;
    // Copilote de saisie (16_PROMPT §2) : contrôles déclenchés au blur de ces champs seulement.
    $copilote = in_array($id, [
        'nom', 'prenoms', 'date_naissance', 'profession', 'activite_1', 'activite_2',
        'revenus_mensuels_estimes', 'depot_especes', 'piece_identite_expiration',
    ], true);
    // Propositions pendant la frappe (17_PROMPT §1) : champs de texte libre normalisables.
    $autocompletion = in_array($id, ['profession', 'activite_1', 'activite_2', 'employeur', 'nationalite'], true);
    $attributsCopilote = $copilote ? 'x-data x-on:blur=window.copiloteBlur($event.target)' : '';
    if ($autocompletion) {
        $attributsCopilote .= ' autocomplete=off x-on:input=window.copiloteFrappe($event.target) x-on:keydown=window.copiloteClavier($event)';
    }
@endphp

@php($enErreur = $errors->has($name))
<div class="champ-fiche {{ $manquant ? 'champ-manquant' : '' }} {{ $enErreur ? 'champ-erreur' : '' }}"
    @if ($definition['obligatoire'] ?? false) data-obligatoire="1" data-libelle="{{ $definition['libelle'] }}" @endif>
    <label for="{{ $id }}" class="champ-fiche-label">
        {{ $definition['libelle'] }}
        @if ($definition['bloquant'] ?? false)
            <span class="champ-obligatoire" title="Obligatoire">*</span>
        @endif
        @isset($definition['source'])
            <x-source-info :source="$definition['source']" :reference="$definition['reference_texte'] ?? null" />
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
    @elseif ($typeSaisie === 'telephone')
        <div class="champ-npi">
            <input type="text" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}"
                class="champ-fiche-input" x-data
                x-on:blur="await Promise.all([window.verifierTelephone($event.target), window.verifierSimulationDepot($event.target)])">
            <span class="champ-npi-statut" data-telephone-statut></span>
        </div>
    @elseif ($typeSaisie === 'date')
        <input type="date" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}"
            class="champ-fiche-input" @disabled(!$saisissable) {!! $attributsCopilote !!}>
    @elseif ($typeSaisie === 'number')
        <input type="number" step="0.01" name="{{ $name }}" id="{{ $id }}"
            value="{{ $value }}" class="champ-fiche-input" @disabled(!$saisissable) {!! $attributsCopilote !!}>
    @elseif ($autocompletion)
        <div class="champ-autocompletion">
            <input type="text" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}"
                class="champ-fiche-input" @disabled(!$saisissable) {!! $attributsCopilote !!}
                role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="propositions-{{ $id }}">
            <ul class="copilote-liste" id="propositions-{{ $id }}" data-copilote-liste="{{ $id }}" role="listbox" hidden></ul>
        </div>
    @else
        <input type="text" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}"
            class="champ-fiche-input" @disabled(!$saisissable) {!! $attributsCopilote !!}>
    @endif

    @if ($copilote)
        {{-- Avertissements du copilote (doublon, incohérence, suggestion) : vide et masqué tant qu'il n'y a rien à dire. --}}
        <div class="champ-copilote" data-copilote-cible="{{ $id }}" role="status" aria-live="polite"></div>
    @endif

    @if ($enErreur)
        <p class="champ-erreur-message" role="alert">{{ $errors->first($name) }}</p>
    @endif
</div>
