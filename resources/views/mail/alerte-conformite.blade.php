<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Nouvelle alerte de conformité</title>
</head>
<body style="font-family: sans-serif; color: #1E293B; font-size: 14px;">
    <p><strong>Nouvelle alerte de conformité — {{ $alerte->gravite->libelle() }}</strong></p>

    <p>{{ $alerte->type->libelle() }}</p>

    <p>Agence : {{ $alerte->client->agenceCreation?->nom ?? 'non renseignée' }}<br>
    Ouverte le : {{ $alerte->created_at->format('d/m/Y à H:i') }}</p>

    <p>Connectez-vous à votre espace responsable pour consulter le dossier complet et
    décider de la suite à donner.</p>

    <p><a href="{{ route('responsable.tableau-de-bord.index') }}">Ouvrir le tableau de bord</a></p>
</body>
</html>
