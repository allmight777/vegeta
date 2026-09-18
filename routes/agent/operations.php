<?php

use App\Http\Controllers\Agent\Operations\HistoriqueOperationController;
use App\Http\Controllers\Agent\Operations\OperationController;
use Illuminate\Support\Facades\Route;

Route::prefix('operations')->name('operations.')->group(function () {
    Route::get('/historique', [HistoriqueOperationController::class, 'index'])->name('historique');
    Route::get('/nouveau', [OperationController::class, 'creer'])->name('creer');
    Route::post('/', [OperationController::class, 'stocker'])->name('stocker');
});
