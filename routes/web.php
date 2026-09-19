<?php

use App\Http\Controllers\Admin\Authentification\ConnexionController as AdminConnexionController;
use App\Http\Controllers\Agent\Authentification\ConnexionController as AgentConnexionController;
use App\Http\Controllers\DocumentIaVisualisationController;
use App\Http\Controllers\Rapports\AccesListePpePartageController;
use App\Http\Controllers\Rapports\AccesRapportPartageController;
use Illuminate\Support\Facades\Route;

// Pas de page d'accueil publique : on renvoie directement vers l'espace agent (guichet en premier).
Route::redirect('/', '/connexion');

Route::middleware('guest:agent')->group(function () {
    Route::get('/connexion', [AgentConnexionController::class, 'creer'])->name('agent.connexion.creer');
    Route::post('/connexion', [AgentConnexionController::class, 'stocker'])
        ->middleware('throttle:connexion')
        ->name('agent.connexion.stocker');
});
Route::post('/deconnexion', [AgentConnexionController::class, 'detruire'])
    ->middleware('auth:agent')
    ->name('agent.connexion.detruire');

Route::get('/rapports/partages/{jeton}', [AccesRapportPartageController::class, 'afficher'])
    ->middleware('throttle:12,1')
    ->name('rapports.partages.afficher');
Route::post('/rapports/partages/{jeton}/telecharger', [AccesRapportPartageController::class, 'telecharger'])
    ->middleware('throttle:6,1')
    ->name('rapports.partages.telecharger');

Route::get('/ppe/partages/{jeton}', [AccesListePpePartageController::class, 'afficher'])
    ->middleware('throttle:12,1')
    ->name('ppe.partages.afficher');
Route::post('/ppe/partages/{jeton}/telecharger', [AccesListePpePartageController::class, 'telecharger'])
    ->middleware('throttle:6,1')
    ->name('ppe.partages.telecharger');

Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/connexion', [AdminConnexionController::class, 'creer'])->name('admin.connexion.creer');
    Route::post('/admin/connexion', [AdminConnexionController::class, 'stocker'])
        ->middleware('throttle:connexion')
        ->name('admin.connexion.stocker');
});
Route::post('/admin/deconnexion', [AdminConnexionController::class, 'detruire'])
    ->middleware('auth:admin')
    ->name('admin.connexion.detruire');

Route::get('/documents-ia/{document}/voir', [DocumentIaVisualisationController::class, 'voir'])
    ->name('documents-ia.voir');
