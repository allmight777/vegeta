<?php

use App\Http\Controllers\Agent\Clients\ClientController;
use App\Http\Controllers\Agent\Clients\SignataireController;
use Illuminate\Support\Facades\Route;

Route::prefix('clients')->name('clients.')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('index');
    Route::get('/nouveau', [ClientController::class, 'creer'])->name('creer');
    Route::post('/', [ClientController::class, 'stocker'])->name('stocker');
    Route::get('/{client}/completer', [ClientController::class, 'completer'])->name('completer');
    Route::put('/{client}/completer', [ClientController::class, 'mettreAJour'])->name('mettre-a-jour');

    Route::post('/personnes-morales/{personneMorale}/signataires', [SignataireController::class, 'stocker'])->name('signataires.stocker');
    Route::delete('/signataires/{signataire}', [SignataireController::class, 'detruire'])->name('signataires.detruire');
});
