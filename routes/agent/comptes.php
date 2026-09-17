<?php

use App\Http\Controllers\Agent\Comptes\CompteController;
use Illuminate\Support\Facades\Route;

Route::post('/clients/{client}/comptes', [CompteController::class, 'stocker'])->name('comptes.stocker');
