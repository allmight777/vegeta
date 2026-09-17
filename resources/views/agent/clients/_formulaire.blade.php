@props([
'type',
'valeurs' => [],
'confiances' => [],
'champsManquants' => [],
'repetables' => [],
'natureRelation' => null,
'client' => null,
'ficheRlbcft' => [],
'fichesRlbcftSignataires' => [],
'signatairesExistants' => null,
])

@php
$referentiel = app(\App\Services\Kyc\ReferentielFicheAdhesion::class);
$groupes = $referentiel->groupes($type);
$agent = auth('agent')->user();
$voitRlbcft = $agent && $agent->estResponsableLbcft();
$enCompletion = $client !== null;
@endphp

@once <style>
/* =========================================
BOUTONS DES CHAMPS REPETABLES
========================================= */

    .fiche-repetable-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-top: 14px;
    }

    /* =========================================
       BOUTON RETIRER
       ========================================= */

    .fiche-repetable-retirer {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;

        padding: 8px 13px;

        border: 1px solid #fecaca;
        border-radius: 8px;

        background: #fff7f7;
        color: #b91c1c;

        font-size: 0.76rem;
        font-weight: 600;
        line-height: 1;

        cursor: pointer;

        transition:
            background 0.2s ease,
            border-color 0.2s ease,
            color 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;

        box-shadow: 0 1px 2px rgba(127, 29, 29, 0.04);
    }

    .fiche-repetable-retirer::before {
        content: "×";

        display: inline-flex;
        align-items: center;
        justify-content: center;

        width: 17px;
        height: 17px;

        border-radius: 50%;

        background: #fee2e2;
        color: #b91c1c;

        font-size: 13px;
        font-weight: 700;

        transition: all 0.2s ease;
    }

    .fiche-repetable-retirer:hover {
        background: #fef2f2;
        border-color: #fca5a5;
        color: #991b1b;

        transform: translateY(-1px);

        box-shadow: 0 4px 10px rgba(127, 29, 29, 0.08);
    }

    .fiche-repetable-retirer:hover::before {
        background: #fecaca;
        color: #991b1b;
    }

    .fiche-repetable-retirer:active {
        transform: translateY(0);
    }

    /* =========================================
       BOUTON AJOUTER
       ========================================= */

    .fiche-ajouter-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .fiche-ajouter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;

        min-height: 42px;
        padding: 10px 18px;

        border: 1px solid #70ae48;
        border-radius: 10px;

        background: #70ae48;
        color: #ffffff;

        font-size: 0.82rem;
        font-weight: 700;
        line-height: 1;

        cursor: pointer;

        transition:
            background 0.2s ease,
            border-color 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;

        box-shadow: 0 4px 10px rgba(112, 174, 72, 0.18);
    }

    .fiche-ajouter::before {
        content: "+";

        display: inline-flex;
        align-items: center;
        justify-content: center;

        width: 21px;
        height: 21px;

        border-radius: 50%;

        background: rgba(255, 255, 255, 0.18);
        color: #ffffff;

        font-size: 16px;
        font-weight: 500;
    }

    .fiche-ajouter:hover {
        background: #619d3b;
        border-color: #619d3b;

        transform: translateY(-2px);

        box-shadow: 0 7px 16px rgba(112, 174, 72, 0.25);
    }

    .fiche-ajouter:active {
        transform: translateY(0);

        box-shadow: 0 3px 7px rgba(112, 174, 72, 0.18);
    }

    /* =========================================
       FOCUS ACCESSIBLE
       ========================================= */

    .fiche-ajouter:focus-visible {
        outline: 3px solid rgba(112, 174, 72, 0.22);
        outline-offset: 2px;
    }

    .fiche-repetable-retirer:focus-visible {
        outline: 3px solid rgba(185, 28, 28, 0.15);
        outline-offset: 2px;
    }

    /* =========================================
       ELEMENT REPETABLE
       ========================================= */

    .fiche-repetable-item {
        position: relative;

        padding: 18px;
        margin-bottom: 14px;

        border: 1px solid #e5e7eb;
        border-radius: 12px;

        background: #ffffff;

        box-shadow: 0 2px 7px rgba(15, 23, 42, 0.04);
    }

    .fiche-repetable-item > .fiche-repetable-retirer {
        position: absolute;
        top: 12px;
        right: 12px;
    }

    /* =========================================
       ESPACEMENT LIEN DE PARENTÉ
       ========================================= */

    .fiche-repetable-item .champ-fiche:has(
        .champ-fiche-label
    ) {
        margin-top: 0;
    }

    /*
     * Le champ "Lien de parenté" est décollé
     * de l'élément précédent.
     */
    .fiche-repetable-item .champ-fiche-label {
        display: block;
    }

    .fiche-repetable-item .champ-fiche + .champ-fiche {
        margin-top: 16px;
    }

    /*
     * Sur les écrans où le libellé exact est
     * "Lien de parenté", on renforce légèrement
     * l'espacement.
     */
    .fiche-repetable-item .champ-fiche:has(
        .champ-fiche-label
    ) {
        scroll-margin-top: 20px;
    }
