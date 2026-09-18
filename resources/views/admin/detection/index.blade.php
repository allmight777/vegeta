@extends('layouts.admin')



@section('sous-titre', 'Configurez les seuils et paramètres utilisés par le moteur de conformité.')


@section('contenu')

<style>

    .regles-page {

        --yellow: #F0E535;
        --yellow-soft: rgba(240, 229, 53, 0.12);
        --yellow-light: #FFFDE7;

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --dark: #2C343D;
        --muted: #64748B;
        --muted-light: #94A3B8;

        --border: #E8EDF2;
        --white: #FFFFFF;
        --background: #F8FAFC;

        --danger: #DC2626;
        --danger-soft: rgba(220, 38, 38, 0.08);

        --shadow-sm: 0 4px 15px rgba(44, 52, 61, 0.04);
        --shadow: 0 12px 35px rgba(44, 52, 61, 0.07);

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    .regles-hero {

        display: flex;

        align-items: center;

        gap: 15px;

        padding: 18px 22px;

        border-radius: 20px;

        background: linear-gradient(135deg, #2C343D 0%, #3A4650 65%, #303A43 100%);

        position: relative;

        overflow: hidden;

        box-shadow: var(--shadow);
    }


    .regles-hero::before {

        content: "";

        position: absolute;

        width: 220px;
        height: 220px;

        right: -80px;
        top: -130px;

        border-radius: 50%;

        background: var(--yellow);

        opacity: 0.10;
    }


    .regles-hero-icon {

        width: 46px;
        height: 46px;

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


    .regles-hero-text {

        position: relative;

        z-index: 2;

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .regles-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .regles-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    /* Alerte succès */

    .regles-alert {

        display: flex;

        align-items: center;

        gap: 12px;

        padding: 14px 16px;

        border-radius: 14px;

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: #238E15;

        font-size: 0.75rem;

        font-weight: 700;

    }


    .regles-alert i {

        font-size: 0.9rem;
    }


    /* Carte règle */

    .regle-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 18px;

        padding: 20px 22px;

        box-shadow: var(--shadow-sm);

        display: flex;

        flex-direction: column;

        gap: 14px;

        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }


    .regle-card:hover {

        border-color: rgba(240, 229, 53, 0.45);

        box-shadow: 0 10px 28px rgba(44, 52, 61, 0.06);
    }


    .regle-card.desactivee {

        opacity: 0.72;

        background: #FCFCFD;
    }


    /* En-tête règle */

    .regle-entete {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        flex-wrap: wrap;
    }


    .regle-entete-left {

        display: flex;

        align-items: center;

        gap: 12px;

        min-width: 0;

        flex: 1;
    }


    .regle-entete-icon {

        width: 38px;
        height: 38px;

        flex-shrink: 0;

        border-radius: 11px;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: var(--dark);

        display: flex;

        align-items: center;
        justify-content: center;

        font-size: 0.85rem;
    }


    .regle-entete-text {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .regle-code {

        font-family: 'JetBrains Mono', ui-monospace, monospace;

        font-size: 0.72rem;

        font-weight: 800;

        color: var(--dark);

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        padding: 2px 8px;

        border-radius: 7px;

        display: inline-block;

        width: fit-content;
    }


    .regle-libelle {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        line-height: 1.3;
    }


    /* Badges à droite */

    .regle-entete-right {

        display: flex;

        align-items: center;

        gap: 8px;

        flex-wrap: wrap;
    }


    .badge-statut {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 11px;

        border-radius: 999px;

        font-size: 0.62rem;

        font-weight: 800;

        white-space: nowrap;
    }


    .badge-statut i {

        font-size: 0.62rem;
    }


    .badge-statut.active {

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: #249C13;
    }


    .badge-statut.desactivee {

        background: rgba(148, 163, 184, 0.12);

        border: 1px solid rgba(148, 163, 184, 0.22);

        color: var(--muted);
    }


    /* Paramètres */

    .regle-parametres {

        display: grid;

        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));

        gap: 10px;

        padding: 14px;

        background: var(--background);

        border: 1px solid var(--border);

        border-radius: 13px;
    }


    .parametre-item {

        display: flex;

        flex-direction: column;

        gap: 3px;

        min-width: 0;
    }


    .parametre-cle {

        font-size: 0.56rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted-light);
    }


    .parametre-valeur {

        color: var(--dark);

        font-size: 0.78rem;

        font-weight: 800;

        letter-spacing: -0.2px;

        font-family: 'JetBrains Mono', ui-monospace, monospace;

        overflow: hidden;

        text-overflow: ellipsis;
    }


    /* Actions */

    .regle-actions {

        display: flex;

        align-items: center;

        justify-content: flex-end;

        gap: 8px;

        flex-wrap: wrap;
    }


    .btn-action {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 7px;

        height: 36px;

        padding: 0 14px;

        border-radius: 10px;

        font-family: inherit;

        font-size: 0.68rem;

        font-weight: 800;

        text-decoration: none;

        cursor: pointer;

        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }


    .btn-action i {

        font-size: 0.68rem;
    }


    .btn-action.editer {

        background: var(--dark);

        border: 1px solid var(--dark);

        color: #FFFFFF;
    }


    .btn-action.editer i {

        color: var(--yellow);
    }


    .btn-action.editer:hover {

        background: #3A4650;

        border-color: #3A4650;

        transform: translateY(-1px);
    }


    .btn-action.activer {

        background: var(--green-soft);

        border: 1px solid rgba(48, 195, 26, 0.20);

        color: #238E15;
    }


    .btn-action.activer:hover {

        background: rgba(48, 195, 26, 0.16);

        border-color: rgba(48, 195, 26, 0.35);

        transform: translateY(-1px);
    }


    .btn-action.desactiver {

        background: var(--danger-soft);

        border: 1px solid rgba(220, 38, 38, 0.20);

        color: var(--danger);
    }


    .btn-action.desactiver:hover {

        background: rgba(220, 38, 38, 0.16);

        border-color: rgba(220, 38, 38, 0.35);

        transform: translateY(-1px);
    }


    /* État vide */

    .regles-empty {

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 12px;

        padding: 55px 25px;

        text-align: center;

        background: #FFFFFF;

        border: 1px dashed var(--border);

        border-radius: 18px;

        color: var(--muted);
    }


    .regles-empty-icon {

        width: 58px;
        height: 58px;

        border-radius: 50%;

        background: var(--yellow-soft);

        border: 1px solid rgba(240, 229, 53, 0.28);

        color: var(--dark);

        display: flex;

        align-items: center;
        justify-content: center;

        font-size: 1.4rem;
    }


    .regles-empty strong {

        color: var(--dark);

        font-size: 0.86rem;

        font-weight: 800;
    }


    .regles-empty span {

        font-size: 0.68rem;

        color: var(--muted-light);

        max-width: 320px;

        line-height: 1.5;
    }


    @media (max-width: 700px) {

        .regles-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .regle-card {

            padding: 16px 16px;
        }


        .regle-actions {

            justify-content: stretch;
        }


        .regle-actions .btn-action {

            flex: 1;
        }
    }

</style>


<div class="regles-page">


    {{-- HERO --}}

    <div class="regles-hero">

        <div class="regles-hero-icon">

            <i class="fa-solid fa-sliders"></i>

        </div>


        <div class="regles-hero-text">

            <strong>
                Règles de détection
            </strong>

            <span>
                Configurez les seuils utilisés par le moteur de filtrage.
            </span>

        </div>

    </div>



    {{-- MESSAGE --}}

    @if (session('statut'))

        <div class="regles-alert">

            <i class="fa-solid fa-circle-check"></i>

            {{ session('statut') }}

        </div>

    @endif



    {{-- LISTE --}}

    @if ($regles->count())


        @foreach ($regles as $regle)

            <div class="regle-card {{ $regle->actif ? '' : 'desactivee' }}">


                {{-- EN-TÊTE --}}

                <div class="regle-entete">

                    <div class="regle-entete-left">

                        <div class="regle-entete-icon">

                            <i class="fa-solid fa-shield-halved"></i>

                        </div>


                        <div class="regle-entete-text">

                            <span class="regle-code">
                                {{ $regle->code }}
                            </span>

                            <span class="regle-libelle">
                                {{ $regle->libelle }}
                            </span>

                        </div>

                    </div>


                    <div class="regle-entete-right">

                        <x-badge-source :source="$regle->source" :reference="$regle->reference_texte" />

                        <span class="badge-statut {{ $regle->actif ? 'active' : 'desactivee' }}">

                            <i class="fa-solid {{ $regle->actif ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>

                            {{ $regle->actif ? 'Active' : 'Désactivée' }}

                        </span>

                    </div>

                </div>



                {{-- PARAMÈTRES --}}

                @if (! empty($regle->parametres))

                    <div class="regle-parametres">

                        @foreach ($regle->parametres as $cle => $valeur)

                            <div class="parametre-item">

                                <span class="parametre-cle">
                                    {{ str_replace('_', ' ', $cle) }}
                                </span>

                                <span class="parametre-valeur">

                                    {{ is_numeric($valeur) ? number_format((float) $valeur, 0, ',', ' ') : $valeur }}

                                </span>

                            </div>

                        @endforeach

                    </div>

                @endif



                {{-- ACTIONS --}}

                <div class="regle-actions">


                    <form
                        method="POST"
                        action="{{ route('admin.regles-detection.basculer', $regle) }}"
                    >

                        @csrf
                        @method('PUT')


                        @if ($regle->actif)

                            <button type="submit" class="btn-action desactiver">

                                <i class="fa-solid fa-power-off"></i>

                                Désactiver

                            </button>

                        @else

                            <button type="submit" class="btn-action activer">

                                <i class="fa-solid fa-check"></i>

                                Activer

                            </button>

                        @endif

                    </form>


                    <a
                        href="{{ route('admin.regles-detection.editer', $regle) }}"
                        class="btn-action editer"
                    >

                        <i class="fa-solid fa-pen-to-square"></i>

                        Modifier les paramètres

                    </a>

                </div>

            </div>

        @endforeach


    @else


        <div class="regles-empty">

            <div class="regles-empty-icon">

                <i class="fa-solid fa-sliders"></i>

            </div>


            <strong>
                Aucune règle de détection
            </strong>


            <span>
                Les règles sont normalement créées par le seeder initial.
                Contactez le super-administrateur si ce n'est pas le cas.
            </span>

        </div>

    @endif


</div>

@endsection
