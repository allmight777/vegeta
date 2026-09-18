<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
    body{font-family:sans-serif;font-size:12px;}
    table{width:100%;border-collapse:collapse;}
    th,td{border:1px solid #ccc;padding:6px 8px;text-align:left;}
    th{background:#f0f0f0;}
</style></head>
<body>
    <h2>Liste des identifiants — {{ now()->format('d/m/Y H:i') }}</h2>
    <table>
        <thead>
            <tr><th>Nom</th><th>Matricule</th><th>Mot de passe</th></tr>
        </thead>
        <tbody>
            @foreach ($lignes as $ligne)
                <tr>
                    <td>{{ $ligne['nom'] }}</td>
                    <td>{{ $ligne['matricule'] }}</td>
                    <td>{{ $ligne['mot_de_passe'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p style="color:#888;">Document confidentiel — à détruire après usage.</p>
</body>
</html>
