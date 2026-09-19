<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de filtrage — {{ $nomCible }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1E293B;
            padding: 30px 40px;
            line-height: 1.5;
        }

        .header {
            border-bottom: 3px solid #DC2626;
            padding-bottom: 16px;
            margin-bottom: 22px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 900;
            color: #2C343D;
            letter-spacing: -0.5px;
        }

        .header .badge-danger {
            display: inline-block;
            padding: 4px 12px;
            background: #FEE2E2;
            border: 1px solid #DC2626;
            color: #DC2626;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .header .meta {
            font-size: 9px;
            color: #64748B;
            font-weight: 600;
        }

        .section {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #E8EDF2;
            border-radius: 10px;
            background: #FBFCFE;
        }

        .section h2 {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #2563EB;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #E8EDF2;
        }

        .grid {
            display: table;
            width: 100%;
        }

        .grid-row {
            display: table-row;
        }

        .grid-cell {
            display: table-cell;
            padding: 4px 10px 4px 0;
            vertical-align: top;
        }

        .label {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94A3B8;
            margin-bottom: 2px;
        }

        .value {
            font-size: 11px;
            font-weight: 700;
            color: #2C343D;
        }

        .score-box {
            display: inline-block;
            padding: 8px 18px;
            border-radius: 8px;
            background: #FEE2E2;
            border: 2px solid #DC2626;
            color: #DC2626;
            font-size: 22px;
            font-weight: 900;
            font-family: 'DejaVu Sans Mono', monospace;
            letter-spacing: -1px;
        }

        .explication {
            font-size: 10.5px;
            line-height: 1.6;
            color: #1E293B;
            padding: 10px 12px;
            background: #F5F3FF;
            border-left: 3px solid #7C3AED;
            border-radius: 6px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .table th {
            text-align: left;
            padding: 6px 8px;
            background: #F1F5F9;
            color: #64748B;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #E8EDF2;
        }

        .table td {
            padding: 7px 8px;
            border-bottom: 1px solid #E8EDF2;
            color: #1E293B;
        }

        .footer {
            margin-top: 24px;
            padding-top: 14px;
            border-top: 1px solid #E8EDF2;
            font-size: 8.5px;
            color: #94A3B8;
            text-align: center;
            font-style: italic;
        }

        .alerte-liste {
            color: #DC2626;
            font-weight: 800;
        }
    </style>
</head>
<body>

    {{-- EN-TÊTE --}}
    <div class="header">

        <div class="header-top">

            <div>
                <h1>Rapport de correspondance — Filtrage</h1>
                <div class="meta">
                    Généré le {{ $genereLe->format('d/m/Y à H:i') }}
                    par {{ $agent->nom }}
                </div>
            </div>

            <span class="badge-danger">
                ⚠ Cas à examiner
            </span>

        </div>

    </div>


    {{-- IDENTITÉ --}}
    <div class="section">

        <h2>1. Identité concernée</h2>

        <div class="grid">

            <div class="grid-row">

                <div class="grid-cell" style="width: 60%;">

                    <div class="label">Nom</div>
                    <div class="value">{{ $nomCible }}</div>

                </div>

                <div class="grid-cell" style="width: 40%;">

                    <div class="label">Type</div>
                    <div class="value">
                        {{ $estSignataire ? 'Signataire de personne morale' : 'Client' }}
                    </div>

                </div>

            </div>

        </div>


        @if ($estSignataire && $personneMoraleParente)

            <div style="margin-top: 8px;">

                <div class="label">Personne morale de rattachement</div>
                <div class="value">
                    {{ $personneMoraleParente->raison_sociale }}
                    @if ($roleSignataire)
                        — {{ $roleSignataire }}
                    @endif
                </div>

            </div>

        @endif

    </div>


    {{-- CORRESPONDANCE --}}
    <div class="section">

        <h2>2. Correspondance détectée</h2>

        <div style="margin-bottom: 12px;">

            <span class="score-box">
                {{ $pourcentage }} %
            </span>

            <span style="margin-left: 10px; font-size: 9px; color: #64748B; font-weight: 700;">
                de similarité de nom
            </span>

        </div>


        <div class="grid">

            <div class="grid-row">

                <div class="grid-cell" style="width: 50%;">

                    <div class="label">Source de la liste</div>
                    <div class="value alerte-liste">
                        {{ $resultat->entreeListe->source->libelle() }}
                    </div>

                </div>

                <div class="grid-cell" style="width: 50%;">

                    <div class="label">Nom inscrit sur la liste</div>
                    <div class="value alerte-liste">
                        {{ $resultat->entreeListe->nom }}
                    </div>

                </div>

            </div>

        </div>


        @if ($resultat->entreeListe->categorie)

            <div style="margin-top: 8px;">

                <div class="label">Catégorie</div>
                <div class="value">{{ $resultat->entreeListe->categorie }}</div>

            </div>

        @endif

    </div>


    {{-- EXPLICATION --}}
    <div class="section">

        <h2>3. Explication de la correspondance</h2>

        <div class="explication">
            {!! $explication !!}
        </div>

    </div>


    {{-- CAS SIMILAIRES --}}
    <div class="section">

        <h2>4. Historique des décisions similaires dans le réseau</h2>

        @if (($similaires['total'] ?? 0) > 0)

            <table class="table">

                <thead>

                    <tr>
                        <th>Motif</th>
                        <th style="width: 100px; text-align: right;">Occurrences</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach ($similaires['parMotif'] as $ligne)

                        <tr>
                            <td>{{ $ligne['motif']->libelle() }}</td>
                            <td style="text-align: right; font-weight: 800;">
                                {{ $ligne['nombre'] }}
                            </td>
                        </tr>

                    @endforeach

                </tbody>

            </table>

            <div style="margin-top: 8px; font-size: 9px; color: #64748B; font-style: italic;">
                Total : {{ $similaires['total'] }}
                cas similaire{{ $similaires['total'] > 1 ? 's' : '' }}
                déjà tranché{{ $similaires['total'] > 1 ? 's' : '' }} dans votre réseau.
            </div>

        @else

            <div style="font-size: 10px; color: #64748B; font-style: italic;">
                Aucun cas similaire tranché pour l'instant dans votre réseau.
            </div>

        @endif

    </div>


    {{-- FOOTER --}}
    <div class="footer">

        Rapport généré automatiquement — explication locale produite par des gabarits fixes,
        sans IA en ligne. Document à usage interne uniquement.

    </div>

</body>
</html>
