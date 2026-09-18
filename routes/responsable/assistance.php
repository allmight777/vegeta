<?php

use App\Http\Controllers\Responsable\Assistance\EscaladesController;
use Illuminate\Support\Facades\Route;

Route::prefix('assistance')->name('assistance.')->group(function () {
    Route::get('/escalades', [EscaladesController::class, 'index'])->name('escalades.index');
    Route::post('/escalades/{escalade}/repondre', [EscaladesController::class, 'repondre'])->name('escalades.repondre');
});
