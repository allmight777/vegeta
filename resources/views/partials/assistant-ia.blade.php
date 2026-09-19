@php
    $assistantAgent = auth('agent')->user();

    if ($assistantAgent?->estResponsableAgence()) {
        $assistantUrlRepondre = route('responsable.assistant.repondre');
        $assistantUrlEscalader = null;
    } elseif ($assistantAgent !== null) {
        $assistantUrlRepondre = route('agent.assistant.repondre');
        $assistantUrlEscalader = route('agent.assistant.escalader');
    } else {
        $assistantUrlRepondre = route('admin.assistant.repondre');
        $assistantUrlEscalader = null;
    }
    $assistantEcran = request()->route()?->getName() ?? request()->path();
    $assistantModeLibelle = app(\App\Services\Assistance\SelecteurProviderIa::class)->modeActifLibelle();
    $assistantHorsLigne = str_contains(mb_strtolower($assistantModeLibelle), 'hors connexion');
@endphp

<style>

    /* =========================================================
       ASSISTANT IA — BOUTON FLOTTANT
    ========================================================= */

    .assistant-ia-root {

        --yellow: #F0E535;
        --yellow-dark: #E6DC28;
        --yellow-soft: rgba(240, 229, 53, 0.14);

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.14);

        --dark: #2C343D;
        --dark-hover: #3A4650;

        --text: #1E293B;
        --muted: #64748B;
        --muted-light: #94A3B8;

        --border: #E8EDF2;
        --white: #FFFFFF;
        --background: #F8FAFC;

        font-family: 'Plus Jakarta Sans', sans-serif;

        position: fixed;

        bottom: 24px;

        right: 24px;

        z-index: 60;
    }


    /* =========================================================
       BOUTON FLOTTANT
    ========================================================= */

    .assistant-ia-bouton {

        position: relative;

        width: 58px;

        height: 58px;

        border-radius: 50%;

        border: none;

        background:
            linear-gradient(
                135deg,
                var(--yellow) 0%,
                #F7EF63 100%
            );

        color: var(--dark);

        cursor: pointer;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.4rem;

        box-shadow:
            0 12px 26px rgba(240, 229, 53, 0.42),
            0 4px 10px rgba(44, 52, 61, 0.10);

        transition:
            transform 0.25s ease,
            box-shadow 0.25s ease;

        animation: assistantFloat 4s ease-in-out infinite;
    }


    .assistant-ia-bouton:hover {

        transform: translateY(-3px) scale(1.05);

        box-shadow:
            0 18px 34px rgba(240, 229, 53, 0.50),
            0 6px 14px rgba(44, 52, 61, 0.14);

        animation-play-state: paused;
    }


    .assistant-ia-bouton:active {

        transform: translateY(-1px) scale(1.02);
    }


    /* Halo pulsant */

    .assistant-ia-bouton::before {

        content: "";

        position: absolute;

        inset: -6px;

        border-radius: 50%;

        background: rgba(240, 229, 53, 0.30);

        z-index: -1;

        animation: assistantPulse 2.6s ease-out infinite;
    }


    /* Pastille "en ligne" */

    .assistant-ia-bouton::after {

        content: "";

        position: absolute;

        width: 12px;

        height: 12px;

        top: 6px;

        right: 6px;

        border-radius: 50%;

        background: var(--green);

        border: 2.5px solid #FFFFFF;

        box-shadow:
            0 0 0 3px rgba(48, 195, 26, 0.14);
    }


    .assistant-ia-bouton.hors-ligne::after {

        background: var(--muted-light);

        box-shadow:
            0 0 0 3px rgba(148, 163, 184, 0.14);
    }


    .assistant-ia-bouton i {

        position: relative;

        z-index: 2;

        transition: transform 0.3s ease;
    }


    .assistant-ia-bouton.ouvert i {

        transform: rotate(-90deg);
    }


    @keyframes assistantFloat {

        0%, 100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-4px);
        }
    }


    @keyframes assistantPulse {

        0% {
            transform: scale(0.9);
            opacity: 0.7;
        }

        100% {
            transform: scale(1.4);
            opacity: 0;
        }
    }


    /* =========================================================
       PANNEAU
    ========================================================= */

    .assistant-ia-panneau {

        position: absolute;

        bottom: 76px;

        right: 0;

        width: min(380px, calc(100vw - 32px));

        max-height: min(560px, calc(100vh - 130px));

        display: flex;

        flex-direction: column;

        background: var(--white);

        border: 1px solid var(--border);

        border-radius: 20px;

        box-shadow:
            0 28px 60px rgba(44, 52, 61, 0.22),
            0 8px 20px rgba(44, 52, 61, 0.10);

        overflow: hidden;

        transform-origin: bottom right;
    }


    /* =========================================================
       EN-TÊTE
    ========================================================= */

    .assistant-ia-entete {

        display: flex;

        align-items: center;

        gap: 12px;

        padding: 14px 16px;

        background:
            linear-gradient(
                135deg,
                #2C343D 0%,
                #3A4650 65%,
                #303A43 100%
            );

        color: #FFFFFF;

        position: relative;

        overflow: hidden;
    }


    .assistant-ia-entete::before {

        content: "";

        position: absolute;

        width: 160px;

        height: 160px;

        right: -70px;

        top: -80px;

        border-radius: 50%;

        background: var(--yellow);

        opacity: 0.10;
    }


    .assistant-ia-avatar {

        width: 42px;

        height: 42px;

        flex-shrink: 0;

        border-radius: 13px;

        background: rgba(240, 229, 53, 0.16);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: var(--yellow);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 1.05rem;

        position: relative;

        z-index: 2;
    }


    .assistant-ia-identite {

        display: flex;

        flex-direction: column;

        gap: 3px;

        position: relative;

        z-index: 2;

        min-width: 0;

        flex: 1;
    }


    .assistant-ia-nom {

        font-size: 0.82rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        color: #FFFFFF;
    }


    .assistant-ia-badge {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 3px 9px;

        border-radius: 999px;

        background: rgba(48, 195, 26, 0.18);

        border: 1px solid rgba(48, 195, 26, 0.28);

        color: #6EE85A;

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.5px;

        align-self: flex-start;
    }


    .assistant-ia-badge::before {

        content: "";

        width: 6px;

        height: 6px;

        border-radius: 50%;

        background: var(--green);

        box-shadow: 0 0 0 3px rgba(48, 195, 26, 0.18);
    }


    .assistant-ia-badge.hors-ligne {

        background: rgba(240, 229, 53, 0.18);

        border-color: rgba(240, 229, 53, 0.28);

        color: #F7EF63;
    }


    .assistant-ia-badge.hors-ligne::before {

        background: var(--yellow);

        box-shadow: 0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    .assistant-ia-fermer {

        width: 32px;

        height: 32px;

        flex-shrink: 0;

        border-radius: 10px;

        background: rgba(255, 255, 255, 0.10);

        border: 1px solid rgba(255, 255, 255, 0.15);

        color: rgba(255, 255, 255, 0.85);

        cursor: pointer;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.78rem;

        transition:
            background 0.2s ease,
            color 0.2s ease;

        position: relative;

        z-index: 2;
    }


    .assistant-ia-fermer:hover {

        background: rgba(255, 255, 255, 0.20);

        color: #FFFFFF;
    }


    /* =========================================================
       MESSAGES
    ========================================================= */

    .assistant-ia-messages {

        flex: 1;

        overflow-y: auto;

        padding: 16px 14px;

        display: flex;

        flex-direction: column;

        gap: 12px;

        background:
            radial-gradient(
                circle at 100% 0%,
                rgba(240, 229, 53, 0.06),
                transparent 40%
            ),
            var(--background);

        scroll-behavior: smooth;
    }


    .assistant-ia-messages::-webkit-scrollbar {

        width: 6px;
    }


    .assistant-ia-messages::-webkit-scrollbar-thumb {

        background: rgba(148, 163, 184, 0.35);

        border-radius: 999px;
    }


    .assistant-ia-message {

        max-width: 88%;

        padding: 10px 13px;

        border-radius: 14px;

        font-size: 0.76rem;

        line-height: 1.55;

        font-weight: 500;

        word-wrap: break-word;

        overflow-wrap: anywhere;

        animation: messageSlide 0.28s ease-out;
    }


    /* Liens générés par formaterMessage() */

    .assistant-ia-message a {

        color: inherit;

        text-decoration: underline;

        text-underline-offset: 2px;

        font-weight: 700;

        transition: opacity 0.2s ease;
    }


    .assistant-ia-message a:hover {

        opacity: 0.75;
    }


    @keyframes messageSlide {

        from {
            opacity: 0;
            transform: translateY(6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    .assistant-ia-message.utilisateur {

        align-self: flex-end;

        background:
            linear-gradient(
                135deg,
                var(--yellow) 0%,
                #F7EF63 100%
            );

        color: var(--dark);

        font-weight: 700;

        border-bottom-right-radius: 4px;

        box-shadow:
            0 6px 14px rgba(240, 229, 53, 0.28);
    }


    .assistant-ia-message.assistant {

        align-self: flex-start;

        background: var(--white);

        border: 1px solid var(--border);

        color: var(--text);

        border-bottom-left-radius: 4px;

        box-shadow:
            0 4px 12px rgba(44, 52, 61, 0.04);
    }


    /* =========================================================
       BOUTON ESCALADE
    ========================================================= */

    .assistant-ia-escalader {

        align-self: flex-start;

        display: inline-flex;

        align-items: center;

        gap: 6px;

        margin-top: 6px;

        padding: 6px 11px;

        border: 1px dashed rgba(44, 52, 61, 0.35);

        background: transparent;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.63rem;

        font-weight: 800;

        border-radius: 9px;

        cursor: pointer;

        transition:
            background 0.2s ease,
            border-color 0.2s ease,
            color 0.2s ease,
            transform 0.2s ease;
    }


    .assistant-ia-escalader i {

        font-size: 0.68rem;
    }


    .assistant-ia-escalader:hover {

        background: var(--yellow-soft);

        border-color: rgba(240, 229, 53, 0.55);

        border-style: solid;

        transform: translateY(-1px);
    }


    /* =========================================================
       VOIX : LECTURE PAR MESSAGE, MICRO, RÉGLAGE LECTURE AUTO
    ========================================================= */

    .assistant-ia-ecouter {

        align-self: flex-start;

        display: inline-flex;

        align-items: center;

        gap: 6px;

        margin-top: 4px;

        padding: 5px 10px;

        border: none;

        background: transparent;

        color: var(--muted-light);

        font-family: inherit;

        font-size: 0.6rem;

        font-weight: 700;

        border-radius: 9px;

        cursor: pointer;

        transition: color 0.2s ease, background 0.2s ease;
    }


    .assistant-ia-ecouter:hover {

        color: var(--dark);

        background: var(--yellow-soft);
    }


    .assistant-ia-micro.actif {

        background: #DC2626;

        animation: assistantMicroPulse 1.4s ease-in-out infinite;
    }


    @keyframes assistantMicroPulse {

        0%, 100% {
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.35);
        }

        50% {
            box-shadow: 0 0 0 6px rgba(220, 38, 38, 0);
        }
    }


    .assistant-ia-lecture-toggle {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        margin-left: auto;

        color: var(--muted-light);

        font-size: 0.55rem;

        font-weight: 700;

        cursor: pointer;

        white-space: nowrap;
    }


    /* =========================================================
       INDICATEUR DE CHARGEMENT
    ========================================================= */

    .assistant-ia-chargement {

        align-self: flex-start;

        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 10px 14px;

        background: var(--white);

        border: 1px solid var(--border);

        border-radius: 14px;

        border-bottom-left-radius: 4px;

        box-shadow:
            0 4px 12px rgba(44, 52, 61, 0.04);
    }


    .assistant-ia-chargement span {

        width: 6px;

        height: 6px;

        border-radius: 50%;

        background: var(--muted-light);

        animation: assistantTyping 1.4s ease-in-out infinite;
    }


    .assistant-ia-chargement span:nth-child(2) {

        animation-delay: 0.2s;
    }


    .assistant-ia-chargement span:nth-child(3) {

        animation-delay: 0.4s;
    }


    @keyframes assistantTyping {

        0%, 60%, 100% {
            transform: translateY(0);
            background: var(--muted-light);
        }

        30% {
            transform: translateY(-4px);
            background: var(--dark);
        }
    }


    /* =========================================================
       SAISIE
    ========================================================= */

    .assistant-ia-saisie {

        display: flex;

        align-items: center;

        gap: 8px;

        padding: 12px;

        border-top: 1px solid var(--border);

        background: var(--white);
    }


    .assistant-ia-saisie input {

        flex: 1;

        min-width: 0;

        height: 42px;

        padding: 0 14px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: var(--background);

        color: var(--dark);

        font-family: inherit;

        font-size: 0.76rem;

        font-weight: 600;

        outline: none;

        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            box-shadow 0.2s ease;
    }


    .assistant-ia-saisie input:hover:not(:disabled) {

        border-color: #CBD5E1;

        background: var(--white);
    }


    .assistant-ia-saisie input:focus {

        border-color: var(--yellow);

        background: var(--white);

        box-shadow:
            0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    .assistant-ia-saisie input::placeholder {

        color: var(--muted-light);

        font-weight: 500;
    }


    .assistant-ia-saisie input:disabled {

        opacity: 0.6;

        cursor: not-allowed;
    }


    .assistant-ia-saisie button {

        width: 42px;

        height: 42px;

        flex-shrink: 0;

        border: none;

        border-radius: 11px;

        background: var(--dark);

        color: #FFFFFF;

        cursor: pointer;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 0.82rem;

        transition:
            background 0.2s ease,
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }


    .assistant-ia-saisie button:hover:not(:disabled) {

        background: var(--dark-hover);

        transform: translateY(-1px);

        box-shadow:
            0 8px 18px rgba(44, 52, 61, 0.18);
    }


    .assistant-ia-saisie button:disabled {

        opacity: 0.45;

        cursor: not-allowed;
    }


    .assistant-ia-saisie button i {

        color: var(--yellow);

    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 560px) {

        .assistant-ia-root {

            bottom: 16px;

            right: 16px;
        }


        .assistant-ia-bouton {

            width: 52px;

            height: 52px;

            font-size: 1.2rem;
        }


        .assistant-ia-panneau {

            bottom: 68px;

            width: calc(100vw - 24px);

            max-height: calc(100vh - 110px);

            border-radius: 18px;
        }
    }

</style>

<div
    class="assistant-ia-root"
    x-data="assistantIa({
        urlRepondre: @js($assistantUrlRepondre),
        urlEscalader: @js($assistantUrlEscalader),
        ecran: @js($assistantEcran),
        modeLibelle: @js($assistantModeLibelle),
    })"
    x-cloak
>


    {{-- =====================================================
         BOUTON FLOTTANT
    ====================================================== --}}

    <button
        type="button"
        class="assistant-ia-bouton"
        :class="{ 'ouvert': ouvert, 'hors-ligne': modeLibelle.toLowerCase().includes('hors connexion') }"
        @click="ouvert = ! ouvert"
        :aria-label="ouvert ? 'Fermer l\'assistant' : 'Ouvrir l\'assistant'"
    >

        <i
            class="fa-solid"
            :class="ouvert ? 'fa-xmark' : 'fa-wand-magic-sparkles'"
        ></i>

    </button>



    {{-- =====================================================
         PANNEAU
    ====================================================== --}}

    <div
        class="assistant-ia-panneau"
        x-show="ouvert"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
    >


        {{-- EN-TÊTE --}}

        <div class="assistant-ia-entete">

            <div class="assistant-ia-avatar">

                <i class="fa-solid fa-wand-magic-sparkles"></i>

            </div>


            <div class="assistant-ia-identite">

                <span class="assistant-ia-nom">
                    Assistant {{ $identite['nom_systeme'] }}
                </span>



            </div>


            <label
                class="assistant-ia-lecture-toggle"
                x-show="syntheseSupportee"
                title="Lire automatiquement chaque réponse à voix haute"
            >
                <input type="checkbox" x-model="lectureAutomatique" @change="sauvegarderLectureAutomatique()">
                Lecture auto
            </label>


            <button
                type="button"
                class="assistant-ia-fermer"
                @click="ouvert = false"
                aria-label="Fermer"
            >

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>



        {{-- MESSAGES --}}

        <div
            class="assistant-ia-messages"
            x-ref="messages"
        >


            {{-- MESSAGE D'ACCUEIL --}}

            <div class="assistant-ia-message assistant">

                Bonjour 👋 Je suis l'assistant {{ $identite['nom_systeme'] }}.
                Posez-moi une question sur un champ, un concept
                réglementaire ou l'utilisation de l'application.

            </div>



            {{-- CONVERSATION --}}

            <template
                x-for="(message, index) in messages"
                :key="index"
            >

                <div style="display: flex; flex-direction: column;">

                    {{-- x-html : le contenu est échappé puis ré-encodé
                         par formaterMessage() avant d'être injecté, afin
                         qu'aucune balise issue de l'OCR d'un document ne
                         puisse être exécutée. Seules les URLs http(s) sont
                         transformées en liens cliquables. --}}
                    <div
                        class="assistant-ia-message"
                        :class="message.role === 'utilisateur' ? 'utilisateur' : 'assistant'"
                        x-html="formaterMessage(message.contenu)"
                    ></div>


                    <div style="display: flex; align-items: center; gap: 4px;">

                        <button
                            type="button"
                            class="assistant-ia-ecouter"
                            x-show="message.role === 'assistant' && syntheseSupportee"
                            @click="lire(message.contenu)"
                            aria-label="Écouter cette réponse"
                        >

                            <i class="fa-solid fa-volume-high"></i>

                            Écouter

                        </button>


                        <button
                            type="button"
                            class="assistant-ia-escalader"
                            x-show="message.role === 'assistant' && message.peutEscalader && urlEscalader && ! message.escaladee"
                            @click="escalader(index)"
                        >

                            <i class="fa-solid fa-headset"></i>

                            Transmettre à un responsable

                        </button>

                    </div>

                </div>

            </template>



            {{-- INDICATEUR DE CHARGEMENT --}}

            <div
                class="assistant-ia-chargement"
                x-show="enCours"
            >

                <span></span>
                <span></span>
                <span></span>

            </div>

        </div>



        {{-- SAISIE --}}

        <form
            class="assistant-ia-saisie"
            @submit.prevent="envoyer()"
        >

            <input
                type="text"
                x-model="question"
                placeholder="Votre question…"
                :disabled="enCours"
                autocomplete="off"
            >


            <button
                type="button"
                class="assistant-ia-micro"
                :class="{ actif: enEcoute }"
                x-show="dicteeSupportee"
                @click="basculerDictee()"
                :aria-label="enEcoute ? 'Arrêter la dictée' : 'Dicter la question'"
                :title="enEcoute ? 'Arrêter la dictée' : 'Dicter la question'"
            >

                <i class="fa-solid" :class="enEcoute ? 'fa-microphone' : 'fa-microphone-slash'"></i>

            </button>


            <button
                type="submit"
                :disabled="enCours || ! question.trim()"
                aria-label="Envoyer"
            >

                <i class="fa-solid fa-paper-plane"></i>

            </button>

        </form>


        <p
            class="assistant-ia-lecture-toggle"
            style="padding: 0 12px 10px; margin-left: 0;"
            x-show="! dicteeSupportee"
        >
            Dictée vocale non disponible sur ce navigateur.
        </p>

    </div>

</div>

@once
    <script>
        function assistantIa(config) {
            return {
                ouvert: false,
                enCours: false,
                question: '',
                messages: [],
                urlRepondre: config.urlRepondre,
                urlEscalader: config.urlEscalader,
                ecran: config.ecran,
                modeLibelle: config.modeLibelle,

                // Voix (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §6) : API navigateur
                // natives uniquement, aucune dépendance serveur. Le texte lu est déjà
                // celui affiché par le widget — donc déjà passé par le filtre de
                // conformité côté serveur (GestionnaireAssistant), jamais un texte non
                // filtré.
                dicteeSupportee: !! (window.SpeechRecognition || window.webkitSpeechRecognition),
                syntheseSupportee: !! window.speechSynthesis,
                enEcoute: false,
                lectureAutomatique: false,
                reconnaissance: null,

                init() {
                    try {
                        this.lectureAutomatique = sessionStorage.getItem('assistantIaLectureAutomatique') === '1';
                    } catch (e) {
                        this.lectureAutomatique = false;
                    }
                },

                sauvegarderLectureAutomatique() {
                    try {
                        sessionStorage.setItem('assistantIaLectureAutomatique', this.lectureAutomatique ? '1' : '0');
                    } catch (e) {
                        // Stockage indisponible (navigation privée, quota) : réglage non
                        // persisté pour cette session, la case reste utilisable.
                    }
                },

                basculerDictee() {
                    if (! this.dicteeSupportee) return;

                    if (this.enEcoute) {
                        this.reconnaissance?.stop();

                        return;
                    }

                    const Reconnaissance = window.SpeechRecognition || window.webkitSpeechRecognition;
                    this.reconnaissance = new Reconnaissance();
                    this.reconnaissance.lang = 'fr-FR';
                    this.reconnaissance.interimResults = false;

                    this.reconnaissance.onstart = () => { this.enEcoute = true; };
                    this.reconnaissance.onend = () => { this.enEcoute = false; };
                    this.reconnaissance.onerror = () => { this.enEcoute = false; };
                    this.reconnaissance.onresult = (evenement) => {
                        this.question = evenement.results[0][0].transcript;
                    };

                    this.reconnaissance.start();
                },

                lire(texte) {
                    if (! this.syntheseSupportee || ! texte) return;

                    window.speechSynthesis.cancel();
                    const enonce = new SpeechSynthesisUtterance(texte);
                    enonce.lang = 'fr-FR';
                    window.speechSynthesis.speak(enonce);
                },

                // Met en forme le texte affiché dans les bulles :
                //   1) échappe d'abord &, < et > pour empêcher toute injection HTML
                //      depuis le contenu d'un document (OCR, contenu extrait) — le
                //      serveur peut parfaitement renvoyer du texte brut sans balises ;
                //   2) transforme uniquement les URLs http(s) que nous savons avoir
                //      générées (ou qui se trouvent littéralement dans le texte) en
                //      liens cliquables ouverts dans un nouvel onglet ;
                //   3) convertit les sauts de ligne \n en <br> pour préserver la mise
                //      en page d'origine du message.
                formaterMessage(texte) {
                    if (! texte) return '';

                    const echappe = String(texte)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');

                    const avecLiens = echappe.replace(
                        /(https?:\/\/[^\s<]+)/g,
                        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
                    );

                    return avecLiens.replace(/\n/g, '<br>');
                },

                async envoyer() {
                    const question = this.question.trim();
                    if (! question || this.enCours) return;

                    this.messages.push({ role: 'utilisateur', contenu: question });
                    this.question = '';
                    this.enCours = true;

                    this.$nextTick(() => {
                        this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                    });

                    try {
                        const reponse = await fetch(this.urlRepondre, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ question, ecran: this.ecran }),
                        });
                        const donnees = await reponse.json();
                        this.messages.push({
                            role: 'assistant',
                            contenu: donnees.reponse ?? 'Une erreur est survenue.',
                            peutEscalader: !! donnees.peut_escalader,
                            question,
                        });

                        if (this.lectureAutomatique) {
                            this.lire(donnees.reponse);
                        }
                    } catch (e) {
                        this.messages.push({
                            role: 'assistant',
                            contenu: 'Une erreur est survenue. Réessayez.',
                            peutEscalader: false,
                        });
                    } finally {
                        this.enCours = false;
                        this.$nextTick(() => {
                            this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                        });
                    }
                },

                async escalader(index) {
                    if (! this.urlEscalader) return;
                    const message = this.messages[index];

                    await fetch(this.urlEscalader, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            question: message.question,
                            ecran: this.ecran,
                            reponse_ia: message.contenu,
                        }),
                    });

                    message.escaladee = true;
                    this.messages.push({
                        role: 'assistant',
                        contenu: 'Votre question a bien été transmise à un responsable. Vous serez recontacté prochainement.',
                        peutEscalader: false,
                    });

                    this.$nextTick(() => {
                        this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                    });
                },
            };
        }
    </script>
@endonce
