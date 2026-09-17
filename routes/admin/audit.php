<?php

use App\Http\Controllers\Admin\Audit\JournalAuditController;
use Illuminate\Support\Facades\Route;

Route::get('/journal-audit', [JournalAuditController::class, 'index'])->name('journal-audit.index');
Route::post('/journal-audit/verifier-chaine', [JournalAuditController::class, 'verifierChaine'])->name('journal-audit.verifier-chaine');
