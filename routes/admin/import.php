<?php

use App\Http\Controllers\Admin\Import\ImportController;
use Illuminate\Support\Facades\Route;

Route::prefix('import')->name('import.')->group(function () {
    Route::get('/', [ImportController::class, 'index'])->name('index');
    Route::get('/nouveau', [ImportController::class, 'creer'])->name('creer');
    Route::post('/apercu', [ImportController::class, 'televerser'])->name('televerser');
    Route::post('/confirmer', [ImportController::class, 'confirmer'])->name('confirmer');
});
