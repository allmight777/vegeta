@props(['texte', 'apercu' => false])

{{--
    Bouton « Expliquer » (16_PROMPT §3.3) : affiche en ligne, d'un clic, l'explication déjà
    produite par Services\Explication\GenerateurExplication (gabarits déterministes, aucun
    appel réseau). `apercu` = le texte est déjà visible, tronqué à deux lignes, et le bouton
    le déplie ; sinon il n'apparaît qu'au clic.
--}}
@once
    <style>
        .expliquer { margin: 6px 0; }
        .expliquer-texte { margin: 6px 0 0; font-size: .82rem; line-height: 1.5; color: #1E293B; }
        .expliquer-texte.replie { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .expliquer-btn {
            display: inline-flex; align-items: center; gap: 6px; margin-top: 6px; padding: 5px 11px;
            border: 1px solid #E7EBEF; border-radius: 999px; background: #fff; color: #2C343D;
            font: inherit; font-size: .74rem; font-weight: 700; cursor: pointer;
        }
        .expliquer-btn:hover { background: #F8FAFC; }
        .expliquer-source { display: block; margin-top: 2px; font-size: .68rem; color: #64748B; font-style: italic; }
    </style>
@endonce

<div class="expliquer" x-data="{ ouvert: false }">
    @if ($apercu)
        <p class="expliquer-texte" :class="{ 'replie': !ouvert }">{{ $texte }}</p>
    @else
        <p class="expliquer-texte" x-show="ouvert" x-cloak>
            {{ $texte }}
            <span class="expliquer-source">Explication locale — texte généré par des gabarits fixes, sans IA en ligne.</span>
        </p>
    @endif

    <button type="button" class="expliquer-btn" @click="ouvert = !ouvert" :aria-expanded="ouvert.toString()">
        <span x-text="ouvert ? 'Réduire' : 'Expliquer'">Expliquer</span>
    </button>
</div>
