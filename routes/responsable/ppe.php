<?php

use App\Http\Controllers\Responsable\Ppe\PpeController;
use Illuminate\Support\Facades\Route;

Route::prefix('ppe')->name('ppe.')->group(function () {
    Route::get('/', [PpeController::class, 'index'])->name('index');
    Route::get('/export/{format}', [PpeController::class, 'exporter'])->whereIn('format', ['pdf', 'xlsx'])->name('exporter');
    Route::get('/{client}', [PpeController::class, 'detail'])->name('detail');
});
