<?php

use App\Http\Controllers\Responsable\Filtrage\FiltrageController;
use Illuminate\Support\Facades\Route;

Route::prefix('filtrage')->name('filtrage.')->group(function () {
    Route::get('/', [FiltrageController::class, 'index'])->name('index');
    Route::put('/{resultatFiltrage}/decider', [FiltrageController::class, 'decider'])->name('decider');
    Route::post('/{resultatFiltrage}/suggerer-motif', [FiltrageController::class, 'suggererMotif'])->name('suggerer-motif');
});
