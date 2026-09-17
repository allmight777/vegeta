<?php

use App\Http\Controllers\Agent\Filtrage\FiltrageController;
use Illuminate\Support\Facades\Route;

Route::prefix('filtrage')->name('filtrage.')->middleware('role.agent:responsable_lbcft,direction')->group(function () {
    Route::get('/', [FiltrageController::class, 'index'])->name('index');
    Route::put('/{resultatFiltrage}/decider', [FiltrageController::class, 'decider'])->name('decider');
});
