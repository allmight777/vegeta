<?php

use App\Http\Controllers\Agent\Clients\ClientController;
use App\Http\Controllers\Agent\Clients\ImportDocumentController;
use App\Http\Controllers\Agent\Clients\MandataireController;
use App\Http\Controllers\Agent\Clients\NpiVerificationController;
use App\Http\Controllers\Agent\Clients\SignataireController;
use App\Http\Controllers\Agent\Clients\SystemeExistantController;
use Illuminate\Support\Facades\Route;

Route::prefix('clients')->name('clients.')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('index');
    Route::get('/nouveau', [ClientController::class, 'creer'])->name('creer');
    Route::post('/', [ClientController::class, 'stocker'])->name('stocker');
    Route::get('/{client}/completer', [ClientController::class, 'completer'])->name('completer');
    Route::put('/{client}/completer', [ClientController::class, 'mettreAJour'])->name('mettre-a-jour');

    Route::post('/npi/verifier', [NpiVerificationController::class, 'verifier'])->name('npi.verifier');

    Route::get('/{client}/systeme-existant', [SystemeExistantController::class, 'rechercher'])->name('systeme-existant.rechercher');
    Route::post('/{client}/systeme-existant', [SystemeExistantController::class, 'appliquer'])->name('systeme-existant.appliquer');

    Route::post('/personnes-morales/{personneMorale}/signataires', [SignataireController::class, 'stocker'])->name('signataires.stocker');
    Route::delete('/signataires/{signataire}', [SignataireController::class, 'detruire'])->name('signataires.detruire');

    Route::post('/personnes-physiques/{personnePhysique}/mandataires', [MandataireController::class, 'stocker'])->name('mandataires.stocker');
    Route::delete('/mandataires/{mandataire}', [MandataireController::class, 'detruire'])->name('mandataires.detruire');

    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ImportDocumentController::class, 'creer'])->name('creer');
        Route::post('/', [ImportDocumentController::class, 'stocker'])->name('stocker');
        Route::get('/{lot}', [ImportDocumentController::class, 'revue'])->name('revue')->where('lot', '[0-9a-fA-F-]{36}');
        Route::get('/documents/{document}/revoir', [ImportDocumentController::class, 'revoir'])->name('revoir');
        Route::post('/documents/{document}/valider', [ImportDocumentController::class, 'valider'])->name('valider');
    });
});
