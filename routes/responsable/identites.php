<?php

use App\Http\Controllers\Responsable\Identites\IdentiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('identites')->name('identites.')->group(function () {
    Route::get('/{identite}', [IdentiteController::class, 'afficher'])->name('afficher');
});
