<?php

use App\Http\Controllers\Controleur\TableauDeBordController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TableauDeBordController::class, 'index'])->name('tableau-de-bord.index');
