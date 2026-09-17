<?php

use App\Http\Controllers\Admin\Detection\RegleDetectionController;
use Illuminate\Support\Facades\Route;

Route::get('/regles-detection', [RegleDetectionController::class, 'index'])->name('regles-detection.index');
