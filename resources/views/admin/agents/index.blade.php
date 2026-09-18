@extends('layouts.admin')

@section('titre', 'Comptes caissier et responsable d\'agence')

@section('contenu')

    @if (session('identifiants_generes'))
        @php $identifiants = session('identifiants_generes'); @endphp
        <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <strong>Identifiants générés — à noter maintenant, ils ne seront plus affichés :</strong>
            <div class="mt-1 font-mono">
                @if ($identifiants['matricule'])
                    Matricule : {{ $identifiants['matricule'] }}<br>
                @endif
                Mot de passe : {{ $identifiants['mot_de_passe'] }}
            </div>
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex gap-2">
            <select name="agence_id" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                <option value="">Toutes les agences</option>
                @foreach ($agences as $agence)
                    <option value="{{ $agence->id }}" @selected(request('agence_id') == $agence->id)>{{ $agence->reseau->nom }} — {{ $agence->nom }}</option>
                @endforeach
            </select>
            <select name="role" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                <option value="">Tous les rôles</option>
                <option value="caissier" @selected(request('role') === 'caissier')>Caissier</option>
                <option value="responsable_agence" @selected(request('role') === 'responsable_agence')>Responsable d'agence</option>
            </select>
        </form>

        <a href="{{ route('admin.agents.creer') }}" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
            Nouveau compte
        </a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Nom</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Agence</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Rôle</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Statut</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($agents as $agent)
                    <tr>
                        <td class="px-3 py-2">{{ $agent->nom }}</td>
                        <td class="px-3 py-2">{{ $agent->agence->reseau->nom }} — {{ $agent->agence->nom }}</td>
                        <td class="px-3 py-2">{{ $agent->role->libelle($agent->civilite) }}</td>
                        <td class="px-3 py-2">
                            @if ($agent->actif)
                                <span class="rounded bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Actif</span>
                            @else
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">Désactivé</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex gap-3">
                                <form method="POST" action="{{ route('admin.agents.activer-desactiver', $agent) }}">
                                    @csrf @method('PUT')
                                    <button type="submit" class="text-xs font-semibold text-emerald-700 hover:underline">
                                        {{ $agent->actif ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.agents.reinitialiser-mot-de-passe', $agent) }}">
                                    @csrf @method('PUT')
                                    <button type="submit" class="text-xs font-semibold text-gray-600 hover:underline">
                                        Réinitialiser le mot de passe
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-gray-400">Aucun compte pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $agents->links() }}</div>

@endsection
