<?php

use App\Http\Controllers\Admin\Assistance\AssistantController;
use Illuminate\Support\Facades\Route;

Route::post('/assistant/repondre', [AssistantController::class, 'repondre'])->name('assistant.repondre');
