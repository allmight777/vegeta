@props(['source', 'reference' => null])

{{--
    Source d'une valeur (CLAUDE.md §3) sur l'écran de saisie : une petite icône ⓘ qui affiche la
    source au survol, au clic ou au focus, au lieu d'un badge en toutes lettres par champ. Aucune
    information supprimée : `title` la porte aussi (accessibilité, tactile). Jamais la mention
    « à confirmer » ici (note interne) ; l'écran admin des règles affiche la source complète.
--}}
@php
    $valeur = $source instanceof \App\Enums\SourceValeur ? $source->value : $source;
    $libelle = \App\Enums\SourceValeur::from($valeur)->libelleCourt();
    $propre = fn (?string $t) => trim(preg_replace('/\s*[—·-]?\s*à confirmer[^.]*/iu', '', (string) $t), " \t—·-");
    $texte = $propre($libelle).($propre($reference) !== '' ? ' · '.$propre($reference) : '');
@endphp

@once
    <style>
        .source-info { position: relative; display: inline-flex; align-items: center; cursor: help; color: #94A3B8; font-size: .72rem; line-height: 1; }
        .source-info:hover, .source-info:focus-visible { color: #2C343D; outline: none; }
        .source-info-bulle {
            position: absolute; z-index: 40; left: 50%; bottom: calc(100% + 6px); transform: translateX(-50%);
            width: max-content; max-width: 240px; padding: 7px 10px; border-radius: 9px;
            background: #2C343D; color: #fff; font-size: .7rem; font-weight: 500; line-height: 1.35;
            text-transform: none; letter-spacing: 0; text-align: left; box-shadow: 0 8px 22px rgba(44,52,61,.25);
        }
        .source-info-bulle::after { content: ''; position: absolute; top: 100%; left: var(--fleche, 50%); margin-left: -5px; border: 5px solid transparent; border-top-color: #2C343D; }
    </style>
@endonce

<span class="source-info" role="button" tabindex="0" title="Source : {{ $texte }}" aria-label="Source : {{ $texte }}"
    x-data="{
        o: false,
        ouvrir() {
            this.o = true;
            this.$nextTick(() => {
                const b = this.$refs.bulle;
                b.style.marginLeft = '0px';
                b.style.removeProperty('--fleche');
                const boite = (this.$el.closest('.fiche-groupe') ?? document.body).getBoundingClientRect();
                const r = b.getBoundingClientRect();
                let decalage = 0;
                if (r.left < boite.left + 8) { decalage = boite.left + 8 - r.left; }
                else if (r.right > boite.right - 8) { decalage = boite.right - 8 - r.right; }
                b.style.marginLeft = decalage + 'px';
                b.style.setProperty('--fleche', 'calc(50% - ' + decalage + 'px)');
            });
        }
    }"
    @mouseenter="ouvrir()" @mouseleave="o = false" @focus="ouvrir()" @blur="o = false"
    @click.prevent.stop="o ? o = false : ouvrir()" @keydown.escape="o = false" @click.outside="o = false">
    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
    <span class="source-info-bulle" x-ref="bulle" x-show="o" x-cloak>Source : {{ $texte }}</span>
</span>
