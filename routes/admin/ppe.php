<?php

use App\Http\Controllers\Admin\Ppe\SignatairesController;
use Illuminate\Support\Facades\Route;

Route::prefix('ppe')->name('ppe.')->group(function () {
    Route::get('/signataires', [SignatairesController::class, 'index'])->name('signataires.index');
});
