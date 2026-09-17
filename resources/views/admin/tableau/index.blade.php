@extends('layouts.admin')

@section('titre', 'Tableau de bord')

@section('contenu')
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-xs text-gray-500">Réseaux</p>
            <p class="mt-1 text-2xl font-semibold">{{ $nombreReseaux }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-xs text-gray-500">Agences</p>
            <p class="mt-1 text-2xl font-semibold">{{ $nombreAgences }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-xs text-gray-500">Administrateurs</p>
            <p class="mt-1 text-2xl font-semibold">{{ $nombreAdmins }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-xs text-gray-500">Agents actifs</p>
            <p class="mt-1 text-2xl font-semibold">{{ $nombreAgents }}</p>
        </div>
    </div>
@endsection
