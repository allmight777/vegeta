<?php

use App\Http\Controllers\Admin\Tableau\TableauBordController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TableauBordController::class, 'index'])->name('tableau-de-bord.index');
