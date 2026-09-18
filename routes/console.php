<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rattrapage NPI en mode dégradé (07_PROMPT_MODE_DEGRADE_NPI_OCR §2.4).
Schedule::command('npi:verifier-en-attente')->everyFiveMinutes();
Schedule::command('rapports:envoyer-quotidiens')->dailyAt('20:00')->timezone(config('app.timezone'));
