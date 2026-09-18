<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Ce fichier est utilisé pour enregistrer toutes les commandes Artisan
| personnalisées ainsi que les tâches planifiées de l'application.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*
|--------------------------------------------------------------------------
| TÂCHES PLANIFIÉES
|--------------------------------------------------------------------------
*/

// 1. Rattrapage NPI en mode dégradé (07_PROMPT_MODE_DEGRADE_NPI_OCR §2.4)
//    Vérifie toutes les 5 minutes si des NPI en attente peuvent
//    maintenant être validés (retour de connexion ANIP).
Schedule::command('npi:verifier-en-attente')
    ->everyFiveMinutes()
    ->timezone(config('app.timezone'));


// 2. Rapports journaliers caissiers (20h, heure locale du serveur)
//    Envoie à chaque agent son rapport d'activité de la journée.
Schedule::command('rapports:envoyer-quotidiens')
    ->dailyAt('20:00')
    ->timezone(config('app.timezone'));


// 3. Récapitulatif PPE quotidien aux responsables d'agence (18h, heure du Bénin)
//    Liste les clients et signataires PPE/sanctions détectés dans la journée
//    et encore en attente de décision.
Schedule::command('ppe:envoyer-recaps')
    ->dailyAt('18:00')
    ->timezone('Africa/Porto-Novo');
