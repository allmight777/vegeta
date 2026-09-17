<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111; }
        .bandeau { background: #fef3c7; border: 1px solid #d97706; color: #92400e; padding: 8px 10px; margin-bottom: 16px; font-weight: bold; text-align: center; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f3f4f6; }
        .total { font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="bandeau">GABARIT DE DÉMONSTRATION — FORMAT OFFICIEL CENTIF NON FOURNI À L'ÉQUIPE</div>

    <h1>Déclaration des transactions en espèces — période {{ $declaration->periode }}</h1>
    <p>Client : {{ $client->nomAffichage() }}</p>
    <p>Montant cumulé : {{ number_format((float) $declaration->montant_cumule, 0, ',', ' ') }} XOF</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Devise</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($operations as $operation)
                <tr>
                    <td>{{ $operation->effectuee_le->format('d/m/Y') }}</td>
                    <td>{{ $operation->type->libelle() }}</td>
                    <td>{{ number_format((float) $operation->montant, 0, ',', ' ') }}</td>
                    <td>{{ $operation->devise_code }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total">Total : {{ number_format((float) $declaration->montant_cumule, 0, ',', ' ') }} XOF</p>
</body>
</html>
