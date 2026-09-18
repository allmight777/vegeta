@extends('layouts.admin')



@section('sous-titre', 'Ajustez les paramètres utilisés par cette règle de détection.')


@section('contenu')

<style>

    .editer-page {

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

        --shadow-sm: 0 4px 15px rgba(44, 52, 61, 0.04);
        --shadow: 0 12px 35px rgba(44, 52, 61, 0.07);

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 16px;
    }


    .editer-hero {

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


    .editer-hero::before {

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


    .editer-hero-icon {

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


    .editer-hero-text {

        position: relative;

        z-index: 2;

        display: flex;

        flex-direction: column;

        gap: 3px;
    }


    .editer-hero-text strong {

        color: #FFFFFF;

        font-size: 1rem;

        font-weight: 800;

        letter-spacing: -0.4px;
    }


    .editer-hero-text span {

        color: rgba(255, 255, 255, 0.62);

        font-size: 0.68rem;

        font-weight: 500;
    }


    .editer-card {

        background: #FFFFFF;

        border: 1px solid var(--border);

        border-radius: 20px;

        padding: 26px 28px;

        box-shadow: var(--shadow-sm);

        width: 100%;

        display: flex;

        flex-direction: column;

        gap: 22px;
    }


    .editer-section {

        display: flex;

        flex-direction: column;

        gap: 14px;
    }


    .editer-section-titre {

        display: flex;

        align-items: center;

        gap: 10px;

        font-size: 0.68rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--dark);

        padding-bottom: 10px;

        border-bottom: 1px solid var(--border);
    }


    .editer-section-titre i {

        color: var(--muted);
    }


    .champ-editer {

        display: flex;

        flex-direction: column;

        gap: 7px;

        min-width: 0;
    }


    .champ-editer-label {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        font-size: 0.62rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--dark);
    }


    .champ-editer-label i {

        color: var(--muted);

        font-size: 0.72rem;
    }


    .champ-editer-label .obligatoire {

        color: var(--danger);

        margin-left: 2px;
    }


    .champ-editer-input,
    .champ-editer textarea {

        width: 100%;

        min-height: 46px;

        padding: 11px 14px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #F8FAFC;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.78rem;

        font-weight: 600;

        outline: none;

        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }


    .champ-editer-input:hover,
    .champ-editer textarea:hover {

        border-color: #CBD5E1;

        background: #FFFFFF;
    }


    .champ-editer-input:focus,
    .champ-editer textarea:focus {

        border-color: var(--yellow);

        background: #FFFFFF;

        box-shadow: 0 0 0 3px rgba(240, 229, 53, 0.18);
    }


    /* Paramètres */

    .parametres-grid {

        display: grid;

        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));

        gap: 14px;
    }


    .parametre-editer {

        display: flex;

        flex-direction: column;

        gap: 7px;

        padding: 14px;

        background: var(--background);

        border: 1px solid var(--border);

        border-radius: 13px;

        transition: border-color 0.2s ease;
    }


    .parametre-editer:focus-within {

        border-color: rgba(240, 229, 53, 0.55);

        background: #FFFFFF;
    }


    .parametre-editer-cle {

        font-size: 0.58rem;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 0.6px;

        color: var(--muted);

        font-family: 'JetBrains Mono', ui-monospace, monospace;
    }


    .parametre-editer-hint {

        font-size: 0.6rem;

        color: var(--muted-light);

        font-weight: 500;
    }


    /* Actions */

    .editer-actions {

        display: flex;

        justify-content: flex-end;

        gap: 10px;

        padding-top: 8px;

        border-top: 1px solid var(--border);
    }


    .btn-secondary {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        height: 46px;

        padding: 0 20px;

        border-radius: 11px;

        border: 1px solid var(--border);

        background: #FFFFFF;

        color: var(--dark);

        font-family: inherit;

        font-size: 0.72rem;

        font-weight: 800;

        text-decoration: none;

        cursor: pointer;

        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }


    .btn-secondary i {

        color: var(--muted);
    }


    .btn-secondary:hover {

        background: var(--background);

        border-color: #CBD5E1;

        transform: translateY(-1px);
    }


    .btn-primary {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 9px;

        height: 46px;

        padding: 0 26px;

        border: none;

        border-radius: 11px;

        background: var(--yellow);

        color: var(--dark);

        font-family: inherit;

        font-size: 0.75rem;

        font-weight: 800;

        cursor: pointer;

        box-shadow: 0 8px 20px rgba(240, 229, 53, 0.30);

        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }


    .btn-primary i {

        color: var(--dark);
    }


    .btn-primary:hover {

        background: #E6DC28;

        transform: translateY(-2px);

        box-shadow: 0 12px 26px rgba(240, 229, 53, 0.36);
    }


    @media (max-width: 700px) {

        .editer-card {

            padding: 20px 18px;
        }


        .editer-hero {

            padding: 15px 16px;

            border-radius: 16px;
        }


        .editer-actions {

            flex-direction: column-reverse;
        }


        .editer-actions .btn-primary,
        .editer-actions .btn-secondary {

            width: 100%;

            justify-content: center;
        }
    }

