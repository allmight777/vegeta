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
$voitRlbcft = $agent && $agent->estResponsableAgence();
$enCompletion = $client !== null;
@endphp

@once <style>

    /* Copilote de saisie (16_PROMPT §2) */
    /* Propositions pendant la frappe (17_PROMPT §1) */
    .champ-autocompletion { position: relative; }
    .copilote-liste {
        position: absolute; z-index: 30; left: 0; right: 0; top: calc(100% + 4px); margin: 0; padding: 4px; list-style: none;
        background: #fff; border: 1px solid #E7EBEF; border-radius: 12px; box-shadow: 0 12px 30px rgba(44, 52, 61, .14);
        max-height: 240px; overflow-y: auto;
    }
    .copilote-liste[hidden] { display: none; }
    .copilote-liste li {
        display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 8px 10px; border-radius: 8px;
        font-size: .82rem; color: #1E293B; cursor: pointer;
    }
    .copilote-liste li small { color: #64748B; font-size: .68rem; white-space: nowrap; }
    .copilote-liste li:hover, .copilote-liste li.actif { background: #FFFBD6; }

    .champ-copilote:empty { display: none; }
    .copilote-note {
        margin-top: 6px; padding: 8px 10px; border-radius: 10px; font-size: .78rem; line-height: 1.4;
        display: flex; flex-wrap: wrap; align-items: center; gap: 4px 10px;
        background: #FFFBD6; border: 1px solid #F0E535; color: #5B5200;
    }
    .copilote-note + .copilote-note { margin-top: 4px; }
    .copilote-source { margin-left: auto; font-size: .68rem; opacity: .75; font-style: italic; }
    .copilote-action {
        border: 0; border-radius: 8px; background: #2C343D; color: #fff;
        padding: 4px 10px; font: inherit; font-weight: 700; cursor: pointer;
    }
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

    window.__clientIdActuel = @json($client?->id);

    window.__nomSaisiActuel = function () {
        const nom = document.getElementById('nom')?.value?.trim() ?? '';
        const prenoms = document.getElementById('prenoms')?.value?.trim() ?? '';
        const raisonSociale = document.getElementById('raison_sociale')?.value?.trim() ?? '';

        return [prenoms, nom].filter(Boolean).join(' ').trim() || raisonSociale || null;
    };

    window.verifierTelephone = async function (input) {
        const statut = input.closest('.champ-npi')?.querySelector('[data-telephone-statut]');
        const telephone = input.value.trim();

        if (!telephone) {
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
                @json(route('agent.clients.telephone.verifier')),
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
                        telephone,
                        nom_saisi: window.__nomSaisiActuel(),
                        client_id_actuel: window.__clientIdActuel,
                    }),
                }
            );

            const donnees = await reponse.json();

            if (statut) {
                if (donnees.avertissement_nom) {
                    statut.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:var(--danger)"></i>';
                } else if (donnees.deja_enregistre) {
                    statut.innerHTML = '<i class="fa-solid fa-circle-info" style="color:var(--dark)"></i>';
                } else {
                    statut.innerHTML = '<i class="fa-solid fa-circle-check" style="color:var(--green)"></i>';
                }

                statut.title = donnees.avertissement_nom
                    ?? (donnees.deja_enregistre ? 'Ce numéro est déjà enregistré pour ce client dans le réseau.' : '');
            }

        } catch (e) {

            if (statut) {
                statut.innerHTML =
                    '<i class="fa-solid fa-triangle-exclamation"></i>';
            }
        }
    };

    window.__resultatSimulationDepot = null;

    // Simulation de dépôt mobile money (annuaire synthétique, jamais un vrai appel
    // USSD/API opérateur — docs/DECISIONS.md). Ne change aucune icône existante : le
    // résultat est simplement mis en cache pour l'écran récapitulatif avant confirmation.
    window.verifierSimulationDepot = async function (input) {
        const telephone = input.value.trim();

        if (!telephone) {
            window.__resultatSimulationDepot = null;
            return;
        }

        try {
            const reponse = await fetch(
                @json(route('agent.clients.simulation-depot.verifier')),
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

                    body: JSON.stringify({ telephone }),
                }
            );

            window.__resultatSimulationDepot = await reponse.json();
        } catch (e) {
            window.__resultatSimulationDepot = null;
        }
    };
    // ---------------------------------------------------------------------------
    // Copilote de saisie (16_PROMPT §2) — au blur uniquement, jamais à la frappe.
    // Non bloquant : une réponse en erreur ou hors ligne n'affiche simplement rien.
    // Le DOM est construit avec textContent (aucune chaîne serveur injectée en HTML).
    // ---------------------------------------------------------------------------
    window.copiloteAppeler = async function (url, corps) {
        try {
            const reponse = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify(corps),
            });

            return reponse.ok ? await reponse.json() : null;
        } catch (e) {
            return null;
        }
    };

    window.copiloteEffacer = function (scope, genre, champ) {
        scope.querySelectorAll('.copilote-note[data-genre="' + genre + '"]' + (champ ? '[data-champ="' + champ + '"]' : ''))
            .forEach((noeud) => noeud.remove());
    };

    window.copiloteAfficher = function (scope, champ, genre, message, source, action) {
        const cible = scope.querySelector('[data-copilote-cible="' + champ + '"]');

        if (!cible) {
            return;
        }

        const note = document.createElement('div');
        note.className = 'copilote-note';
        note.dataset.genre = genre;
        note.dataset.champ = champ;

        const texte = document.createElement('span');
        texte.textContent = message;
        note.appendChild(texte);

        if (action) {
            const bouton = document.createElement('button');
            bouton.type = 'button';
            bouton.className = 'copilote-action';
            bouton.textContent = action.libelle;
            bouton.addEventListener('click', () => { action.executer(); note.remove(); });
            note.appendChild(bouton);
        }

        if (source) {
            const mention = document.createElement('span');
            mention.className = 'copilote-source';
            mention.textContent = source;
            note.appendChild(mention);
        }

        cible.appendChild(note);
    };

    window.copiloteValeur = function (scope, id) {
        return scope.querySelector('#' + id)?.value?.trim() ?? '';
    };

    window.copiloteDoublon = async function (scope) {
        if (!scope.querySelector('#nom')) {
            return;
        }

        const nom = window.copiloteValeur(scope, 'nom');
        window.copiloteEffacer(scope, 'doublon');

        if (nom.length < 2) {
            return;
        }

        const donnees = await window.copiloteAppeler(@json(route('agent.clients.copilote.doublon')), {
            nom,
            prenoms: window.copiloteValeur(scope, 'prenoms') || null,
            date_naissance: window.copiloteValeur(scope, 'date_naissance') || null,
            client_id_actuel: window.__clientIdActuel ?? null,
        });

        window.copiloteEffacer(scope, 'doublon');

        if (donnees && donnees.doublon) {
            window.copiloteAfficher(scope, 'nom', 'doublon', donnees.message, 'contrôle local (empreinte)', null);
        }
    };

    window.copiloteCoherence = async function (scope) {
        const naissance = window.copiloteValeur(scope, 'date_naissance');
        const revenus = parseFloat(window.copiloteValeur(scope, 'revenus_mensuels_estimes'));
        const depot = parseFloat(window.copiloteValeur(scope, 'depot_especes'));
        const expiration = window.copiloteValeur(scope, 'piece_identite_expiration');
        const profession = window.copiloteValeur(scope, 'profession');

        // Caractéristiques dérivées calculées ici : la date brute de naissance et l'expiration ne partent pas.
        let age = null;
        if (naissance && !isNaN(Date.parse(naissance))) {
            const n = new Date(naissance);
            const auj = new Date();
            age = auj.getFullYear() - n.getFullYear()
                - ((auj.getMonth() < n.getMonth() || (auj.getMonth() === n.getMonth() && auj.getDate() < n.getDate())) ? 1 : 0);
            age = (age >= 0 && age <= 130) ? age : null;
        }

        const ratio = (revenus > 0 && depot >= 0) ? depot / revenus : null;
        const pieceExpiree = !!expiration && !isNaN(Date.parse(expiration)) && new Date(expiration) < new Date(new Date().toDateString());

        if (age === null && ratio === null && !pieceExpiree) {
            window.copiloteEffacer(scope, 'coherence');
            return;
        }

        const donnees = await window.copiloteAppeler(@json(route('agent.clients.copilote.coherence')), {
            age_calcule: age,
            profession: profession || null,
            ratio_depot_revenu: ratio,
            piece_expiree: pieceExpiree,
        });

        window.copiloteEffacer(scope, 'coherence');

        if (!donnees) {
            return;
        }

        const source = donnees.source === 'regles_php' ? 'contrôle local' : 'analyse assistée';
        (donnees.avertissements ?? []).forEach((a) => window.copiloteAfficher(scope, a.champ, 'coherence', a.message, source, null));
    };

    window.copiloteSuggestion = async function (scope, input) {
        window.copiloteEffacer(scope, 'suggestion', input.id);

        const valeur = input.value.trim();
        if (!valeur) {
            return;
        }

        const donnees = await window.copiloteAppeler(@json(route('agent.clients.copilote.normalisation')), {
            champ: input.id,
            valeur,
        });

        window.copiloteEffacer(scope, 'suggestion', input.id);

        if (donnees && donnees.suggestion && input.value.trim() === valeur) {
            window.copiloteAfficher(scope, input.id, 'suggestion', donnees.suggestion.message, 'comptage local', {
                libelle: 'Utiliser cette orthographe',
                executer: () => {
                    input.value = donnees.suggestion.valeur;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    window.copiloteCoherence(scope);
                },
            });
        }
    };

    // ---------------------------------------------------------------------------
    // Propositions pendant la frappe (17_PROMPT §1) : à partir de 2 caractères, anti-rebond
    // 280 ms, liste cliquable (3 à 5 entrées), jamais de liste vide ni de message d'erreur.
    // Les réponses arrivées trop tard (le texte a changé entre-temps) sont ignorées.
    // ---------------------------------------------------------------------------
    window.copiloteListe = (input) => input.closest('.champ-autocompletion')?.querySelector('.copilote-liste');

    window.copiloteFermerListe = function (input) {
        const liste = window.copiloteListe(input);
        if (liste) {
            liste.hidden = true;
            liste.replaceChildren();
        }
        input.setAttribute('aria-expanded', 'false');
        input._actif = -1;
    };

    window.copiloteChoisir = function (input, valeur) {
        input.value = valeur;
        window.copiloteFermerListe(input);
        input._jeton = (input._jeton ?? 0) + 1;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        const scope = input.closest('.fiche-formulaire') ?? document;
        window.copiloteEffacer(scope, 'suggestion', input.id);
        if (input.id === 'profession') {
            window.copiloteCoherence(scope);
        }
        input.focus();
    };

    window.copiloteAfficherListe = function (input, propositions) {
        const liste = window.copiloteListe(input);
        if (!liste || propositions.length === 0) {
            window.copiloteFermerListe(input);
            return;
        }

        liste.replaceChildren();
        propositions.slice(0, 5).forEach((p) => {
            const li = document.createElement('li');
            li.setAttribute('role', 'option');
            const texte = document.createElement('span');
            texte.textContent = p.valeur;
            const nombre = document.createElement('small');
            nombre.textContent = p.nombre + ' dossier' + (p.nombre > 1 ? 's' : '');
            li.append(texte, nombre);
            // mousedown + preventDefault : le champ ne perd pas le focus (pas de blur avant le clic).
            li.addEventListener('mousedown', (e) => e.preventDefault());
            li.addEventListener('click', () => window.copiloteChoisir(input, p.valeur));
            liste.appendChild(li);
        });
        liste.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        input._actif = -1;
    };

    window.copiloteFrappe = function (input) {
        clearTimeout(input._minuteur);
        const saisie = input.value.trim();

        if (saisie.length < 2) {
            input._jeton = (input._jeton ?? 0) + 1;
            window.copiloteFermerListe(input);
            return;
        }

        input._minuteur = setTimeout(async () => {
            const jeton = input._jeton = (input._jeton ?? 0) + 1;
            const donnees = await window.copiloteAppeler(@json(route('agent.clients.copilote.propositions')), { champ: input.id, valeur: saisie });

            if (jeton !== input._jeton || input.value.trim() !== saisie) {
                return;
            }

            window.copiloteAfficherListe(input, donnees?.propositions ?? []);
        }, 280);
    };

    window.copiloteClavier = function (evenement) {
        const input = evenement.target;
        const liste = window.copiloteListe(input);

        if (!liste || liste.hidden) {
            return;
        }

        const items = [...liste.children];
        const bouger = (delta) => {
            input._actif = (((input._actif ?? -1) + delta) % items.length + items.length) % items.length;
            items.forEach((li, i) => li.classList.toggle('actif', i === input._actif));
            items[input._actif].scrollIntoView({ block: 'nearest' });
        };

        if (evenement.key === 'ArrowDown') { evenement.preventDefault(); bouger(1); }
        else if (evenement.key === 'ArrowUp') { evenement.preventDefault(); bouger(-1); }
        else if (evenement.key === 'Escape') { evenement.preventDefault(); window.copiloteFermerListe(input); }
        else if (evenement.key === 'Enter' && (input._actif ?? -1) >= 0) {
            evenement.preventDefault();
            window.copiloteChoisir(input, items[input._actif].firstChild.textContent);
        }
    };

    // Clic en dehors : ferme toutes les listes ouvertes.
    document.addEventListener('mousedown', (e) => {
        document.querySelectorAll('.copilote-liste:not([hidden])').forEach((liste) => {
            if (!liste.parentElement.contains(e.target)) {
                window.copiloteFermerListe(liste.parentElement.querySelector('input'));
            }
        });
    });

    window.copiloteBlur = async function (input) {
        const scope = input.closest('.fiche-formulaire') ?? document;
        const id = input.id;
        const taches = [];

        clearTimeout(input._minuteur);
        if (window.copiloteListe(input)) {
            window.copiloteFermerListe(input);
        }

        if (['nom', 'prenoms', 'date_naissance'].includes(id)) {
            taches.push(window.copiloteDoublon(scope));
        }
        if (['profession', 'activite_1', 'activite_2', 'employeur', 'nationalite'].includes(id)) {
            taches.push(window.copiloteSuggestion(scope, input));
        }
        if (['date_naissance', 'profession', 'revenus_mensuels_estimes', 'depot_especes', 'piece_identite_expiration'].includes(id)) {
            taches.push(window.copiloteCoherence(scope));
        }

        await Promise.all(taches);
    };

    // En complétion d'un dossier existant, le profil est déjà rempli : on l'évalue une fois au chargement.
    document.addEventListener('DOMContentLoaded', () => {
        if (window.__clientIdActuel) {
            document.querySelectorAll('.fiche-formulaire').forEach((scope) => window.copiloteCoherence(scope));
        }
    });
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
