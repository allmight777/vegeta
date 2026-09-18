<?php

use App\Http\Controllers\Agent\Identites\IdentiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('identites')->name('identites.')->group(function () {
    Route::get('/{identite}', [IdentiteController::class, 'afficher'])->name('afficher');
});
