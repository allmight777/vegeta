<?php

use App\Http\Controllers\Agent\Assistance\AssistantController;
use App\Http\Controllers\Agent\Assistance\EscaladesController;
use Illuminate\Support\Facades\Route;

Route::post('/assistant/repondre', [AssistantController::class, 'repondre'])->name('assistant.repondre');
Route::post('/assistant/escalader', [AssistantController::class, 'escalader'])->name('assistant.escalader');

Route::prefix('assistance')->name('assistance.')->middleware('role.agent:responsable_lbcft,direction')->group(function () {
    Route::get('/escalades', [EscaladesController::class, 'index'])->name('escalades.index');
    Route::post('/escalades/{escalade}/repondre', [EscaladesController::class, 'repondre'])->name('escalades.repondre');
});
