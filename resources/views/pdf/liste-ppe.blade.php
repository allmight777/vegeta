<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><title>Liste des PPE</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#1e293b}h1{font-size:16px;margin:0 0 4px}p{margin:0 0 12px;color:#64748b}table{width:100%;border-collapse:collapse}th,td{border:1px solid #cbd5e1;padding:6px;text-align:left}th{background:#f1f5f9}</style></head>
<body>
<h1>Liste des personnes politiquement exposées (PPE)</h1>
<p>{{ $agence->nom }} — généré le {{ $genereLe->format('d/m/Y à H:i') }} — {{ $clients->count() }} client(s). Document confidentiel (Loi art. 63).</p>
<table>
<thead><tr><th>Client</th><th>Type</th><th>Statut PPE</th><th>Déclarée à l'adhésion</th><th>Pièces justificatives</th><th>Fiche créée le</th></tr></thead>
<tbody>
@forelse ($clients as $client)
<tr>
<td>{{ $client->nomAffichage() }}</td>
<td>{{ $client->type->value === 'personne_morale' ? 'Personne morale' : 'Personne physique' }}</td>
<td>{{ $client->statut_ppe->libelle() }}</td>
<td>{{ $client->ppe_declare ? 'Oui' : 'Non' }}</td>
<td>{{ $client->documents_ppe_count }}</td>
<td>{{ $client->created_at->format('d/m/Y') }}</td>
</tr>
@empty
<tr><td colspan="6">Aucune PPE enregistrée.</td></tr>
@endforelse
</tbody></table>
</body></html>
