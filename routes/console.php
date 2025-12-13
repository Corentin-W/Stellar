<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\CheckStaleTargetsJob;
use App\Jobs\CreditMonthlyAllowanceJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * RoboTarget Scheduled Jobs
 */

// Vérifier les cibles stale toutes les heures
Schedule::job(new CheckStaleTargetsJob(48))
    ->hourly()
    ->name('robotarget:check-stale')
    ->onOneServer()
    ->withoutOverlapping();

// Renouveler les crédits mensuels le 1er de chaque mois à 00:00
Schedule::job(new CreditMonthlyAllowanceJob())
    ->monthlyOn(1, '00:00')
    ->name('subscription:renew-credits')
    ->onOneServer()
    ->withoutOverlapping();
