<?php

use App\Http\Controllers\Responsable\Soupcons\DossierRecuController;
use Illuminate\Support\Facades\Route;

Route::prefix('soupcons')->name('soupcons.')->group(function () {
    Route::get('/', [DossierRecuController::class, 'index'])->name('index');
    Route::get('/{dossier}', [DossierRecuController::class, 'afficher'])->name('afficher');
    Route::post('/{dossier}/decider', [DossierRecuController::class, 'decider'])->name('decider');
    Route::post('/{dossier}/resumer', [DossierRecuController::class, 'resumer'])->name('resumer');
});