</style>


<div class="editer-page">


    {{-- HERO --}}

    <div class="editer-hero">

        <div class="editer-hero-icon">

            <i class="fa-solid fa-pen-to-square"></i>

        </div>


        <div class="editer-hero-text">

            <strong>
                Modifier la règle {{ $regle->code }}
            </strong>

            <span>
                Ajustez les paramètres utilisés par le moteur de détection.
            </span>

        </div>

    </div>



    {{-- ERREURS --}}

    @if ($errors->any())

        <div style="padding: 14px 16px; border-radius: 14px; background: #FEF2F2; border: 1px solid #FECACA; color: var(--danger); font-size: 0.75rem; font-weight: 700; display: flex; gap: 10px; align-items: center;">

            <i class="fa-solid fa-triangle-exclamation"></i>

            {{ $errors->first() }}

        </div>

    @endif



    {{-- FORMULAIRE --}}

    <form
        method="POST"
        action="{{ route('admin.regles-detection.mettre-a-jour', $regle) }}"
        class="editer-card"
    >

        @csrf
        @method('PUT')


        {{-- INFORMATIONS GÉNÉRALES --}}

        <div class="editer-section">

            <div class="editer-section-titre">

                <i class="fa-solid fa-circle-info"></i>

                Informations générales

            </div>


            <div class="champ-editer">

                <label class="champ-editer-label" for="libelle">

                    <i class="fa-solid fa-heading"></i>

                    Libellé

                    <span class="obligatoire">*</span>

                </label>


                <input
                    type="text"
                    name="libelle"
                    id="libelle"
                    required
                    maxlength="255"
                    value="{{ old('libelle', $regle->libelle) }}"
                    class="champ-editer-input"
                >

            </div>


            <div class="champ-editer">

                <label class="champ-editer-label" for="reference_texte">

                    <i class="fa-solid fa-quote-left"></i>

                    Référence textuelle (source)

                </label>


                <input
                    type="text"
                    name="reference_texte"
                    id="reference_texte"
                    maxlength="255"
                    value="{{ old('reference_texte', $regle->reference_texte) }}"
                    class="champ-editer-input"
                    placeholder="Ex : Loi n°2020-XX, article 12"
                >

            </div>

        </div>



        {{-- PARAMÈTRES --}}

        <div class="editer-section">

            <div class="editer-section-titre">

                <i class="fa-solid fa-sliders"></i>

                Paramètres de la règle

            </div>


            @if (! empty($regle->parametres))

                <div class="parametres-grid">

                    @foreach ($regle->parametres as $cle => $valeur)

                        <div class="parametre-editer">

                            <label
                                class="parametre-editer-cle"
                                for="param_{{ $cle }}"
                            >
                                {{ str_replace('_', ' ', $cle) }}
                            </label>


                            <input
                                type="text"
                                name="parametres[{{ $cle }}]"
                                id="param_{{ $cle }}"
                                value="{{ old('parametres.' . $cle, $valeur) }}"
                                class="champ-editer-input"
                            >


                            @if (is_numeric($valeur))

                                <span class="parametre-editer-hint">

                                    Valeur numérique — saisir un nombre entier ou décimal.

                                </span>

                            @endif

                        </div>

                    @endforeach

                </div>

            @else

                <div style="padding: 14px; background: var(--background); border: 1px dashed var(--border); border-radius: 11px; color: var(--muted); font-size: 0.72rem; text-align: center;">

                    Cette règle n'a pas de paramètres configurables.

                </div>

            @endif

        </div>



        {{-- ACTIONS --}}

        <div class="editer-actions">

            <a
                href="{{ route('admin.regles-detection.index') }}"
                class="btn-secondary"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Annuler

            </a>


            <button type="submit" class="btn-primary">

                <i class="fa-solid fa-floppy-disk"></i>

                Enregistrer les modifications

            </button>

        </div>


    </form>


</div>

@endsection
