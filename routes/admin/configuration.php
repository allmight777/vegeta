<?php

use App\Http\Controllers\Admin\Configuration\ConfigurationController;
use Illuminate\Support\Facades\Route;

Route::get('/configuration', [ConfigurationController::class, 'index'])->name('configuration.index');
Route::put('/configuration', [ConfigurationController::class, 'mettreAJour'])->name('configuration.mettre-a-jour');
Route::post('/configuration/reinitialiser', [ConfigurationController::class, 'reinitialiser'])->name('configuration.reinitialiser');
