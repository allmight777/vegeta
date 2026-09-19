<?php

use App\Http\Controllers\Responsable\Comptes\CompteController;
use Illuminate\Support\Facades\Route;

Route::prefix('comptes')->name('comptes.')->group(function () {
    Route::put('/{compte}/geler', [CompteController::class, 'geler'])->name('geler');
    Route::put('/{compte}/lever', [CompteController::class, 'lever'])->name('lever');
});
