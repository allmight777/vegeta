<?php

use App\Http\Controllers\Agent\Rapports\RapportJournalierController;
use Illuminate\Support\Facades\Route;

Route::prefix('rapports')->name('rapports.')->group(function () {
    Route::get('/', [RapportJournalierController::class, 'index'])->name('index');
    Route::post('/', [RapportJournalierController::class, 'generer'])->name('generer');
    Route::get('/{rapport}/telecharger', [RapportJournalierController::class, 'telecharger'])->name('telecharger');
    Route::post('/{rapport}/envoyer', [RapportJournalierController::class, 'envoyer'])->name('envoyer');
});
