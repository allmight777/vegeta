<?php

use App\Http\Controllers\Responsable\Conformite\AlerteNpiController;
use App\Http\Controllers\Responsable\Conformite\DeclarationCentifController;
use Illuminate\Support\Facades\Route;

Route::prefix('conformite')->name('conformite.')->group(function () {
    Route::post('/declarations-centif/{declarationCentif}/generer', [DeclarationCentifController::class, 'generer'])->name('declarations-centif.generer');
    Route::post('/alertes-npi/{alerte}/traiter', [AlerteNpiController::class, 'traiter'])->name('alertes-npi.traiter');
});