</style>

<script>
    window.verifierNpi = async function (input) {
        const statut = input.closest('.champ-npi')?.querySelector('[data-npi-statut]');
        const npi = input.value.trim();

        if (!npi) {
            if (statut) {
                statut.innerHTML = '';
            }
            return;
        }

        if (statut) {
            statut.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        }

        try {
            const reponse = await fetch(
                @json(route('agent.clients.npi.verifier')),
                {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.content ?? '',
                        'Accept': 'application/json',
                    },

                    body: JSON.stringify({
                        npi
                    }),
                }
            );

            const donnees = await reponse.json();

            if (statut) {
                statut.innerHTML = donnees.est_valide
                    ? '<i class="fa-solid fa-circle-check" style="color:var(--green)"></i>'
                    : '<i class="fa-solid fa-circle-xmark" style="color:var(--danger)"></i>';

                statut.title = donnees.avertissement_nom ?? '';
            }

        } catch (e) {

            if (statut) {
                statut.innerHTML =
                    '<i class="fa-solid fa-triangle-exclamation"></i>';
            }
        }
    };
</script>


@endonce

<div class="fiche-formulaire">


@foreach ($groupes as $groupeCode => $groupe)

    @continue($groupeCode === 'fiche_rlbcft')

    {{-- En complétion, mandataires/signataires se gèrent un par un via des routes
         dédiées (ajout/retrait immédiat, filtrage à chaque ajout) — pas via ce
         tableau répétable qui ne vaut que pour une création groupée. --}}

    @continue(($groupe['repetable'] ?? false) && $enCompletion)


    <details class="fiche-groupe" open>

        <summary>
            {{ $groupe['libelle'] }}
        </summary>


        @if ($groupe['repetable'] ?? false)

            @php
                $lignesInitiales = $repetables[$groupeCode] ?? [];

                if ($lignesInitiales === []) {
                    $lignesInitiales = array_fill(
                        0,
                        max((int) ($groupe['min'] ?? 0), 1),
                        []
                    );
                }
            @endphp


            <div
                class="fiche-groupe-corps"
                style="display: block;"

                x-data="{
                    lignes: {{ Illuminate\Support\Js::from(array_values($lignesInitiales)) }},

                    min: {{ (int) ($groupe['min'] ?? 0) }},

                    max: {{ (int) ($groupe['max'] ?? 3) }},

                    ajouter() {
                        if (this.lignes.length < this.max) {
                            this.lignes.push({});
                        }
                    },

                    retirer(i) {
                        if (this.lignes.length > this.min) {
                            this.lignes.splice(i, 1);
                        }
                    },
                }"
            >


                <template
                    x-for="(ligne, i) in lignes"
                    :key="i"
                >

                    <div class="fiche-repetable-item">


                        {{-- BOUTON RETIRER --}}

                        <button
                            type="button"
                            class="fiche-repetable-retirer"

                            x-show="lignes.length > min"

                            @click="retirer(i)"

                            title="Retirer cette ligne"
                        >
                            Retirer
                        </button>


                        @foreach ($groupe['champs'] as $code => $definition)

                            <div class="champ-fiche">

                                <label class="champ-fiche-label">
                                    {{ $definition['libelle'] }}
                                </label>


                                @if (($definition['type_saisie'] ?? 'text') === 'select')

                                    <select
                                        :name="`{{ $groupeCode }}[${i}][{{ $code }}]`"

                                        class="champ-fiche-input"

                                        x-model="ligne.{{ $code }}"
                                    >

                                        <option value="">
                                            —
                                        </option>

                                        @foreach (($definition['options'] ?? []) as $optionValeur => $optionLibelle)

                                            <option
                                                value="{{ $optionValeur }}"
                                            >
                                                {{ $optionLibelle }}
                                            </option>

                                        @endforeach

                                    </select>


                                @elseif (($definition['type_saisie'] ?? 'text') === 'file')

                                    <input
                                        type="file"

                                        :name="`{{ $groupeCode }}[${i}][{{ $code }}]`"

                                        class="champ-fiche-input"
                                    >


                                @elseif (($definition['type_saisie'] ?? 'text') === 'npi')

                                    <div class="champ-npi">

                                        <input
                                            type="text"

                                            :name="`{{ $groupeCode }}[${i}][{{ $code }}]`"

                                            class="champ-fiche-input"

                                            x-model="ligne.{{ $code }}"

                                            x-on:blur="await window.verifierNpi($event.target)"
                                        >

                                        <span
                                            class="champ-npi-statut"
                                            data-npi-statut
                                        ></span>

                                    </div>


                                @elseif (($definition['type_saisie'] ?? 'text') === 'date')

                                    <input
                                        type="date"

                                        :name="`{{ $groupeCode }}[${i}][{{ $code }}]`"

                                        class="champ-fiche-input"

                                        x-model="ligne.{{ $code }}"
                                    >


                                @elseif (($definition['type_saisie'] ?? 'text') === 'number')

                                    <input
                                        type="number"

                                        step="0.01"

                                        :name="`{{ $groupeCode }}[${i}][{{ $code }}]`"

                                        class="champ-fiche-input"

                                        x-model="ligne.{{ $code }}"
                                    >


                                @else

                                    <input
                                        type="text"

                                        :name="`{{ $groupeCode }}[${i}][{{ $code }}]`"

                                        class="champ-fiche-input"

                                        x-model="ligne.{{ $code }}"
                                    >

                                @endif

                            </div>

                        @endforeach

                    </div>

                </template>


                {{-- BOUTON AJOUTER --}}

                <div class="fiche-ajouter-wrapper">

                    <button
                        type="button"

                        class="fiche-ajouter"

                        x-show="lignes.length < max"

                        @click="ajouter()"
                    >
                        Ajouter ({{ $groupe['libelle'] }})
                    </button>

                </div>

            </div>


        @else

            <div class="fiche-groupe-corps">

                @foreach ($groupe['champs'] as $code => $definition)

                    @continue(
                        ($definition['contexte'] ?? null) === 'completion_uniquement'
                        && ! $enCompletion
                    )


                    @if (
                        $code === 'beneficiaire_effectif_signataire_id'
                        && $signatairesExistants
                    )

                        <div class="champ-fiche">

                            <label
                                for="beneficiaire_effectif_signataire_id"

                                class="champ-fiche-label"
                            >
                                {{ $definition['libelle'] }}
                            </label>


                            <select
                                name="beneficiaire_effectif_signataire_id"

                                id="beneficiaire_effectif_signataire_id"

                                class="champ-fiche-input"
                            >

                                <option value="">
                                    —
                                </option>


                                @foreach ($signatairesExistants as $signataire)

                                    <option
                                        value="{{ $signataire->id }}"

                                        @selected(
                                            ($valeurs['beneficiaire_effectif_signataire_id'] ?? null)
                                            === $signataire->id
                                        )
                                    >
                                        {{ $signataire->nom }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                    @else

                        @include('agent.clients._champ', [
                            'name' => $code,
                            'id' => $code,
                            'definition' => $definition,
                            'value' => $valeurs[$code] ?? null,
                            'confiance' => $confiances[$code] ?? null,
                            'manquant' => in_array($code, $champsManquants, true),
                        ])

                    @endif

                @endforeach

            </div>

        @endif

    </details>

@endforeach


{{-- Pour une personne morale, la fiche RLBC/FT est toujours "par signataire" :
     elle n'a de sens qu'une fois des signataires réellement persistés
     (écran de complétion), jamais à la création où ils n'ont pas encore d'identifiant. --}}

@if (
    $voitRlbcft &&
    ($type === 'personne_physique' || $signatairesExistants)
)

    @php
        $groupeRlbcft = $referentiel->groupeFicheRlbcft($type);
    @endphp


    @if ($groupeRlbcft)

        <details
            class="fiche-groupe"
            open
        >

            <summary>
                {{ $groupeRlbcft['libelle'] }}
            </summary>


            <div
                class="fiche-groupe-corps"
                style="display: block;"
            >


                @if (
                    ($groupeRlbcft['par_signataire'] ?? false)
                    && $signatairesExistants
                )

                    @forelse ($signatairesExistants as $signataire)

                        <div class="fiche-repetable-item">

                            <p
                                style="
                                    grid-column: 1 / -1;
                                    font-weight: 700;
                                    font-size: 0.75rem;
                                    color: var(--dark);
                                    margin: 0 0 14px;
                                "
                            >
                                {{ $signataire->nom }}
                                —
                                {{ $signataire->fonction ?? $signataire->role->libelle() }}
                            </p>


                            @foreach ($groupeRlbcft['champs'] as $code => $definition)

                                @include('agent.clients._champ', [
                                    'name' => "fiche_rlbcft[{$signataire->id}][{$code}]",
                                    'id' => "fiche_rlbcft_{$signataire->id}_{$code}",
                                    'definition' => $definition,
                                    'value' => $fichesRlbcftSignataires[$signataire->id][$code] ?? null,
                                ])

                            @endforeach

                        </div>


                    @empty

                        <p class="text-muted">
                            Aucun signataire à contrôler pour l'instant.
                        </p>

                    @endforelse


                @else

                    <div class="fiche-groupe-corps">

                        @foreach ($groupeRlbcft['champs'] as $code => $definition)

                            @include('agent.clients._champ', [
                                'name' => "fiche_rlbcft[{$code}]",
                                'id' => "fiche_rlbcft_{$code}",
                                'definition' => $definition,
                                'value' => $ficheRlbcft[$code] ?? null,
                            ])

                        @endforeach

                    </div>

                @endif

            </div>

        </details>

    @endif

@endif


</div>
