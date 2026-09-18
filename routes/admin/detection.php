<?php

use App\Http\Controllers\Admin\Detection\RegleDetectionController;
use Illuminate\Support\Facades\Route;

Route::get('/regles-detection', [RegleDetectionController::class, 'index'])
    ->name('regles-detection.index');

Route::put('/regles-detection/{regle}/basculer', [RegleDetectionController::class, 'basculer'])
    ->name('regles-detection.basculer');

Route::get('/regles-detection/{regle}/editer', [RegleDetectionController::class, 'editer'])
    ->name('regles-detection.editer');

Route::put('/regles-detection/{regle}', [RegleDetectionController::class, 'mettreAJour'])
    ->name('regles-detection.mettre-a-jour');
