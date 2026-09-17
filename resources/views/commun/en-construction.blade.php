@extends($layout ?? 'layouts.agent')

@section('titre', $titre ?? 'En construction')

@section('contenu')
    <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center">
        <p class="text-sm font-medium text-gray-900">{{ $titre ?? 'Écran en construction' }}</p>
        <p class="mt-2 text-sm text-gray-500">
            Cet écran arrive dans une prochaine étape du build ({{ $proprietaire ?? 'équipe' }}).
        </p>
    </div>
@endsection
