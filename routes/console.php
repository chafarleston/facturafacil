<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tareas programadas (Laravel 11+ usa routes/console.php; App\Console\Kernel::schedule() NO se ejecuta)
Schedule::command('print:process-queue')->everyMinute()->withoutOverlapping();
Schedule::command('sunat:download-padron')->weeklyOn(0, '02:00');

// Facturación electrónica: boletas al día siguiente (09:00), reintento a las 12:00, check tras enviar, alerta si persiste
Schedule::command('sunat:send-daily-summary')->dailyAt('09:00')->withoutOverlapping();
Schedule::command('sunat:send-daily-summary')->dailyAt('12:00')->withoutOverlapping();
Schedule::command('sunat:check-summaries')->dailyAt('09:05')->withoutOverlapping();
Schedule::command('sunat:check-summaries')->dailyAt('09:20')->withoutOverlapping();
Schedule::command('sunat:check-summaries')->dailyAt('12:05')->withoutOverlapping();
Schedule::command('sunat:check-summaries')->dailyAt('12:20')->withoutOverlapping();
Schedule::command('sunat:verify-unresolved')->dailyAt('12:06')->withoutOverlapping();
