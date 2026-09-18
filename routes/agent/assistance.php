<?php

use App\Http\Controllers\Agent\Assistance\AssistantController;
use Illuminate\Support\Facades\Route;

Route::post('/assistant/repondre', [AssistantController::class, 'repondre'])->name('assistant.repondre');
Route::post('/assistant/escalader', [AssistantController::class, 'escalader'])->name('assistant.escalader');
