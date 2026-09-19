<?php

use App\Http\Controllers\Controleur\Soupcons\AideAnalyseController;
use App\Http\Controllers\Controleur\Soupcons\DossierSoupconController;
use Illuminate\Support\Facades\Route;

Route::prefix('soupcons')->name('soupcons.')->group(function () {
    Route::get('/{suggestion}', [DossierSoupconController::class, 'ouvrir'])->name('ouvrir');
    Route::post('/{suggestion}/brouillon', [DossierSoupconController::class, 'enregistrerBrouillon'])->name('brouillon');
    Route::post('/{suggestion}/transmettre', [DossierSoupconController::class, 'transmettre'])->name('transmettre');
    Route::post('/{suggestion}/ecarter', [DossierSoupconController::class, 'ecarter'])->name('ecarter');
    Route::post('/{suggestion}/aide', AideAnalyseController::class)->name('aide');
});
