<?php

use App\Http\Controllers\Admin\Listes\EntreeListeController;
use Illuminate\Support\Facades\Route;

Route::get('/listes', [EntreeListeController::class, 'index'])->name('listes.index');
Route::post('/listes/importer', [EntreeListeController::class, 'importer'])->name('listes.importer');
