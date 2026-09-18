<?php

use App\Http\Controllers\Admin\DocumentsIa\DocumentIaController;
use Illuminate\Support\Facades\Route;

Route::prefix('documents-ia')->name('documents-ia.')->group(function () {
    Route::get('/', [DocumentIaController::class, 'index'])->name('index');
    Route::get('/creer', [DocumentIaController::class, 'creer'])->name('creer');
    Route::post('/', [DocumentIaController::class, 'stocker'])->name('stocker');
    Route::put('/{documentIa}/visibilite', [DocumentIaController::class, 'mettreAJourVisibilite'])->name('mettre-a-jour-visibilite');
    Route::delete('/{documentIa}', [DocumentIaController::class, 'supprimer'])->name('supprimer');
});
