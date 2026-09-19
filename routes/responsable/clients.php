<?php

use App\Http\Controllers\Responsable\Clients\ClientController;
use App\Http\Controllers\Responsable\Ppe\ListePpeController;
use Illuminate\Support\Facades\Route;

Route::prefix('clients')->name('clients.')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('index');
    Route::post('/ppe/envoyer', [ListePpeController::class, 'envoyer'])->name('ppe.envoyer');
    Route::get('/{client}', [ClientController::class, 'afficher'])->name('afficher');
});
