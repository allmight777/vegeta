<?php

use App\Http\Controllers\Admin\Agents\AgentController;
use Illuminate\Support\Facades\Route;

Route::prefix('agents')->name('agents.')->group(function () {
    Route::get('/', [AgentController::class, 'index'])->name('index');
    Route::get('/creer', [AgentController::class, 'creer'])->name('creer');
    Route::post('/', [AgentController::class, 'stocker'])->name('stocker');
    Route::get('/{agent}/modifier', [AgentController::class, 'modifier'])->name('modifier');
    Route::put('/{agent}', [AgentController::class, 'mettreAJour'])->name('mettre-a-jour');
    Route::post('/envoyer-pdf', [AgentController::class, 'envoyerPdf'])->name('envoyer-pdf');
    Route::put('/{agent}/activer-desactiver', [AgentController::class, 'activerOuDesactiver'])->name('activer-desactiver');
    Route::put('/{agent}/reinitialiser-mot-de-passe', [AgentController::class, 'reinitialiserMotDePasse'])->name('reinitialiser-mot-de-passe');
});
