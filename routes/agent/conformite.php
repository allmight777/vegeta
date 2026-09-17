<?php

use App\Http\Controllers\Agent\Conformite\DeclarationCentifController;
use Illuminate\Support\Facades\Route;

Route::prefix('conformite')->name('conformite.')->middleware('role.agent:responsable_lbcft,direction')->group(function () {
    Route::post('/declarations-centif/{declarationCentif}/generer', [DeclarationCentifController::class, 'generer'])->name('declarations-centif.generer');
});
