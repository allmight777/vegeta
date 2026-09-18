<?php

use App\Http\Controllers\Responsable\Clients\ClientController;
use Illuminate\Support\Facades\Route;

Route::prefix('clients')->name('clients.')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('index');
    Route::get('/{client}', [ClientController::class, 'afficher'])->name('afficher');
});
